<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services\Tax;

use App\Services\Tax\TaxEngine;
use PHPUnit\Framework\TestCase;

class TaxEngineTest extends TestCase
{
    private TaxEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new TaxEngine();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // --- calcExclusive ---

    public function test_exclusive_single_non_compound_rate(): void
    {
        $rates = [['id' => 1, 'rate' => 20.0, 'label' => 'VAT', 'compound' => false]];

        $taxes = $this->engine->calc(100.0, $rates, false);

        $this->assertEqualsWithDelta(20.0, $taxes[1], 0.001);
    }

    public function test_exclusive_zero_rate_returns_zero(): void
    {
        $rates = [['id' => 1, 'rate' => 0.0, 'label' => 'Exempt', 'compound' => false]];

        $taxes = $this->engine->calc(100.0, $rates, false);

        $this->assertEqualsWithDelta(0.0, $taxes[1], 0.001);
    }

    public function test_exclusive_compound_rate_stacks_on_subtotal(): void
    {
        $rates = [
            ['id' => 1, 'rate' => 10.0, 'label' => 'State',  'compound' => false],
            ['id' => 2, 'rate' => 5.0,  'label' => 'County', 'compound' => true],
        ];
        // non-compound: 100 * 10% = 10
        // compound:    (100 + 10) * 5% = 5.5
        $taxes = $this->engine->calc(100.0, $rates, false);

        $this->assertEqualsWithDelta(10.0, $taxes[1], 0.001);
        $this->assertEqualsWithDelta(5.5,  $taxes[2], 0.001);
    }

    public function test_exclusive_multiple_compound_rates_stack_sequentially(): void
    {
        $rates = [
            ['id' => 1, 'rate' => 10.0, 'label' => 'Base',    'compound' => false],
            ['id' => 2, 'rate' => 5.0,  'label' => 'County',  'compound' => true],
            ['id' => 3, 'rate' => 2.0,  'label' => 'Special', 'compound' => true],
        ];
        $taxes = $this->engine->calc(100.0, $rates, false);

        $this->assertEqualsWithDelta(10.0, $taxes[1], 0.001);
        // compound 1: (100 + 10) * 5% = 5.5; running = 15.5
        $this->assertEqualsWithDelta(5.5,  $taxes[2], 0.001);
        // compound 2: (100 + 15.5) * 2% = 2.31
        $this->assertEqualsWithDelta(2.31, $taxes[3], 0.001);
    }

    public function test_exclusive_only_compound_rates_uses_base_price(): void
    {
        $rates = [
            ['id' => 1, 'rate' => 10.0, 'label' => 'Compound only', 'compound' => true],
        ];
        // preCompoundTotal = 0; price + 0 = 100; 100 * 10% = 10
        $taxes = $this->engine->calc(100.0, $rates, false);

        $this->assertEqualsWithDelta(10.0, $taxes[1], 0.001);
    }

    public function test_exclusive_empty_rates_returns_empty_array(): void
    {
        $taxes = $this->engine->calc(100.0, [], false);

        $this->assertSame([], $taxes);
    }

    // --- calcInclusive ---

    public function test_inclusive_single_rate_extracts_correct_tax(): void
    {
        // Price 120 includes 20% VAT → tax = 120 - (120 / 1.20) = 20
        $rates = [['id' => 1, 'rate' => 20.0, 'label' => 'VAT', 'compound' => false]];

        $taxes = $this->engine->calc(120.0, $rates, true);

        $this->assertEqualsWithDelta(20.0, $taxes[1], 0.01);
    }

    public function test_inclusive_zero_rate_returns_zero(): void
    {
        $rates = [['id' => 1, 'rate' => 0.0, 'label' => 'Exempt', 'compound' => false]];

        $taxes = $this->engine->calc(100.0, $rates, true);

        $this->assertEqualsWithDelta(0.0, $taxes[1], 0.001);
    }

    public function test_inclusive_empty_rates_returns_empty_array(): void
    {
        $taxes = $this->engine->calc(100.0, [], true);

        $this->assertSame([], $taxes);
    }

    // --- calc() delegation ---

    public function test_calc_delegates_to_exclusive_when_flag_is_false(): void
    {
        $rates = [['id' => 1, 'rate' => 15.0, 'label' => 'Tax', 'compound' => false]];

        $this->assertSame(
            $this->engine->calcExclusive(200.0, $rates),
            $this->engine->calc(200.0, $rates, false)
        );
    }

