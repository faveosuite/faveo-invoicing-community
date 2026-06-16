<?php

namespace App\Plugins\Payment\Dto;

/**
 * The payer's details, as a plain value object. Every field is optional — a
 * gateway uses what it is given and falls back sensibly for the rest.
 */
final readonly class Customer
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $line1 = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $postalCode = null,
        public ?string $country = null,
    ) {
    }
}
