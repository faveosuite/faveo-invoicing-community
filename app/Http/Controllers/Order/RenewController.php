<?php

namespace App\Http\Controllers\Order;

use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
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
            if ($isAgentIncrease) {
                resolve(SubscriptionRenewalService::class)->extendDates($sub, $days); // @phpstan-ignore argument.type
            }

            $invoice = $this->invoiceBySubscriptionId($id, $planid, $cost, $currency, $agents);

            if ($isAgentIncrease) {
                return $sub;
            }

            return $invoice; // @phpstan-ignore return.type
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

    public function getPriceByProductId(int $productid, int $userid): float|int
    {
        try {
            $product = $this->getProductById($productid);
            if (! $product instanceof Product) {
                throw new Exception(__('message.product_removed_database'));
            }

            $currency = $this->getUserCurrencyById($userid); // @phpstan-ignore method.notFound
            $price = $product->price()->where('currency', $currency)->first();
            if (! $price) {
                throw new Exception(__('message.price_removed_database'));
            }

            $cost = $price->sales_price;
            if (! $cost) {
                return $price->regular_price; // @phpstan-ignore property.notFound
            }

            return (float) $cost;
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
            $plan = Plan::findOrFail($planId);

            $existingUnpaidInvoice = $this->checkExistingUnpaidInvoice($sub, $planId);
            if ($existingUnpaidInvoice instanceof InvoiceItem) {
                return successResponse(trans('message.existings_invoice'), ['invoice_id' => $existingUnpaidInvoice->invoice_id]);
            }

            /** @var User $authUser */
            $authUser = Auth::user();
            $planDetails = userCurrencyAndPrice($authUser->id, $plan);
            $price = $planDetails['plan']->renew_price;
            $currency = $planDetails['currency'];
            $noOfAgentsPerPlan = (int) $planDetails['plan']->no_of_agents;

            $agents = InvoiceItem::whereHas('invoice', fn (Builder $q) => $q->whereHas('orders', fn (Builder $q) => $q->where('orders.id', $sub->order_id)) // @phpstan-ignore argument.templateType
            )
                ->orderByDesc('id')
                ->value('agents');

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

    private function checkExistingUnpaidInvoice(Subscription $subscription, int $planId): ?InvoiceItem
    {
        $invoice_id = OrderInvoiceRelation::where('order_id', $subscription->order_id)->latest()->value('invoice_id');

        return InvoiceItem::whereHas('invoice', function (Builder $query) use ($invoice_id, $planId): void {
            $query->where('invoice_id', $invoice_id)
                ->where('is_renewed', 1)
                ->where('status', 'pending')
                ->where('plan_id', $planId);
        })
            ->latest('created_at')
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
