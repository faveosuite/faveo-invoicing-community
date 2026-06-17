<?php

namespace App\Services\Tax;

use App\Model\Payment\TaxClass;
use App\Model\Payment\TaxOption;
use App\Model\Payment\TaxProductRelation;

/**
 * Application-facing tax façade. Callers (cart, invoice, renewal) use this
 * instead of touching rates or the engine directly. It:
 *   - decides whether tax applies at all (global switch + customer exemption),
 *   - finds the product's tax class and the rates for the customer's location,
 *   - runs the engine (inclusive or exclusive),
 *   - returns a structured breakdown plus a legacy-shaped condition so the
 *     existing cart code keeps working during the migration.
 */
class TaxService
{
    public function __construct(
        private readonly TaxRateResolver $resolver,
        private readonly TaxEngine $engine,
    ) {
    }

    /**
     * Full tax breakdown for a single taxable amount (one invoice line total).
     *
     * @return array{
     *   applicable: bool,
     *   prices_include_tax: bool,
     *   rates: array,
     *   total: float,
     *   percent: float,
     *   name: string,
     *   lines: array<int, array{tax_rate_id:int, label:string, rate:float, compound:bool, amount:float}>
     * }
     */
    public function calculate(float $amount, int $productId, $user): array
    {
        $empty = [
            'applicable' => false,
            'prices_include_tax' => false,
            'rates' => [],
            'total' => 0.0,
            'percent' => 0.0,
            'name' => '',
            'lines' => [],
        ];

        $option = TaxOption::find(1);
        if (! $option || (int) $option->tax_enable !== 1) {
            return $empty;
        }

        if ($user && ! empty($user->is_tax_exempt)) {
            return $empty;
        }

        $taxClass = $this->taxClassFor($productId);
        if ($taxClass === null) {
            return $empty; // product is not taxable
        }

        $rates = $this->resolver->ratesForCustomer($taxClass, $user);
        if ($rates === []) {
            return $empty;
        }

        $pricesIncludeTax = (int) $option->inclusive === 1;
        $taxes = $this->engine->calc($amount, $rates, $pricesIncludeTax);

        $lines = [];
        $percent = 0.0;
        $labels = [];
        foreach ($rates as $rate) {
            $amountForRate = (float) ($taxes[$rate['id']] ?? 0);
            $lines[] = [
                'tax_rate_id' => (int) $rate['id'],
                'label' => $rate['label'],
                'rate' => (float) $rate['rate'],
                'compound' => (bool) $rate['compound'],
                'amount' => $amountForRate,
            ];
            $percent += (float) $rate['rate'];
            $labels[] = $rate['label'];
        }

        return [
            'applicable' => true,
            'prices_include_tax' => $pricesIncludeTax,
            'rates' => $rates,
            'total' => array_sum($taxes),
            'percent' => $percent,
            'name' => implode(' + ', array_unique($labels)),
            'lines' => $lines,
        ];
    }

    /**
     * Backward-compatible condition matching the old TaxCalculation trait
     * shape: ['name' => 'GST', 'type' => 'tax', 'value' => '18%'] (or a 'null'
     * sentinel when no tax applies).
     */
    public function legacyCondition(int $productId, $user, bool $fromAdminPanel = false): array
    {
        $result = $this->calculate(0.0, $productId, $user); // amount irrelevant for percent/name

        if (! $result['applicable']) {
            return $fromAdminPanel
                ? ['name' => 'null', 'value' => '0%']
                : ['name' => 'null', 'type' => 'tax', 'value' => '0%'];
        }

        $value = rtrim(rtrim(number_format($result['percent'], 4, '.', ''), '0'), '.').'%';

        return $fromAdminPanel
            ? ['name' => $result['name'], 'value' => $value]
            : ['name' => $result['name'], 'type' => 'tax', 'value' => $value];
    }

    /**
     * The tax-class slug a product belongs to, or null when the product is not
     * taxable (no tax_product_relation row).
     */
    public function taxClassFor(int $productId): ?string
    {
        $relation = TaxProductRelation::where('product_id', $productId)->first();
        if (! $relation) {
            return null;
        }

        $slug = TaxClass::where('id', $relation->tax_class_id)->value('slug');

        return $slug ?? '';
    }
}
