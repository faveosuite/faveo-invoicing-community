<?php

declare(strict_types=1);

namespace App\Plugins\Payment\Dto;

/**
 * The outcome of capturing/refunding a payment ({@see \App\Plugins\Payment\Contracts\PaymentGateway::capturePayment}).
 *
 * $paid is the only field a caller must check; $reference carries the gateway
 * transaction id (Stripe PaymentIntent id, Razorpay payment id) for records.
 */
final readonly class PaymentResult
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public bool $paid,
        public string $gateway,
        public ?string $reference = null,
        public string $status = '',
        public array $raw = [],
    ) {
    }
}
