<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers;

use App\Http\Controllers\BaseHomeController;
use Tests\TestCase;

class BaseHomeControllerTest extends TestCase
{
    private BaseHomeController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new BaseHomeController();
    }

    // =========================================================================
    // getDomain() – pure string parsing
    // =========================================================================

    public function test_get_domain_with_full_url(): void
    {
        $result = $this->controller->getDomain('https://www.example.com/path');
        $this->assertSame('example.com', $result);
    }

    public function test_get_domain_with_plain_domain(): void
    {
        $result = $this->controller->getDomain('http://example.com');
        $this->assertSame('example.com', $result);
    }

    public function test_get_domain_with_empty_string(): void
    {
        $result = $this->controller->getDomain('');
        $this->assertSame('', $result);
    }

    // =========================================================================
    // getUserIP() – reads from request server vars
    // =========================================================================

    public function test_get_user_ip_returns_string_or_null(): void
    {
        $result = $this->controller->getUserIP();
        $this->assertTrue(is_string($result) || is_null($result));
    }

    // =========================================================================
    // getTotalSales() – DB query, returns numeric
    // =========================================================================

    public function test_get_total_sales_returns_numeric(): void
    {
        $result = $this->controller->getTotalSales();
        $this->assertIsNumeric($result);
    }

    // =========================================================================
    // verificationResult() – returns error array when order/key missing
    // =========================================================================

    public function test_verification_result_returns_error_for_empty_inputs(): void
    {
        $result = $this->controller->verificationResult('', '');
        $this->assertIsArray($result);
        // Empty order_number and serial_key → returns ['error' => ...] or similar
        $this->assertNotEmpty($result);
    }
}
