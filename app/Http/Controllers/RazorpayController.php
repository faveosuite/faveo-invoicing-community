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
    /**
     * @param  bool  $immediate  True when authorizing right after a fresh purchase, where
     *                           the current period was already paid for moments ago — no
     *                           upfront addon charge, and the recurring schedule starts at
     *                           the current period's own expiry (not one cycle beyond it).
     *                           False (default) is the renewal:cron case: the current
     *                           period's payment is actually due now, collected as the
     *                           upfront addon, with the recurring schedule starting the
     *                           cycle after that.
     */
    public function handleRzpAutoPay(mixed $cost, mixed $days, mixed $product_name, mixed $invoice, mixed $currency, mixed $subscription, mixed $user, mixed $order, mixed $endDate, mixed $productDetails, bool $immediate = false): SubscriptionResult
    {
        $now = Date::now();
        $periodEnd = Date::parse($subscription->update_ends_at);
        $targetStart = $immediate ? $periodEnd->copy() : $periodEnd->copy()->addDays(round((int) $days));

        // start_at must be at least 15 minutes in the future for Razorpay; if the subscription
        // has already expired or ends within 15 minutes, omit start_at so it starts right away.
        $startAt = $targetStart->gt($now->copy()->addMinutes(15)) ? (int) $targetStart->timestamp : null;

        // expire_by is when customer authorization must be completed.
        // Razorpay API enforces:
        // 1. Must be >= now + 15 minutes.
        // 2. If start_at is set, expire_by MUST be <= start_at.
        // Allow up to 7 days to authorize, capped at start_at - 15 minutes if start_at is set.
        $expireBy = null;
        if ($startAt !== null) {
            $maxExpire = $targetStart->copy()->subMinutes(15);
            $defaultExpire = $now->copy()->addDays(7);
            $chosenExpire = $defaultExpire->lt($maxExpire) ? $defaultExpire : $maxExpire;
            if ($chosenExpire->gt($now->copy()->addMinutes(15))) {
                $expireBy = (int) $chosenExpire->timestamp;
            }
        } else {
            $expireBy = (int) $now->copy()->addDays(7)->timestamp;
        }

        return resolve(SubscriptionService::class)->createSubscription('Razorpay', new SubscriptionRequest(
            amountMinor: (int) $cost,
            currency: $currency,
            intervalDays: (int) $days,
            planName: $product_name,
            startAt: $startAt,
            expireBy: $expireBy,
            // Without start_at Razorpay bills the first cycle on authorization, and that charge
            // IS this renewal — an upfront addon on top would charge the customer twice.
            includeUpfrontCharge: ! $immediate && $startAt !== null,
        ));
    }
}
