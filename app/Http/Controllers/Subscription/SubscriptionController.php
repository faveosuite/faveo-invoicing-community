<?php

namespace App\Http\Controllers\Subscription;

use App\Auto_renewal;
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\ConcretePostSubscriptionHandleController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Order\BaseRenewController;
use App\Http\Controllers\RazorpayController;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Common\TemplateType;
use App\Model\Mailjob\ExpiryMailDay;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Plugins\Stripe\Controllers\SettingsController;
use App\Services\Payment\ProcessingFee;
use App\Services\Payment\SubscriptionService;
use App\User;
use DB;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Logger;

class SubscriptionController extends Controller
{
    public function __construct(
        protected ConcretePostSubscriptionHandleController $PostSubscriptionHandle
    ) {
    }

    // ── Cron entry point ──────────────────────────────────────────────────

    public function autoRenewal(): void
    {
        ini_set('memory_limit', '-1');

        foreach ($this->getCreatedSubscription() as $sub) {
            $this->checkSubscriptionStatus((object) $sub);
        }

        foreach ($this->getOnDayExpiryInfoSubs() as $subscriptionData) {
            $this->processSubscriptionRenewal((object) $subscriptionData);
        }
    }

    // ── Subscription queries ──────────────────────────────────────────────

    /**
     * @return array<mixed>
     */
    public function getOnDayExpiryInfoSubs(): array
    {
        $stripeEnabled = (bool) StatusSetting::value('stripe_auto_renewal');
        $razorpayEnabled = (bool) StatusSetting::value('razorpay_auto_renewal');

        if (! $stripeEnabled && ! $razorpayEnabled) {
            return [];
        }

        $days = $this->getRenewalDays();
        if ($days === []) {
            return [];
        }

        $subscriptions = collect();

        foreach ($days as $day) {
            $endDate = Date::now()->addDays($day)->toDateString();

            $subscriptions = $subscriptions->merge(
                Subscription::query()
                    ->select(['subscriptions.*', 'orders.id as order_id', 'subscriptions.id as id'])
                    ->join('orders', 'subscriptions.order_id', '=', 'orders.id')
                    ->where(fn (Builder $q) => $q
                        ->whereDate('subscriptions.update_ends_at', $endDate)
                        ->orWhereDate('subscriptions.support_ends_at', $endDate)
                        ->orWhereDate('subscriptions.ends_at', $endDate)
                    )
                    ->where(fn (Builder $q) => $q
                        ->when($stripeEnabled, fn ($q) => $q
                            // status=1: needs gateway subscription created
                            // status=3: active, renewal invoice pre-created for webhook
                            ->orWhere(fn (Builder $q) => $q->where('subscriptions.is_subscribed', 1)->whereIn('subscriptions.autoRenew_status', [1, 3]))
                        )
                        ->when($razorpayEnabled, fn ($q) => $q
                            ->orWhere(fn (Builder $q) => $q->where('subscriptions.is_subscribed', 1)->whereIn('subscriptions.rzp_subscription', [1, 3]))
                        )
                    )
                    ->get()
            );
        }

        return $subscriptions->unique('id')->values()->toArray();
    }

    /**
     * @return array<mixed>
     */
    public function getCreatedSubscription(): array
    {
        $stripeEnabled = (bool) StatusSetting::value('stripe_auto_renewal');
        $razorpayEnabled = (bool) StatusSetting::value('razorpay_auto_renewal');

        if (! $stripeEnabled && ! $razorpayEnabled) {
            return [];
        }

        $days = $this->getRenewalDays();
        if ($days === []) {
            return [];
        }

        $subscriptions = collect();

        foreach ($days as $day) {
            $endDate = Date::now()->addDays($day)->toDateString();

            $subscriptions = $subscriptions->merge(
                Subscription::where(fn (\Illuminate\Contracts\Database\Eloquent\Builder $q) => $q
                    ->whereDate('update_ends_at', $endDate)
                    ->orWhereDate('support_ends_at', $endDate)
                    ->orWhereDate('ends_at', $endDate)
                )
                    ->where(fn ($q) => $q
                        ->when($stripeEnabled, fn ($q) => $q->orWhere('autoRenew_status', 2))
                        ->when($razorpayEnabled, fn ($q) => $q->orWhere('rzp_subscription', 2))
                    )
                    ->get()
            );
        }

        return $subscriptions->unique('id')->values()->toArray();
    }

