<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Order;

use App\Http\Controllers\Order\TaxRatesAndCodeExpiryController;
use Tests\TestCase;

class TaxRatesAndCodeExpiryControllerTest extends TestCase
{
    private TaxRatesAndCodeExpiryController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->controller = new TaxRatesAndCodeExpiryController();
    }

    // =========================================================================
    // getGrandTotal() – pure logic branches
    // =========================================================================

    public function test_get_grand_total_returns_zero_total_when_no_total(): void
    {
        $result = $this->controller->getGrandTotal(null, 0, 0, 1, 'USD');
        $this->assertSame(['total' => 0, 'code' => '', 'value' => '', 'mode' => ''], $result);
    }

    public function test_get_grand_total_returns_total_when_no_code(): void
    {
        $result = $this->controller->getGrandTotal(null, 100.0, 100.0, 1, 'USD');
        $this->assertSame(['total' => 100.0, 'code' => '', 'value' => '', 'mode' => ''], $result);
    }

    public function test_get_grand_total_throws_when_invalid_code(): void
    {
        $this->expectException(\Exception::class);
        $this->controller->getGrandTotal('INVALIDCODE', 100.0, 100.0, 1, 'USD');
    }

    // =========================================================================
    // getMessage() – pure logic
    // =========================================================================

    public function test_get_message_returns_success_when_items_truthy(): void
    {
        $items = new \stdClass();
        $items->invoice_id = 1;
        $result = $this->controller->getMessage($items, 1);
        $this->assertArrayHasKey('success', $result);
    }

    public function test_get_message_returns_fails_when_items_falsy(): void
    {
        $result = $this->controller->getMessage(null, 1);
        $this->assertArrayHasKey('fails', $result);
    }

    // =========================================================================
    // invoiceUrl() – pure URL generation
    // =========================================================================

    public function test_invoice_url_returns_url_with_invoice_id(): void
    {
        $result = $this->controller->invoiceUrl(42);
        $this->assertStringContainsString('42', (string) $result);
        $this->assertStringContainsString('my-invoice', (string) $result);
    }

    // =========================================================================
    // currency() – returns space for invoice not found
    // =========================================================================

    public function test_currency_returns_space_for_nonexistent_invoice(): void
    {
        $result = $this->controller->currency(999999999);
        $this->assertIsString($result);
    }
}