    public function test_calc_delegates_to_inclusive_when_flag_is_true(): void
    {
        $rates = [['id' => 1, 'rate' => 10.0, 'label' => 'Tax', 'compound' => false]];

        $this->assertSame(
            $this->engine->calcInclusive(110.0, $rates),
            $this->engine->calc(110.0, $rates, true)
        );
    }

    public function test_large_price_precision(): void
    {
        $rates = [['id' => 1, 'rate' => 18.0, 'label' => 'GST', 'compound' => false]];

        $taxes = $this->engine->calc(99999.99, $rates, false);

        $this->assertEqualsWithDelta(17999.9982, $taxes[1], 0.01);
    }

    // --- Edge cases ---

    public function test_zero_price_returns_zero_tax(): void
    {
        $rates = [['id' => 1, 'rate' => 20.0, 'label' => 'VAT', 'compound' => false]];

        $taxes = $this->engine->calc(0.0, $rates, false);

        $this->assertEqualsWithDelta(0.0, $taxes[1], 0.001);
    }

    public function test_negative_price_returns_negative_tax(): void
    {
        // Edge case: credit notes / refunds may pass negative amounts
        $rates = [['id' => 1, 'rate' => 20.0, 'label' => 'VAT', 'compound' => false]];

        $taxes = $this->engine->calc(-100.0, $rates, false);

        $this->assertEqualsWithDelta(-20.0, $taxes[1], 0.001);
    }

    public function test_rate_over_100_percent_is_handled(): void
    {
        // Some luxury tax jurisdictions exceed 100% — the engine must not break
        $rates = [['id' => 1, 'rate' => 150.0, 'label' => 'Luxury', 'compound' => false]];

        $taxes = $this->engine->calc(100.0, $rates, false);

        $this->assertEqualsWithDelta(150.0, $taxes[1], 0.001);
    }

    public function test_floating_point_precision_with_many_decimal_places(): void
    {
        // 33.333...% of 99.99 should not produce NaN or INF
        $rates = [['id' => 1, 'rate' => 33.333, 'label' => 'Tax', 'compound' => false]];

        $taxes = $this->engine->calc(99.99, $rates, false);

        $this->assertIsFloat($taxes[1]);
        $this->assertFalse(is_nan($taxes[1]));
        $this->assertFalse(is_infinite($taxes[1]));
        $this->assertEqualsWithDelta(33.33, $taxes[1], 0.01);
    }

    public function test_large_number_of_rates_all_calculated(): void
    {
        // 100 non-compound rates — ensures no off-by-one or key collision
        $rates = [];
        for ($i = 1; $i <= 100; $i++) {
            $rates[] = ['id' => $i, 'rate' => 1.0, 'label' => "Rate $i", 'compound' => false];
        }

        $taxes = $this->engine->calc(100.0, $rates, false);

        $this->assertCount(100, $taxes);
        // Each rate: 100 * 1% = 1.0
        foreach ($taxes as $tax) {
            $this->assertEqualsWithDelta(1.0, $tax, 0.001);
        }
    }

    public function test_duplicate_rate_ids_last_one_wins(): void
    {
        // If two rates share the same id, array key collision — last write wins
        $rates = [
            ['id' => 1, 'rate' => 10.0, 'label' => 'First',  'compound' => false],
            ['id' => 1, 'rate' => 20.0, 'label' => 'Second', 'compound' => false],
        ];

        $taxes = $this->engine->calc(100.0, $rates, false);

        // Result array keyed by id — only one entry for id=1 will exist
        $this->assertCount(1, $taxes);
    }

    public function test_inclusive_with_multiple_non_compound_rates_proportional_split(): void
    {
        // Two equal rates on a price-inclusive basis — each should get proportional share
        $rates = [
            ['id' => 1, 'rate' => 10.0, 'label' => 'A', 'compound' => false],
            ['id' => 2, 'rate' => 10.0, 'label' => 'B', 'compound' => false],
        ];
        // Total tax rate = 20%; on price 120 → total tax = 20, split equally: 10 each
        $taxes = $this->engine->calc(120.0, $rates, true);

        $this->assertEqualsWithDelta($taxes[1], $taxes[2], 0.001);
        $this->assertEqualsWithDelta(20.0, array_sum($taxes), 0.01);
    }

    
}