    /**
     * @return array<mixed>
     */
    private function getRenewalDays(): array
    {
        $raw = ExpiryMailDay::value('autorenewal_days');
        if (empty($raw)) {
            return [];
        }

        $decoded = json_decode((string) $raw);
        if (json_last_error() !== JSON_ERROR_NONE || ! $decoded) {
            return [];
        }

        return array_map(intval(...), (array) $decoded);
    }

    // ── Per-subscription renewal ──────────────────────────────────────────

    private function processSubscriptionRenewal(\stdClass $subscriptionData): void
    {
        $invoice = null;
        $cost = null;
        $currency = null;
        $order = null;
        $user = null;
        $product = null;
        $paymentMethod = null;

        try {
            $subscription = Subscription::findOrFail($subscriptionData->id);
            if (! $subscription instanceof Subscription) {
                throw new Exception('Subscription not found.');
            }
            $plan = Plan::findOrFail($subscription->plan_id);
            $order = Order::findOrFail($subscription->order_id);
            $user = User::findOrFail($subscription->user_id);
            $product = Product::findOrFail($subscription->product_id);
            $planDetails = userCurrencyAndPrice($user->id, $plan);
            $currency = $planDetails['currency'];
            $paymentMethod = $this->resolvePaymentMethod($subscription);

            if (is_null($planDetails['plan'])) {
                throw new Exception(__('message.order_no_active_plan_cancelled', ['order_number' => $order->number]));
            }

            $cost = $this->calculateRenewalCost($subscription, $planDetails, $order);
            $cost = ProcessingFee::addTo($cost, $paymentMethod);

            if ($cost == 0) {
                $subscription->update(['is_subscribed' => 0]);

                return;
            }

            $invoice = $this->findOrCreateRenewalInvoice($subscription, $order, $product, $user, $plan, $cost, $currency);
            $cost = (float) $invoice->grand_total;
            $currency = (string) $invoice->currency;
            $unitCost = $this->PostSubscriptionHandle->calculateUnitCost($currency, $cost);

            $stripeDetails = Auto_renewal::where('user_id', $user->id)
                ->where('order_id', $order->id)
                ->where('payment_method', 'stripe')
                ->latest()
                ->first(['customer_id', 'payment_intent_id']);

            // Active subscriptions (status=3) are fulfilled via webhook — gateway fires
            // invoice.payment_succeeded (Stripe) or subscription.charged (Razorpay)
            // and SubscriptionWebhookController handles fulfillment. No polling needed.
            $this->createSubscriptionsForEnabledUsers($stripeDetails, $product, $unitCost, $currency, $plan, $subscription, $invoice, $order, $user, $cost, $subscription->update_ends_at);
        } catch (Exception $exception) {
            $this->PostSubscriptionHandle->sendFailedPayment(
                $cost, $exception->getMessage(), $user,
                $order?->number, $subscriptionData->update_ends_at, // @phpstan-ignore argument.type
                $currency, $order, $product, $invoice, $paymentMethod
            );
        }
    }

    private function resolvePaymentMethod(Subscription $subscription): ?string
    {
        if ($subscription->autoRenew_status != '0') {
            return 'stripe';
        }

        if ($subscription->rzp_subscription != '0') {
            return 'razorpay';
        }

        return null;
    }

    /**
     * @param  array<mixed>  $planDetails
     */
    private function calculateRenewalCost(Subscription $subscription, array $planDetails, Order $order): float
    {
        $price = (float) ($planDetails['plan']->renew_price ?? 0);

        if (isAgentAllowed($subscription->product_id, $subscription->plan_id)) {
            $pricePerAgent = $price / $planDetails['plan']->no_of_agents;

            return $this->getPriceforCloud($order, $pricePerAgent);
        }

        return $price;
    }

    private function findOrCreateRenewalInvoice(Subscription $subscription, Order $order, Product $product, User $user, Plan $plan, float $cost, string $currency): Invoice
    {
        $latestInvoiceId = DB::table('order_invoice_relations')
            ->where('order_id', $subscription->order_id)
            ->latest()
            ->value('invoice_id');

        $existingItem = DB::table('invoice_items')
            ->where('invoice_id', $latestInvoiceId)
            ->where('product_id', $subscription->product_id)
            ->first();

        if ($existingItem) {
            $unpaid = Invoice::where('id', $existingItem->invoice_id)
                ->where('status', 'pending')
                ->where('is_renewed', 1)
                ->latest()
                ->first();

            if ($unpaid) {
                return $unpaid;
            }
        }

        $originalInvoiceId = DB::table('order_invoice_relations')
            ->where('order_id', $order->id)
            ->oldest()
            ->value('invoice_id');

        $agents = DB::table('invoice_items')->where('invoice_id', $originalInvoiceId)->value('agents');
        $invoiceItem = new BaseRenewController()->generateInvoice($product, $user, $order->id, $plan->id, $cost, '', $agents, $currency);
        if (! $invoiceItem instanceof InvoiceItem) {
            throw new Exception('Failed to generate invoice item.');
        }

        return Invoice::findOrFail($invoiceItem->invoice_id);
    }

