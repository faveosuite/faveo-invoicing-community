<?php

namespace App\Plugins\Payment\Dto;

/**
 * An immutable request to charge a payer once, in plain data only.
 *
 * The amount is given in MAJOR currency units (e.g. 49.99, not 4999); each
 * gateway converts to minor units itself via {@see \App\Plugins\Payment\Support\Money}.
 */
final class PaymentRequest
{
    /**
     * @param  float  $amount  Amount to charge, in major currency units.
     * @param  string  $currency  ISO-4217 code, e.g. "USD".
     * @param  string  $reference  Merchant reference shown on the gateway, e.g. an invoice number.
     * @param  array<string, scalar>  $metadata  Opaque key/values echoed back on confirmation (e.g. invoice id).
     */
    public function __construct(
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $reference,
        public readonly ?Customer $customer = null,
        public readonly ?string $description = null,
        public readonly ?string $returnUrl = null,
        public readonly array $metadata = [],
    ) {
    }
}
