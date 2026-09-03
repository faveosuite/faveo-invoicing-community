<?php

declare(strict_types=1);

namespace App\Plugins\Payment\Dto;

use App\Plugins\Payment\Support\Money;

/**
 * An immutable request to charge a payer once, in plain data only.
 *
 * The amount is given in MAJOR currency units (e.g. 49.99, not 4999); each
 * gateway converts to minor units itself via {@see Money}.
 */
final readonly class PaymentRequest
{
    /**
     * @param  float  $amount  Amount to charge, in major currency units.
     * @param  string  $currency  ISO-4217 code, e.g. "USD".
     * @param  string  $reference  Merchant reference shown on the gateway, e.g. an invoice number.
     * @param  array<string, scalar>  $metadata  Opaque key/values echoed back on confirmation (e.g. invoice id).
     * @param  bool  $saveForFutureUse  Save the payment method for a later off-session
     *                                  charge (e.g. auto-renewal), as part of this same payment.
     */
    public function __construct(
        public float $amount,
        public string $currency,
        public string $reference,
        public ?Customer $customer = null,
        public ?string $description = null,
        public ?string $returnUrl = null,
        public array $metadata = [],
        public bool $saveForFutureUse = false,
    ) {
    }
}
