<?php

namespace App\Plugins\Payment\Dto;

/**
 * The result of opening a payment ({@see \App\Plugins\Payment\Contracts\PaymentGateway::createPayment}).
 *
 * - $id          The gateway's handle for this attempt (Stripe Checkout Session
 *                id, Razorpay Order id) — pass it back when confirming.
 * - $clientConfig Everything the client-side SDK needs, ready to hand to the
 *                frontend as-is (Stripe: client_secret + publishable key;
 *                Razorpay: Checkout options object).
 * - $raw         The untouched gateway response, for logging/debugging.
 */
final class PaymentSession
{
    /**
     * @param  array<string, mixed>  $clientConfig
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public readonly string $gateway,
        public readonly string $id,
        public readonly array $clientConfig,
        public readonly array $raw = [],
    ) {
    }
}
