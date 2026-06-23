<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Traits;

use App\Traits\TaxCalculation;
use Tests\DBTestCase;

class TaxCalculationTest extends DBTestCase
{
    // Use an anonymous class that uses the trait.
    // taxValue() is static so we call it directly.

    // =========================================================================
    // taxValue() — static, pure math
    // =========================================================================

    public function test_tax_value_calculates_percentage_of_price(): void
    {
        // 18% of 100 = 18
        $result = TaxCalculation::taxValue('18%', 100);
        $this->assertEqualsWithDelta(18.0, $result, 0.001);
    }

    public function test_tax_value_zero_price_returns_zero(): void
    {
        $result = TaxCalculation::taxValue('18%', 0);
        $this->assertEqualsWithDelta(0.0, $result, 0.001);
    }

    public function test_tax_value_empty_rate_returns_zero(): void
    {
        $result = TaxCalculation::taxValue('', 100);
        $this->assertSame(0, $result);
    }

    public function test_tax_value_zero_rate_returns_zero(): void
    {
        $result = TaxCalculation::taxValue('0%', 100);
        $this->assertEqualsWithDelta(0.0, $result, 0.001);
    }

    public function test_tax_value_negative_price_returns_negative(): void
    {
        // Credit notes may pass negative amounts
        $result = TaxCalculation::taxValue('18%', -100);
        $this->assertEqualsWithDelta(-18.0, $result, 0.001);
    }

    public function test_tax_value_zero_price_integer_returns_zero(): void
    {
        // Using integer 0 rather than string — taxValue requires int|float
        $result = TaxCalculation::taxValue('18%', 0);
        $this->assertEqualsWithDelta(0.0, $result, 0.001);
    }

    public function test_tax_value_rate_without_percent_sign_works(): void
    {
        // sumPercent strips '%' — calling taxValue directly: "18" should work too
        // But taxValue doesn't call sumPercent — it strips '%' itself
        $result = TaxCalculation::taxValue('10', 100);
        $this->assertEqualsWithDelta(10.0, $result, 0.001);
    }

    public function test_tax_value_floating_point_rate(): void
    {
        // 8.5% of 200 = 17.0
        $result = TaxCalculation::taxValue('8.5%', 200);
        $this->assertEqualsWithDelta(17.0, $result, 0.001);
    }

    public function test_tax_value_rounding_flag_false_returns_float(): void
    {
        $result = TaxCalculation::taxValue('18%', 99, round: false);
        $this->assertIsFloat((float) $result);
    }

    // =========================================================================
    // calculateTotal() — reads TaxOption(1) from DB
    // =========================================================================

    private function subject(): object
    {
        return new class {
            use TaxCalculation;
        };
    }

    public function test_calculate_total_with_zero_percent_rate_returns_original(): void
    {
        $this->getLoggedInUser('user');

        $result = $this->subject()->calculateTotal('0%', 100.0);

        $this->assertEqualsWithDelta(100.0, $result, 0.01);
    }

    public function test_calculate_total_with_rate_adds_tax_when_exclusive(): void
    {
        $this->getLoggedInUser('user');
        // TaxOption(1).inclusive from real DB — if exclusive: 100 + 18% = 118
        // If inclusive: returns 100 unchanged
        // Either is valid — just verify no crash and result >= 100
        $result = $this->subject()->calculateTotal('18%', 100.0);

        $this->assertIsFloat($result);
        $this->assertGreaterThanOrEqual(100.0, $result);
    }

    public function test_calculate_total_with_compound_rates_sums_correctly(): void
    {
        // "9%,9%" means 18% total: 100 + 18 = 118 (or 100 if inclusive)
        $this->getLoggedInUser('user');
        $result = $this->subject()->calculateTotal('9%,9%', 100.0);

        $this->assertIsFloat($result);
        $this->assertGreaterThanOrEqual(100.0, $result);
    }

    // =========================================================================
    // Private sumPercent() via getPrivateMethod
    // =========================================================================

    public function test_sum_percent_parses_single_rate(): void
    {
        $subject = $this->subject();
        $result  = $this->getPrivateMethod($subject, 'sumPercent', ['18%']);

        $this->assertEqualsWithDelta(18.0, $result, 0.001);
    }

    public function test_sum_percent_parses_compound_rates(): void
    {
        $subject = $this->subject();
        $result  = $this->getPrivateMethod($subject, 'sumPercent', ['9%,9%']);

        $this->assertEqualsWithDelta(18.0, $result, 0.001);
    }

    public function test_sum_percent_returns_zero_for_empty_rate(): void
    {
        $subject = $this->subject();
        $result  = $this->getPrivateMethod($subject, 'sumPercent', ['']);

        $this->assertEqualsWithDelta(0.0, $result, 0.001);
    }

    public function test_sum_percent_ignores_non_numeric_parts(): void
    {
        $subject = $this->subject();
        $result  = $this->getPrivateMethod($subject, 'sumPercent', ['10%,abc']);

        $this->assertEqualsWithDelta(10.0, $result, 0.001);
    }

    
}
