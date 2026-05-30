<?php

namespace App\Plugins\Payment\Dto;

/**
 * The payer's details, as a plain value object. Every field is optional — a
 * gateway uses what it is given and falls back sensibly for the rest.
 */
final class Customer
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?string $line1 = null,
        public readonly ?string $city = null,
        public readonly ?string $state = null,
        public readonly ?string $postalCode = null,
        public readonly ?string $country = null,
    ) {
    }
}
