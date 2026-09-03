<?php

declare(strict_types=1);

namespace App\Plugins\Payment\Dto;

/**
 * A request to open a recurring subscription on a gateway.
 *
 * Plain value object — no application coupling. The per-cycle charge is given in
 * MINOR units ({@see $amountMinor}, e.g. 4999 = 49.99 USD), because that is the
 * unit every gateway's subscription/plan API consumes directly; carrying it
 * pre-converted avoids any lossy major↔minor round-trip on recurring money.
 *
 * Gateways take only the fields they need: Stripe uses {@see $paymentMethodReference}
 * (the id of the PaymentIntent that saved a card via setup_future_usage=off_session;
 * its customer + payment method drive the subscription); Razorpay uses
 * {@see $startAt} / {@see $expireBy} / {@see $totalCount}.
 */
final readonly class SubscriptionRequest
{
    /**
     * @param  int  $amountMinor  Per-cycle charge in minor units (gateway-native).
     * @param  int  $intervalDays  Billing period length, in days.
     * @param  string|null  $paymentMethodReference  Stripe: the PaymentIntent id that saved the card.
     * @param  int|null  $startAt  Razorpay: first-charge time (unix timestamp).
     * @param  int|null  $expireBy  Razorpay: subscription expiry (unix timestamp).
     * @param  int  $totalCount  Razorpay: number of billing cycles.
     * @param  bool  $includeUpfrontCharge  Razorpay: add a one-time addon charge, equal to
     *                                      one cycle, collected immediately when the customer
     *                                      authorizes. Correct when the current period's payment
     *                                      is actually due now (e.g. renewing right at expiry);
     *                                      must be false when the customer already paid for the
     *                                      current period moments ago (e.g. opted in at checkout)
     *                                      — otherwise it double-charges them.
     * @param  array<string, scalar>  $metadata
     */
    public function __construct(
        public int $amountMinor,
        public string $currency,
        public int $intervalDays,
        public string $planName,
        public ?string $paymentMethodReference = null,
        public ?int $startAt = null,
        public ?int $expireBy = null,
        public int $totalCount = 100,
        public bool $includeUpfrontCharge = true,
        public array $metadata = [],
    ) {
    }
}
