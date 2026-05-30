<?php

namespace App\Plugins\Payment\Dto;

/**
 * The outcome of capturing/refunding a payment ({@see \App\Plugins\Payment\Contracts\PaymentGateway::capturePayment}).
 *
 * $paid is the only field a caller must check; $reference carries the gateway
 * transaction id (Stripe PaymentIntent id, Razorpay payment id) for records.
 */
final class PaymentResult
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public readonly bool $paid,
        public readonly string $gateway,
        public readonly ?string $reference = null,
        public readonly string $status = '',
        public readonly array $raw = [],
    ) {
    }
}