    // ── Gateway status checks (status=2 → awaiting auth) ─────────────────

    public function checkSubscriptionStatus(\stdClass $subscription): void
    {
        try {
            $invoiceId = DB::table('order_invoice_relations')
                ->where('order_id', $subscription->order_id)
                ->latest()
                ->value('invoice_id');

            $item = DB::table('invoice_items')->where('invoice_id', $invoiceId)->where('product_id', $subscription->product_id)->first();
            $invoice = Invoice::where('id', $item?->invoice_id)->where('status', 'pending')->first();

            if (! $invoice) {
                return;
            }

            $order = Order::find($subscription->order_id);
            $user = User::find($subscription->user_id);
            $productName = (string) Product::where('id', $subscription->product_id)->value('name');
            $cost = (float) $invoice->grand_total;

            if (! $order instanceof Order || ! $user instanceof User) {
                return;
            }

            $sub = Subscription::find($subscription->id);
            if (! $sub instanceof Subscription) {
                return;
            }

            if ($sub->subscribe_id && $sub->rzp_subscription == '2') {
                $status = resolve(SubscriptionService::class)->getStatus('Razorpay', $sub->subscribe_id);
                match ($status) {
                    'authenticated' => $this->handleAuthenticatedSubscription($sub, $invoice, $cost, $user, $order, $productName, 'razorpay'),
                    'expired' => $sub->update(['rzp_subscription' => '1', 'subscribe_id' => '']),
                    default => null,
                };
            } elseif ($sub->subscribe_id && $sub->autoRenew_status == '2') {
                $status = resolve(SubscriptionService::class)->getStatus('Stripe', $sub->subscribe_id);
                match ($status) {
                    'active' => $this->handleAuthenticatedSubscription($sub, $invoice, $cost, $user, $order, $productName, 'stripe'),
                    'incomplete_expired' => $sub->update(['autoRenew_status' => '1', 'subscribe_id' => '']),
                    default => null,
                };
            }
        } catch (Exception $exception) {
            Logger::error($exception->getMessage()); // @phpstan-ignore staticMethod.notFound
        }
    }

    private function handleAuthenticatedSubscription(Subscription $subscription, Invoice $invoice, float|int $cost, User $user, Order $order, string $productName, string $gateway): void
    {
        $statusField = $gateway === 'stripe' ? 'autoRenew_status' : 'rzp_subscription';
        Subscription::where('id', $subscription->id)->update([$statusField => '3']);

        $sub = $this->PostSubscriptionHandle->successRenew($invoice, $subscription, $gateway, $invoice->currency);
        $this->PostSubscriptionHandle->recordPayment($invoice, $gateway);
        $this->PostSubscriptionHandle->sendPaymentSuccessMail($sub, $invoice->currency, $cost, $user, $productName, $order->number); // @phpstan-ignore argument.type
        $this->PostSubscriptionHandle->PaymentSuccessMailtoAdmin($invoice, $cost, $user, $productName, template: null, order: $order, payment: $gateway);
    }

    // ── New subscription creation (status=1) ──────────────────────────────

    public function createSubscriptionsForEnabledUsers(mixed $stripeDetails, Product $product, float|int $unitCost, string $currency, Plan $plan, Subscription $subscription, Invoice $invoice, Order $order, User $user, float|int $cost, mixed $end): void
    {
        if ($subscription->is_subscribed != '1') {
            return;
        }

        if ($subscription->autoRenew_status == '1') {
            $this->handleStripeSubscription($stripeDetails, $product, $unitCost, $currency, $plan, $subscription, $invoice, $order, $user, $cost);
        } elseif ($subscription->rzp_subscription == '1') {
            $this->handleRazorpaySubscription($unitCost, $plan, $product, $invoice, $currency, $subscription, $user, $order, $end);
        }
    }

