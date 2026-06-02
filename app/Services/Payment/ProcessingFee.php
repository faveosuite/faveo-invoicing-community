<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\DB;

/**
 * Processing fee — the single source of truth for payment-gateway fees.
 *
 * A processing fee is a merchant surcharge to recover gateway costs: a single
 * percentage per gateway (currency-independent), set by the admin and charged
 * ON TOP of the order total, so the customer pays it and the merchant nets the
 * full amount.
 *
 * The fee lives in the gateway's own table (e.g. `stripe`, `razorpay`) — the
 * only thing that table is used for, since currency *support* is driven by the
 * plugin config, not these rows. It is stored uniformly across the rows so any
 * single read is authoritative.
 */
class ProcessingFee
{
    /** Configured fee percentage for a gateway (0 when unset/unknown). */
    public static function percent(?string $gateway): float
    {
        if (! $gateway) {
            return 0.0;
        }

        try {
            return (float) DB::table(strtolower($gateway))->value('processing_fee');
        } catch (\Throwable) {
            return 0.0;
        }
    }

    /** Persist the admin-configured fee, written uniformly across the gateway's rows. */
    public static function store(string $gateway, float $percent): void
    {
        DB::table(strtolower($gateway))->update(['processing_fee' => $percent]);
    }

    /** `$base` plus the gateway's fee — the amount actually charged. */
    public static function addTo(float $base, ?string $gateway): float
    {
        return (float) rounding($base * (1 + self::percent($gateway) / 100));
    }

    /** Just the fee portion added when charging `$base` on `$gateway`. */
    public static function amount(float $base, ?string $gateway): float
    {
        return round(self::addTo($base, $gateway) - $base, 2);
    }

    /** Format a fee percentage for storage on an invoice: 2.5 -> "2.5%". */
    public static function label(float $percent): string
    {
        return rtrim(rtrim(number_format($percent, 2, '.', ''), '0'), '.').'%';
    }

    /**
     * Fee amount embedded in a fee-inclusive total, given the stored fee (a
     * "2.5%" string or numeric percent). grand_total is persisted fee-inclusive,
     * so the fee is the part above the pre-fee total — never a % of subtotal.
     */
    public static function fromInclusive(float $inclusiveTotal, $fee): float
    {
        $pct = is_numeric($fee)
            ? (float) $fee
            : (float) filter_var((string) $fee, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        return $pct > 0 ? $inclusiveTotal - ($inclusiveTotal / (1 + $pct / 100)) : 0.0;
    }
}
