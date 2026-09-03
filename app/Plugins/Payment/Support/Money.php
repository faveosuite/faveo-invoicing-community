<?php

declare(strict_types=1);

namespace App\Plugins\Payment\Support;

/**
 * Currency-aware conversion between major units (49.99) and the minor units
 * (4999) that payment gateways charge in. Self-contained so the package carries
 * no dependency on the host application's money helpers.
 */
final class Money
{
    /**
     * Number of decimal places (the ISO-4217 "exponent") for currencies that
     * are not the usual 2. Everything not listed defaults to 2.
     *
     * @var array<string, int>
     */
    private const array EXPONENTS = [
        // zero-decimal
        'BIF' => 0, 'CLP' => 0, 'DJF' => 0, 'GNF' => 0, 'ISK' => 0, 'JPY' => 0, 'KMF' => 0,
        'KRW' => 0, 'PYG' => 0, 'RWF' => 0, 'UGX' => 0, 'UYI' => 0, 'VND' => 0, 'VUV' => 0,
        'XAF' => 0, 'XOF' => 0, 'XPF' => 0,
        // one-decimal
        'MGA' => 1, 'MRU' => 1,
        // three-decimal
        'BHD' => 3, 'IQD' => 3, 'JOD' => 3, 'KWD' => 3, 'LYD' => 3, 'OMR' => 3, 'TND' => 3,
        // four-decimal
        'CLF' => 4,
    ];

    /** Decimal places used by $currency. */
    public static function decimals(string $currency): int
    {
        return self::EXPONENTS[strtoupper($currency)] ?? 2;
    }

    /** Convert a major-unit amount to the integer minor units a gateway charges. */
    public static function toMinor(float $amount, string $currency): int
    {
        $decimals = self::decimals($currency);

        return (int) round($amount * (10 ** $decimals));
    }
}