    private function handleStripeSubscription(mixed $stripeDetails, Product $product, float|int $unitCost, string $currency, Plan $plan, Subscription $subscription, Invoice $invoice, Order $order, User $user, float|int $cost): void
    {
        $response = new SettingsController()->handleStripeAutoPay($stripeDetails, $product, $unitCost, $currency, $plan);

        if ($response->status === 'active') {
            Subscription::where('id', $subscription->id)->update(['subscribe_id' => $response->id, 'autoRenew_status' => '3']);

            // Fulfill the first charge here — Stripe fires invoice.payment_succeeded
            // with billing_reason=subscription_create which the webhook ignores.
            // Subsequent renewals (billing_reason=subscription_cycle) are webhook-driven.
            $sub = $this->PostSubscriptionHandle->successRenew($invoice, $subscription, 'stripe', $currency);
            $this->PostSubscriptionHandle->recordPayment($invoice, 'stripe');

            if ($cost && emailSendingStatus()) {
                $this->PostSubscriptionHandle->sendPaymentSuccessMail($sub, $currency, $cost, $user, $product->name, $order->number); // @phpstan-ignore argument.type
                $this->PostSubscriptionHandle->PaymentSuccessMailtoAdmin($invoice, $cost, $user, $product->name, template: null, order: $order, payment: 'stripe');
            }
        } elseif ($response->status === 'incomplete') {
            $stripeInvoice = \Stripe\Invoice::retrieve($response->raw['latest_invoice'] ?? null);
            $url = $stripeInvoice->hosted_invoice_url;

            if ($url && emailSendingStatus()) {
                $this->sendPendingAuthMail($subscription, $product, $cost, $currency, $url, $user);
                Subscription::where('id', $subscription->id)->update(['subscribe_id' => $response->id, 'autoRenew_status' => '2']);
            }
        }
    }

    private function handleRazorpaySubscription(float|int $unitCost, Plan $plan, Product $product, Invoice $invoice, string $currency, Subscription $subscription, User $user, Order $order, mixed $end): void
    {
        $response = new RazorpayController()->handleRzpAutoPay($unitCost, $plan->days, $product->name, $invoice, $currency, $subscription, $user, $order, $end, $product);

        if ($response->status === 'created') {
            $cost = $this->calculateReverseUnitCost($currency, $unitCost);
            $url = $response->raw['short_url'] ?? null;
            $this->sendPendingAuthMail($subscription, $product, $cost, $currency, $url, $user);
            Subscription::where('id', $subscription->id)->update(['subscribe_id' => $response->id, 'rzp_subscription' => '2']);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function getPriceforCloud(Order $order, float $pricePerAgent): float
    {
        return (int) ltrim(substr($order->serial_key, -4), '0') * $pricePerAgent;
    }

    public function calculateReverseUnitCost(string $currency, float|int $cost): float
    {
        $decimalPlaces = [
            'BIF' => 0, 'CLP' => 0, 'DJF' => 0, 'GNF' => 0, 'JPY' => 0,
            'KMF' => 0, 'KRW' => 0, 'MGA' => 0, 'PYG' => 0, 'RWF' => 0,
            'UGX' => 0, 'VND' => 0, 'VUV' => 0, 'XAF' => 0, 'XOF' => 0,
            'XPF' => 0, 'BHD' => 3, 'JOD' => 3, 'KWD' => 3, 'OMR' => 3,
            'TND' => 3,
        ];

        return match ($decimalPlaces[$currency] ?? 2) {
            0 => round((int) $cost),
            3 => round((int) $cost) / 1000,
            default => round((int) $cost) / 100,
        };
    }

    private function sendPendingAuthMail(Subscription $subscription, Product $product, float|int $cost, string $currency, ?string $url, User $user): void
    {
        $setting = Setting::find(1);
        if (! $setting instanceof Setting) {
            return;
        }
        $contact = getContactData();
        $template = TemplateType::where('name', 'stripe_subscription_authentication')
            ->with('templates')
            ->first()
            ?->templates
            ?->first();

        if (! $template) {
            return;
        }

        $order = Order::find($subscription->order_id);

        $replace = [
            'name' => ucfirst((string) $user->first_name).' '.ucfirst((string) $user->last_name),
            'product' => $product->name,
            'total' => currencyFormat($cost, $currency),
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'expiry_date' => Date::tomorrow()->format('d M Y'),
            'reply_email' => $setting->company_email,
            'application_title' => $setting->title,
            'company_title' => $setting->company,
            'url' => $url,
            'number' => $order?->number,
            'date' => Date::parse($subscription->update_ends_at)->format('d M Y'),
        ];

        new PhpMailController()
            ->SendEmail($setting->email, $user->email, $template->data, $template->name, $template->type()->value('name'), $replace, $template->type()->value('name'));
    }
}
