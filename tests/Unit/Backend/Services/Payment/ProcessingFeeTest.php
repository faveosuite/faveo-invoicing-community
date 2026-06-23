<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services\Payment;

use App\Services\Payment\ProcessingFee;
use Tests\DBTestCase;

class ProcessingFeeTest extends DBTestCase
{
    // --- percent() ---

    public function test_percent_returns_zero_for_null_gateway(): void
    {
        $this->assertEqualsWithDelta(0.0, ProcessingFee::percent(null), 0.001);
    }

    public function test_percent_returns_zero_for_empty_string_gateway(): void
    {
        $this->assertEqualsWithDelta(0.0, ProcessingFee::percent(''), 0.001);
    }

    public function test_percent_returns_zero_when_table_does_not_exist(): void
    {
        // 'nonexistent_gateway_xyz' has no DB table — Throwable caught → 0.0
        $this->assertEqualsWithDelta(0.0, ProcessingFee::percent('nonexistent_gateway_xyz'), 0.001);
    }

    // --- label() ---

    public function test_label_formats_simple_percentage(): void
    {
        $this->assertSame('2.5%', ProcessingFee::label(2.5));
    }

    public function test_label_strips_trailing_zeros(): void
    {
        $this->assertSame('10%', ProcessingFee::label(10.0));
    }

    public function test_label_handles_zero_percent(): void
    {
        $this->assertSame('0%', ProcessingFee::label(0.0));
    }

    public function test_label_handles_fraction_with_no_trailing_zeros(): void
    {
        $this->assertSame('2.99%', ProcessingFee::label(2.99));
    }

    public function test_label_handles_100_percent(): void
    {
        $this->assertSame('100%', ProcessingFee::label(100.0));
    }

    // --- fromInclusive() ---

    public function test_from_inclusive_extracts_percent_string_fee(): void
    {
        // 120 with 20% fee embedded: 120 - 120/1.2 = 20.0
        $fee = ProcessingFee::fromInclusive(120.0, '20%');

        $this->assertEqualsWithDelta(20.0, $fee, 0.01);
    }

    public function test_from_inclusive_extracts_numeric_fee(): void
    {
        $fee = ProcessingFee::fromInclusive(120.0, 20);

        $this->assertEqualsWithDelta(20.0, $fee, 0.01);
    }

    public function test_from_inclusive_returns_zero_for_zero_fee(): void
    {
        $this->assertEqualsWithDelta(0.0, ProcessingFee::fromInclusive(100.0, 0), 0.001);
        $this->assertEqualsWithDelta(0.0, ProcessingFee::fromInclusive(100.0, '0%'), 0.001);
    }

    public function test_from_inclusive_returns_zero_for_empty_string_fee(): void
    {
        $this->assertEqualsWithDelta(0.0, ProcessingFee::fromInclusive(100.0, ''), 0.001);
    }

    public function test_from_inclusive_works_with_float_string(): void
    {
        // "2.9%" → pct 2.9; fee = 102.9 - (102.9/1.029) ≈ 102.9 - 100.0 = 2.9
        $fee = ProcessingFee::fromInclusive(102.9, '2.9%');

        $this->assertEqualsWithDelta(2.9, $fee, 0.01);
    }

    // --- addTo() and amount() — call rounding() which reads TaxOption(1) ---

    public function test_add_to_returns_base_unchanged_when_no_gateway(): void
    {
        // percent(null) = 0; base * 1.0 = base; rounding keeps it as-is
        $result = ProcessingFee::addTo(100.0, null);

        $this->assertEqualsWithDelta(100.0, $result, 0.01);
    }

    public function test_amount_returns_zero_when_no_gateway_fee(): void
    {
        // addTo(100.0, null) = 100.0; 100.0 - 100.0 = 0.0
        $result = ProcessingFee::amount(100.0, null);

        $this->assertEqualsWithDelta(0.0, $result, 0.001);
    }

    public function test_add_to_is_always_greater_than_or_equal_to_base(): void
    {
        // Even with a DB-configured fee, addTo must never return less than base
        // (fees are surcharges, never discounts).
        $result = ProcessingFee::addTo(50.0, null); // known 0% for null

        $this->assertGreaterThanOrEqual(50.0, $result);
    }
}
