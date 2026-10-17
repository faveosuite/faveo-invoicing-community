<?php

namespace App\Services\Payment;

use App\Events\OrderPlacedEvent;
use App\Http\Controllers\Order\InvoiceController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\RenewController;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Http\Controllers\Tenancy\CloudExtraActivities;
use App\Http\Controllers\Tenancy\TenantController;
use App\Model\Cart\Cart;
use App\Model\Common\FaveoCloud;
use App\Model\Common\StatusSetting;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Plugins\Payment\Dto\SubscriptionRequest;
use App\Traits\Payment\PostPaymentHandle;
use App\Traits\TaxCalculation;
use App\User;
use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Logger;
use RuntimeException;
use Throwable;

class PostPaymentService
{
    use PostPaymentHandle;
    use TaxCalculation;

    public function __construct(
        private readonly AutoRenewalActivationService $autoRenewal,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function handle(Invoice $invoice, string $gateway): array
    {
        $this->clearCart($invoice);
        $this->recordPayment($invoice, $gateway);

        $metadata = $invoice->metadata ?? [];

        $result = match ($invoice->type) {
            'agent_alteration' => $this->handleAgentAlteration($invoice, $metadata),
            'upgrade_downgrade' => $this->handleUpgradeDowngrade($invoice, $metadata),
            'renewal' => $this->handleRenewal($invoice),
            default => $this->handlePurchase($invoice, $gateway),
        };

        if ($invoice->grand_total && emailSendingStatus()) {
            /** @var User $user */
            $user = User::find($invoice->user_id);
            $productNames = $invoice->invoiceItem()->pluck('product_name')->implode(', ');
            self::sendPaymentSuccessMailtoAdmin($invoice, (float) $invoice->grand_total, $user, $productNames);
        }

        return $result;
    }

    private function clearCart(Invoice $invoice): void
    {
        $cart = Cart::where('user_id', $invoice->user_id)->first();
        if ($cart) {
            $cart->items()->delete();
            $cart->update(['coupon_code' => null, 'coupon_discount' => 0, 'invoice_id' => null]);
        }
    }

    /**
     * @return array<mixed>
     */
    private function handlePurchase(Invoice $invoice, string $gateway): array
    {
        $this->executeOrders($invoice);

        event(new OrderPlacedEvent($invoice));

        if (! empty($invoice->cloud_domain)) {
            $orderNumber = Order::whereIn(
                'id',
                OrderInvoiceRelation::where('invoice_id', $invoice->id)->pluck('order_id')
            )->whereIn('product', cloudPopupProducts())->value('number');

            new TenantController(new Client, new FaveoCloud)->createTenant(
                new Request(['orderNo' => $orderNumber, 'domain' => $invoice->cloud_domain])
            );
        }

        $this->activateRazorpayAutoRenewalOptIn($invoice, $gateway);

        return ['status' => 'success'];
    }

    /**
     * Activation (including immediately creating the real Razorpay
     * subscription and sending the authorization email) is handled by
     * {@see AutoRenewalActivationService::activate()} — shared with the
     * order-page "Enable Auto Renewal" flow so both behave identically.
     *
     * Never lets a failure here fail the purchase itself — auto-renewal is a
     * bonus, not a requirement of a successful purchase.
     */
    private function activateRazorpayAutoRenewalOptIn(Invoice $invoice, string $gateway): void
    {
        // Re-checked here, not just trusted from the flag captured at checkout
        // time — an admin could disable auto-renewal (globally or for this
        // gateway) in the time between checkout and the payment completing.
        if (strtolower($gateway) !== 'razorpay'
            || ! ($invoice->metadata['auto_renew_opt_in'] ?? false)
            || ! StatusSetting::autoRenewalEnabledFor('razorpay')) {
            return;
        }

        $orders = $invoice->orders()->whereHas('subscription')->get();
        $user = $orders->isNotEmpty() ? User::find($invoice->user_id) : null;

        if (! $user) {
            return;
        }

        foreach ($orders as $order) {
            try {
                $this->autoRenewal->activate($order, $user, 'razorpay', 'invoice_'.$invoice->id);
            } catch (Throwable $throwable) {
                Logger::exception($throwable);
            }
        }
    }

    /**
     * @return array<mixed>
     */
    private function handleRenewal(Invoice $invoice): array
    {
        new RenewController()->successRenew($invoice);

        return ['status' => 'success'];
    }

    /**
     * @param  array<mixed>  $metadata
     * @return array<mixed>
     */
    private function handleAgentAlteration(Invoice $invoice, array $metadata): array
    {
        $cloud = new CloudExtraActivities(new Client, new FaveoCloud);

        if ($metadata['agent_increase_date'] ?? false) {
            new RenewController()->successRenew($invoice);
        }

        $cloud->doTheAgentAltering(
            $metadata['new_agents'],
            $metadata['old_license'],
            $metadata['order_id'],
            $metadata['installation_path'],
            $metadata['product_id'],
        );

        $this->updateSubscriptionPriceIfNeeded($metadata['order_id'], $invoice);

        return ['status' => 'success'];
    }

    /**
     * @param  array<mixed>  $metadata
     * @return array<mixed>
     */
    private function handleUpgradeDowngrade(Invoice $invoice, array $metadata): array
    {
        $terminatedOrderId = (int) $metadata['old_order_id'];

        // Safety net behind upgradeDowngradeCloud's one-change-per-order check: if the old
        // order was already replaced, a second paid plan-change invoice must not create another
        // order or bank the old plan's credit again. Payment is recorded; reconcile by hand.
        if (Order::whereKey($terminatedOrderId)->value('order_status') === 'Terminated') {
            Logger::exception(new RuntimeException(sprintf(
                'Plan-change invoice #%s paid for order %d, which is already Terminated — not fulfilled again; refund or apply manually.',
                $invoice->number, $terminatedOrderId
            )));

            return [];
        }

        $oldLicense = (string) $metadata['old_license'];
        $installationPath = (string) $metadata['installation_path'];
        $discount = isset($metadata['discount']) ? (float) $metadata['discount'] : null;

        $this->executeOrders($invoice);

        $newActiveOrderId = (int) OrderInvoiceRelation::where('invoice_id', $invoice->id)
            ->latest()->value('order_id');
        $newOrder = Order::find($newActiveOrderId);
        if (! $newOrder) {
            throw new RuntimeException(sprintf('New order not found for invoice #%s after checkoutAction.', $invoice->id));
        }

        // Order::$serial_key already decrypts on access (see Order::serialKey()) —
        // decrypting it again here threw DecryptException("The payload is invalid")
        // on every upgrade/downgrade whose new order's license this loaded.
        $licenseCode = $newOrder->serial_key;
        $productId = (int) $newOrder->product;

        $cloud = new CloudExtraActivities(new Client, new FaveoCloud);
        $cloud->doTheProductUpgradeDowngrade(
            $licenseCode,
            $installationPath,
            $productId,
            $oldLicense,
            $terminatedOrderId,
            $newActiveOrderId,
            $discount,
            $invoice->currency,
        );

        $this->moveAutoRenewalToNewOrder($newActiveOrderId, $invoice);

        return ['status' => 'success'];
    }

    private function executeOrders(Invoice $invoice): void
    {
        $alreadyExecuted = Order::whereIn(
            'id',
            OrderInvoiceRelation::where('invoice_id', $invoice->id)->pluck('order_id')
        )->exists();

        if (! $alreadyExecuted) {
            new OrderController()->executeOrder($invoice->id);
        }
    }

    /** Record the invoice's outstanding amount as paid via $gateway and mark it paid. Also used by admin renew. */
    public function recordPayment(Invoice $invoice, string $gateway): void
    {
        $outstanding = $invoice->outstanding();

        if ($outstanding > 0) {
            $amount = rounding($outstanding, $invoice->currency);

            Payment::create([
                'invoice_id' => 0,
                'parent_id' => 0,
                'user_id' => $invoice->user_id,
                'amount' => $amount,
                'payment_method' => $gateway,
                'payment_status' => 'success',
                'created_at' => Date::now(),
                'currency' => $invoice->currency,
            ])->invoices()->attach($invoice->id, ['amount' => $amount]);
        }

        // Asserted, not derived: the gateway has been paid in full, and
        // rounding() may legitimately record a hair less than was owed (it
        // rounds to whole units when the tax rule says so), which would
        // otherwise leave a paid invoice reading as partially paid.
        $invoice->update(['status' => 'success']);
    }

    /**
     * After an upgrade/downgrade, carry the old order's auto-renewal over to the new order
     * and take it off the terminated one.
     */
    private function moveAutoRenewalToNewOrder(int $newActiveOrderId, Invoice $invoice): void
    {
        // Transfer subscription from terminated order to new order
        $termOrderId = DB::table('terminated_order_upgrade')
            ->where('upgraded_order_id', $newActiveOrderId)->value('terminated_order_id');
        /** @var Order|null $terminatedOrder */
        $terminatedOrder = Order::find($termOrderId);
        if ($terminatedOrder) {
            $oldSub = Subscription::where('order_id', $terminatedOrder->id)->first();
            if ($terminatedOrder->order_status === 'Terminated' && $oldSub?->subscribe_id) {
                Subscription::where('order_id', $newActiveOrderId)->update([
                    'subscribe_id' => $oldSub->subscribe_id,
                    'is_subscribed' => $oldSub->is_subscribed,
                    'autoRenew_status' => $oldSub->autoRenew_status,
                    'rzp_subscription' => $oldSub->rzp_subscription,
                ]);
                $this->updateSubscriptionPriceIfNeeded($newActiveOrderId, $invoice); // @phpstan-ignore argument.type
            } elseif ($oldSub?->is_subscribed === '1') { // @phpstan-ignore identical.alwaysFalse
                Subscription::where('order_id', $newActiveOrderId)->update([
                    'is_subscribed' => $oldSub->is_subscribed,
                    'autoRenew_status' => $oldSub->autoRenew_status,
                    'rzp_subscription' => $oldSub->rzp_subscription,
                ]);
            }

            // Auto-renewal now lives on the new order only. Left on the terminated one too, both
            // rows share the gateway subscription id and the renewal webhook (which takes the
            // first match) could extend the dead order while the new one expires. Query builder,
            // as in AutoRenewalActivationService::deactivate(): these columns aren't fillable.
            if ($terminatedOrder->order_status === 'Terminated' && $oldSub) {
                DB::table('subscriptions')->where('id', $oldSub->id)->update([
                    'subscribe_id' => '', 'is_subscribed' => 0, 'autoRenew_status' => 0, 'rzp_subscription' => 0,
                ]);
            }
        }
    }

    /**
     * Check and update the subscription price if necessary.
     *
     * @param  string  $orderId  The order ID associated with the subscription.
     * @param  Invoice  $invoice  The invoice object for the subscription.
     */
    private function updateSubscriptionPriceIfNeeded($orderId, Invoice $invoice): void
    {
        $subscription = Subscription::where('order_id', $orderId)->first();

        if (! $subscription) {
            return; // No subscription found
        }

        /** @var Order $order */
        $order = Order::find($orderId);
        /** @var Product $product */
        $product = Product::find($subscription->product_id);

        if ($subscription->is_subscribed != '1') {
            return; // Subscription not active
        }

        if ($subscription->rzp_subscription != '3' && $subscription->autoRenew_status != '3') {
            return; // Subscription not eligible for price check/update
        }

        /** @var Plan $plan */
        $plan = Plan::find($subscription->plan_id);
        /** @var User $invoiceUser */
        $invoiceUser = User::find($invoice->user_id);

        if (! $subscription->subscribe_id) {
            return;
        }

        $gateway = $subscription->rzp_subscription == '3' ? 'Razorpay' : 'Stripe';

        // The same recurring amount the renewal cron and activation charge (SubscriptionController
        // ::calculateRenewalCost → processing fee → tax). The old inline version multiplied the
        // plan's whole renew_price by the licence's agents (5× for a 5-agent plan) and, for an
        // unlimited licence (serial …0000 → 0 agents), repriced the subscription to 0.
        $planDetails = userCurrencyAndPrice($invoiceUser->id, $plan);
        if (empty($planDetails['plan'])) {
            return;
        }
        $cost = resolve(SubscriptionController::class)->calculateRenewalCost($subscription, $planDetails, $order);
        $cost = ProcessingFee::addTo($cost, strtolower($gateway));
        $tax = $this->calculateTax($subscription->product_id, (string) $invoiceUser->state, (string) $invoiceUser->country);
        $amount = rounding((new InvoiceController)->calculateTotal($tax['value'], $cost));
        $renewPrice = intval(calculateUnitCost($invoice->currency, (float) $amount));

        // The gateway driver fetches the live subscription, skips the update when
        // the price/interval already matches or the subscription is inactive, and
        // (Stripe) cancels + flags raw['cancelled'] if the change deactivates it.
        $result = resolve(SubscriptionService::class)->updateSubscriptionPrice(
            $gateway,
            $subscription->subscribe_id,
            new SubscriptionRequest(
                amountMinor: $renewPrice,
                currency: $invoice->currency,
                intervalDays: (int) $plan->days,
                planName: $product->name,
            )
        );

        if (($result->raw['cancelled'] ?? false) === true) {
            Subscription::where('id', $subscription->id)->update([
                'is_subscribed' => '0',
                'autoRenew_status' => '0',
                'subscribe_id' => null,
            ]);
        }
    }
}
