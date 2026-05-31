<?php

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
 * (the saved payment method whose customer + default method drive the subscription);
 * Razorpay uses {@see $startAt} / {@see $expireBy} / {@see $totalCount}.
 */
final class SubscriptionRequest
{
    /**
     * @param  int  $amountMinor  Per-cycle charge in minor units (gateway-native).
     * @param  int  $intervalDays  Billing period length, in days.
     * @param  string|null  $paymentMethodReference  Stripe: saved PaymentMethod id.
     * @param  int|null  $startAt  Razorpay: first-charge time (unix timestamp).
     * @param  int|null  $expireBy  Razorpay: subscription expiry (unix timestamp).
     * @param  int  $totalCount  Razorpay: number of billing cycles.
     * @param  array<string, scalar>  $metadata
     */
    public function __construct(
        public readonly int $amountMinor,
        public readonly string $currency,
        public readonly int $intervalDays,
        public readonly string $planName,
        public readonly ?string $paymentMethodReference = null,
        public readonly ?int $startAt = null,
        public readonly ?int $expireBy = null,
        public readonly int $totalCount = 100,
        public readonly array $metadata = [],
    ) {
    }
}
