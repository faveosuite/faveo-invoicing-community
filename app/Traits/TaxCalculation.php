<?php

namespace App\Traits;

use App\Model\Payment\TaxOption;
use App\Services\Tax\TaxService;
use Auth;
use Exception;
use Throwable;

/**
 * Backward-compatible adapter over the generic tax engine
 * (App\Services\Tax\*). The methods keep their original signatures and return
 * shapes so existing callers (cart, invoice, renewal, post-payment) work
 * unchanged, but all calculation now flows through TaxService / TaxEngine —
 * no India/GST hardcoding, real compound + inclusive support, and no integer
 * truncation of money.
 *
 * New code should depend on App\Services\Tax\TaxService directly.
 */
trait TaxCalculation
{
    /**
     * Resolve the tax condition for a product + customer location.
     *
     * @return array{name:string, type?:string, value:string}
     */
    public function calculateTax($productid, $user_state = '', $user_country = '', $taxCaluculationFromAdminPanel = false)
    {
        try {
            $user = $this->taxUserFromLocation($user_state, $user_country);

            return resolve(TaxService::class)->legacyCondition((int) $productid, $user, (bool) $taxCaluculationFromAdminPanel);
        } catch (Throwable $ex) {
            resolve('log')->warning('calculateTax failed: '.$ex->getMessage());

            return $taxCaluculationFromAdminPanel
                ? ['name' => 'null', 'value' => '0%']
                : ['name' => 'null', 'type' => 'tax', 'value' => '0%'];
        }
    }

    /**
     * Add tax to a total given a percentage label (e.g. "18%" or "9%,9%").
     * Honours the global "prices entered with tax" (inclusive) setting and,
     * unlike the old implementation, never truncates the amount to an integer.
     */
    public function calculateTotal($rate, $total)
    {
        try {
            $total = (float) $total;
            $option = TaxOption::find(1);

            // Prices already include tax — nothing to add on top.
            if ($option && (int) $option->inclusive === 1) {
                return $total;
            }

            $percent = $this->sumPercent($rate);

            return $total + ($total * $percent / 100);
        } catch (Throwable $ex) {
            resolve('log')->warning($ex->getMessage());

            throw new Exception($ex->getMessage());
        }
    }

    /**
     * Tax amount for a single rate against a price. Retained for invoice
     * display helpers.
     */
    public static function taxValue($rate, $price, $round = true)
    {
        try {
            if (! $rate || ! is_numeric($price)) {
                return 0;
            }

            $rate = floatval(str_replace('%', '', (string) $rate));

            return $price * ($rate / 100);
        } catch (Throwable) {
            return 0;
        }
    }

    /** Sum a comma-separated percentage label into a single float. */
    private function sumPercent($rate): float
    {
        $percent = 0.0;
        foreach (explode(',', (string) $rate) as $part) {
            $part = trim(str_replace('%', '', $part));
            if ($part !== '' && is_numeric($part)) {
                $percent += (float) $part;
            }
        }

        return $percent;
    }

    /**
     * Build the lightweight customer object the tax engine needs from a
     * state/country pair, carrying the authenticated user's exemption flag
     * when available.
     */
    private function taxUserFromLocation($state, $country): object
    {
        return (object) [
            'country' => $country,
            'state' => $state,
            'zip' => Auth::user()?->zip ?? '',
            'city' => Auth::user()?->city ?? '',
            'is_tax_exempt' => (bool) (Auth::user()?->is_tax_exempt ?? false),
        ];
    }
}
