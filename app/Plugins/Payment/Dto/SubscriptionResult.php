<?php

declare(strict_types=1);

namespace App\Plugins\Payment\Dto;

/**
 * The outcome of a subscription operation.
 *
 * {@see $status} is the gateway's OWN status string (Stripe "active" /
 * "incomplete", Razorpay "created" / "authenticated") — deliberately not
 * normalised, because callers branch on these gateway-specific states. {@see $raw}
 * carries the full gateway payload so callers can read gateway-specific fields
 * (Stripe latest_invoice / hosted_invoice_url, Razorpay short_url) without the
 * package having to model every one of them.
 */
final readonly class SubscriptionResult
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $gateway,
        public ?string $id,
        public string $status,
        public array $raw = [],
    ) {
    }
}
