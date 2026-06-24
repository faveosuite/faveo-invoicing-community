<?php

namespace App\Http\Controllers;

use App\Model\Common\State;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Payment\TaxByState;
use App\Plugins\Payment\Dto\SubscriptionRequest;
use App\Plugins\Payment\Dto\SubscriptionResult;
use App\Services\Payment\SubscriptionService;
use App\Traits\Payment\PostPaymentHandle;
use App\User;
use Auth;
use Illuminate\Support\Facades\Date;

class RazorpayController extends Controller
{
    use PostPaymentHandle;

    /**
     * @var Invoice
     */
    public $invoice;

    /**
     * @var InvoiceItem
     */
    public $invoiceItem;

    public function __construct()
    {
        $invoice = new Invoice;
        $this->invoice = $invoice;

        $invoiceItem = new InvoiceItem;
        $this->invoiceItem = $invoiceItem;
    }

    public function getState(mixed $country, mixed $stateCode): mixed
    {
        /** @var User $authUser3 */
        $authUser3 = Auth::user();
        if ($authUser3->country != 'IN') {
            return State::where('country_code', $country)->where('iso2', $stateCode)->value('state_subdivision_name');
        }

        return TaxByState::where('state_code', $authUser3->state)->value('state');
    }

    /**
     * Create a recurring Razorpay subscription for autopay.
     *
     * Thin adapter over the centralized {@see SubscriptionService}
     * (which drives the payment package's RazorpayGateway). Returns a
     * {@see SubscriptionResult} — callers read ->status,
     * ->id and ->raw['short_url']. $cost is already in minor units; start_at /
     * expire_by are derived here from the subscription's current period.
     */
    public function handleRzpAutoPay(mixed $cost, mixed $days, mixed $product_name, mixed $invoice, mixed $currency, mixed $subscription, mixed $user, mixed $order, mixed $endDate, mixed $productDetails): SubscriptionResult
    {
        return resolve(SubscriptionService::class)->createSubscription('Razorpay', new SubscriptionRequest(
            amountMinor: (int) $cost,
            currency: $currency,
            intervalDays: (int) $days,
            planName: $product_name,
            startAt: (int) Date::parse($subscription->update_ends_at)->addDays(round((int) $days))->timestamp,
            expireBy: (int) Date::parse($subscription->update_ends_at)->addDays(1)->timestamp,
        ));
    }
}
