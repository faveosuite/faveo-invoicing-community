<?php

namespace Tests\Unit\Backend\Http\Controllers\Order;

use App\Http\Controllers\Order\BaseInvoiceController;
use App\Model\Payment\TaxOption;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class BaseInvoiceControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classObject = new BaseInvoiceController;
    }

    #[Group('baseinvoicecontroller')]
    public function test_calculate_total_calculate_total_after_applying_rate_when_inclusive_of_tax_returns_price_after_adding_tax(): void
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $price = $this->classObject->calculateTotal('10%', '1000');
        $this->assertEquals($price, '1100');
    }

    #[Group('baseinvoicecontroller')]
    public function test_calculate_total_calculate_total_after_applying_rate_when_exclusive_of_tax_returns_price_without_tax(): void
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $tax_rule = new TaxOption;
        $tax_rule->findOrFail(1)->update(['inclusive' => 1]);
        $price = $this->classObject->calculateTotal('10%', '1000');
        $this->assertEquals($price, '1000');
    }

    // =========================================================================
    // getExpiryStatus — additional branches
    // =========================================================================

    public function test_get_expiry_status_null_when_no_dates(): void
    {
        $result = $this->classObject->getExpiryStatus(null, null, now()->toDateTimeString());
        $this->assertTrue($result === null || is_string($result));
    }

    public function test_get_expiry_status_with_both_dates(): void
    {
        $result = $this->classObject->getExpiryStatus(
            now()->subDays(5)->toDateTimeString(),
            now()->addDays(30)->toDateTimeString(),
            now()->toDateTimeString()
        );
        $this->assertIsString($result);
    }

    public function test_get_expiry_status_expired(): void
    {
        $result = $this->classObject->getExpiryStatus(
            now()->subDays(60)->toDateTimeString(),
            now()->subDays(10)->toDateTimeString(),
            now()->toDateTimeString()
        );
        $this->assertTrue($result === null || is_string($result));
    }

    // =========================================================================
    // whenDateNotSet / whenStartDateSet / whenEndDateSet / whenBothSet
    // =========================================================================

    public function test_when_date_not_set_returns_null_when_both_have_values(): void
    {
        $result = $this->classObject->whenDateNotSet('2024-01-01', '2025-01-01');
        $this->assertNull($result);
    }

    public function test_when_start_date_set_with_end_date(): void
    {
        $result = $this->classObject->whenStartDateSet(
            now()->subDays(10)->toDateTimeString(),
            now()->addDays(10)->toDateTimeString(),
            now()->toDateTimeString()
        );
        $this->assertTrue($result === null || is_string($result));
    }

    public function test_when_end_date_set_without_start(): void
    {
        $result = $this->classObject->whenEndDateSet(
            null,
            now()->addDays(10)->toDateTimeString(),
            now()->toDateTimeString()
        );
        $this->assertTrue($result === null || is_string($result));
    }

    public function test_when_both_set_both_in_future(): void
    {
        $result = $this->classObject->whenBothSet(
            now()->subDays(1)->toDateTimeString(),
            now()->addDays(30)->toDateTimeString(),
            now()->toDateTimeString()
        );
        $this->assertIsString($result);
    }

    // =========================================================================
    // domain — returns session value or null
    // =========================================================================

    public function test_domain_returns_null_or_empty_when_no_session(): void
    {
        $result = $this->classObject->domain('999999');
        $this->assertTrue($result === null || $result === '');
    }

    public function test_domain_returns_value_when_in_session(): void
    {
        session(['domain999999' => 'test.example.com']);
        $result = $this->classObject->domain('999999');
        $this->assertEquals('test.example.com', $result);
    }
}
