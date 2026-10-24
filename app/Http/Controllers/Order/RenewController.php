<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderAbility;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Services\Payment\PostPaymentService;
use App\Services\SubscriptionRenewalService;
use App\Traits\TaxCalculation;
use App\User;
use Auth;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Session;

class RenewController extends BaseRenewController
{
    use TaxCalculation;

    protected Subscription $sub;

    protected Plan $plan;

    protected Order $order;

    protected Invoice $invoice;

    protected InvoiceItem $item;

    protected Product $product;

    protected User $user;

    public function __construct()
    {
        $sub = new Subscription;
        $this->sub = $sub;

        $plan = new Plan;
        $this->plan = $plan;

        $order = new Order;
        $this->order = $order;

        $invoice = new Invoice;
        $this->invoice = $invoice;

        $item = new InvoiceItem;
        $this->item = $item;

        $product = new Product;
        $this->product = $product;

        $user = new User;
        $this->user = $user;
    }

    public function renew(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'plan' => ['required', 'integer'],
            'payment_method' => ['required', 'string'],
            'cost' => ['required', 'numeric'],
        ]);

        try {
            $sub = Subscription::where('order_id', $id)->firstOrFail();
            $order = Order::findOrFail($id);

            // Same rule as the admin Renew button — see OrderPolicy::adminRenew.
            $access = Gate::inspect(OrderAbility::AdminRenew, $order);
            if ($access->denied()) {
                return deniedResponse($access);
            }

            $this->renewBySubId(
                $sub->id,
                (int) $request->input('plan'),
                $request->input('payment_method'),
                (float) $request->input('cost'),
                (string) $request->input('code', ''),
            );

            return successResponse(__('message.renewed_successfully'));
        } catch (Exception $exception) {
            return errorResponse([$exception->getMessage()]);
        }
    }

    // Renew From admin panel
    public function renewBySubId(int $id, int $planid, string $payment_method, float|int $cost, string $code, bool $isAgentIncrease = true, ?int $agents = null): Subscription|InvoiceItem
    {
        try {
            /** @var Plan $plan */
            $plan = $this->plan->find($planid);
            $days = $plan->days;
            /** @var Subscription $sub */
            $sub = $this->sub->find($id);
            $currency = userCurrencyAndPrice($sub->user_id, $plan)['currency'];

            // All or nothing: dates extended with no invoice behind them (invoice creation
            // failed) would be extended again by a retry.
            return DB::transaction(function () use ($sub, $days, $id, $planid, $cost, $currency, $agents, $isAgentIncrease, $payment_method): Subscription|InvoiceItem {
                if ($isAgentIncrease) {
                    resolve(SubscriptionRenewalService::class)->extendDates($sub, $days); // @phpstan-ignore argument.type
                }

                $invoice = $this->invoiceBySubscriptionId($id, $planid, $cost, $currency, $agents);

                if ($isAgentIncrease) {
                    if (! $invoice instanceof InvoiceItem || ! $invoice->invoice) {
                        throw new Exception(__('message.something_went_wrong'));
                    }

                    // Admin renew has already extended the dates above, so its invoice is settled
                    // here with the method the admin chose. Left pending, paying it later would run
                    // successRenew() and extend the dates a second time.
                    resolve(PostPaymentService::class)->recordPayment($invoice->invoice, $payment_method);

                    return $sub;
                }

                return $invoice; // @phpstan-ignore return.type
            });
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    // Renewal from ClienT Panel
    public function successRenew(Invoice $invoice): void
    {
        try {
            $invoice->status = 'success';
            $invoice->save();

            $orderId = OrderInvoiceRelation::where('invoice_id', $invoice->id)->value('order_id');

            $newPlanId = InvoiceItem::where('invoice_id', $invoice->id)->value('plan_id');

            $sub = Subscription::where('order_id', $orderId)->firstOrFail();

            if ($newPlanId) {
                $sub->plan_id = $newPlanId;
                $sub->save();
            }

            $days = Plan::findOrFail($sub->plan_id)->days;

            resolve(SubscriptionRenewalService::class)->extendDates($sub, (int) $days);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    // Tuesday, June 13, 2017 08:06 AM

    public function getProductById(int $id): ?Product
    {
        try {
            $product = $this->product->where('id', $id)->first();
            if ($product) {
                return $product;
            }
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }

        return null;
    }

    public function getUserById(int $id): ?User
    {
        try {
            $user = $this->user->where('id', $id)->first();
            if ($user) {
                return $user;
            }
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }

        return null;
    }

    public function createOrderInvoiceRelation(int $orderid, int $invoiceid): void
    {
        try {
            $relation = new OrderInvoiceRelation;
            $relation->create([
                'order_id' => $orderid,
                'invoice_id' => $invoiceid,
            ]);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function renewByClient(int $id, Request $request): JsonResponse
    {
        try {
            $request->validate(
                ['plan' => ['required']],
                ['plan.required' => __('validation.plan_renewal.plan_required')]
            );

            $planId = (int) $request->input('plan');
            $sub = Subscription::findOrFail($id);
            $order = Order::find($sub->order_id);
            if (! $order instanceof Order) {
                return errorResponse(__('message.unauthorized_action'), 403);
            }

            // Ownership, terminated order, deleted cloud instance, nothing to renew — see OrderPolicy::renew.
            $access = Gate::inspect(OrderAbility::Renew, $order);
            if ($access->denied()) {
                return deniedResponse($access);
            }

            $plan = Plan::findOrFail($planId);

            // Already asked to renew on this plan and not paid yet — send them to that invoice.
            $existingUnpaidInvoice = $this->checkExistingUnpaidInvoice($sub, $planId);
            if ($existingUnpaidInvoice instanceof InvoiceItem) {
                return successResponse(trans('message.existings_invoice'), ['invoice_id' => $existingUnpaidInvoice->invoice_id]);
            }

            // Any other unpaid invoice on the order (a renewal on another plan, an agent change)
            // must be settled or cancelled first — the same rule as admin renew. Otherwise plan A
            // then plan B left two renewal invoices, and paying both extended the dates twice.
            $unpaid = $order->latestUnpaidInvoice();
            if ($unpaid) {
                return errorResponse(__('message.renew_blocked_unpaid_invoice', ['number' => $unpaid->number]));
            }

            /** @var User $authUser */
            $authUser = Auth::user();
            $planDetails = userCurrencyAndPrice($authUser->id, $plan);
            $price = $planDetails['plan']->renew_price;
            $currency = $planDetails['currency'];
            $noOfAgentsPerPlan = (int) $planDetails['plan']->no_of_agents;

            $agents = $this->orderAgents((int) $sub->order_id);

            $cost = ($agents > 0 && $noOfAgentsPerPlan > 0)
                ? ($price / $noOfAgentsPerPlan) * (int) $agents
                : $price;

            $items = $this->invoiceBySubscriptionId($id, $planId, $cost, $currency, $agents ?: null);
            $invoiceid = $items->invoice_id; // @phpstan-ignore property.notFound

            return successResponse('', ['invoice_id' => $invoiceid]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * The order's latest invoice when it is a still-pending RENEWAL on this plan. Agent-change
     * invoices also carry is_renewed=1 (metadata.type = agent_alteration) — handing one back here
     * made paying "the renewal" change agents instead.
     */
    private function checkExistingUnpaidInvoice(Subscription $subscription, int $planId): ?InvoiceItem
    {
        $invoiceId = OrderInvoiceRelation::where('order_id', $subscription->order_id)->latest()->value('invoice_id');

        return InvoiceItem::where('invoice_id', $invoiceId)
            ->where('plan_id', $planId)
            ->whereHas('invoice', fn (Builder $query) => $query
                ->where('is_renewed', 1)
                ->where('status', 'pending')
                ->whereNull('metadata->type'))
            ->latest('id')
            ->first();
    }

    public function setSession(int $sub_id, int $planid): void
    {
        Session::put('subscription_id', $sub_id);
        Session::put('plan_id', $planid);
    }

    // Update License Expiry Date
    public function getExpiryDate(mixed $permissions, Subscription $sub, int $days): string|Carbon
    {
        $expiry_date = '';
        if ($days > 0 && $permissions == 1) {
            $date = Date::parse($sub->ends_at);
            $expiry_date = $date->addDays($days);
        }

        return $expiry_date;
    }

    // Update Updates Expiry Date
    public function getUpdatesExpiryDate(mixed $permissions, Subscription $sub, int $days): string|Carbon
    {
        $expiry_date = '';
        if ($days > 0 && $permissions == 1) {
            $date = Date::parse($sub->update_ends_at);
            $expiry_date = $date->addDays($days);
        }

        return $expiry_date;
    }

    // Update Support Expiry Date
    public function getSupportExpiryDate(mixed $permissions, Subscription $sub, int $days): string|Carbon
    {
        $expiry_date = '';
        if ($days > 0 && $permissions == 1) {
            $date = Date::parse($sub->support_ends_at);
            $expiry_date = $date->addDays($days);
        }

        return $expiry_date;
    }
}
