<?php

declare(strict_types=1);

namespace App\Services\Tax;

/**
 * Pure tax arithmetic — a direct port of WooCommerce's WC_Tax calculation
 * core. It knows nothing about the database, customers or products: given a
 * price and a set of normalised rates it returns the tax amount per rate.
 *
 * A normalised rate is an array: ['id' => int|string, 'rate' => float,
 * 'label' => string, 'compound' => bool].
 *
 * Non-compound rates are charged on the base price; compound rates stack on
 * top of the running (price + non-compound tax) total.
 */
class TaxEngine
{
    /**
     * @param  array<int, array>  $rates
     * @return array<int|string, float> tax amount keyed by rate id
     */
    public function calc(float $price, array $rates, bool $pricesIncludeTax = false): array
    {
        return $pricesIncludeTax
            ? $this->calcInclusive($price, $rates)
            : $this->calcExclusive($price, $rates);
    }

    /**
     * Price is net (tax added on top). e.g. 100 @ 20% -> tax 20.
     *
     * @return array<int|string, float>
     */
    public function calcExclusive(float $price, array $rates): array
    {
        $taxes = [];

        foreach ($rates as $rate) {
            if (! empty($rate['compound'])) {
                continue;
            }

            $taxes[$rate['id']] = $price * ((float) $rate['rate'] / 100);
        }

        $preCompoundTotal = array_sum($taxes);

        foreach ($rates as $rate) {
            if (empty($rate['compound'])) {
                continue;
            }

            $priceIncTax = $price + $preCompoundTotal;
            $taxes[$rate['id']] = $priceIncTax * ((float) $rate['rate'] / 100);
            $preCompoundTotal = array_sum($taxes);
        }

        return $taxes;
    }

    /**
     * Price is gross (tax already embedded). e.g. 120 @ 20% -> tax 20, net 100.
     * Compound rates are unwound first (in reverse), then regular rates split
     * proportionally — matching WooCommerce exactly.
     *
     * @return array<int|string, float>
     */
    public function calcInclusive(float $price, array $rates): array
    {
        $taxes = [];
        $compoundRates = [];
        $regularRates = [];

        foreach ($rates as $rate) {
            $taxes[$rate['id']] = 0.0;
            if (! empty($rate['compound'])) {
                $compoundRates[$rate['id']] = (float) $rate['rate'];
            } else {
                $regularRates[$rate['id']] = (float) $rate['rate'];
            }
        }

        $compoundRates = array_reverse($compoundRates, preserve_keys: true);
        $nonCompoundPrice = $price;

        foreach ($compoundRates as $id => $compoundRate) {
            $taxAmount = $nonCompoundPrice - ($nonCompoundPrice / (1 + ($compoundRate / 100)));
            $taxes[$id] += $taxAmount;
            $nonCompoundPrice -= $taxAmount;
        }

        $regularTaxRate = 1 + (array_sum($regularRates) / 100);

        foreach ($regularRates as $id => $regularRate) {
            $theRate = ($regularRate / 100) / $regularTaxRate;
            $netPrice = $price - ($theRate * $nonCompoundPrice);
            $taxes[$id] += $price - $netPrice;
        }

        return $taxes;
    }
}
