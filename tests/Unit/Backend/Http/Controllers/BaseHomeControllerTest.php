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

    public function test_verification_result_returns_fails_for_invalid_order(): void
    {
        // Order '999999' doesn't exist → verifyOrder returns null → fails
        $result = $this->controller->verificationResult('999999', 'INVALID-SERIAL-KEY');
        $this->assertIsArray($result);
        $this->assertEquals('fails', $result['status']);
    }

    // =========================================================================
    // getLastFourDigistsOfLicenseCode() – pure match logic
    // =========================================================================

    public function test_get_last_four_digits_enterprise(): void
    {
        $result = $this->controller->getLastFourDigistsOfLicenseCode('Faveo Enterprise');
        $this->assertSame('0000', $result);
    }

    public function test_get_last_four_digits_company(): void
    {
        $result = $this->controller->getLastFourDigistsOfLicenseCode('Faveo Company');
        $this->assertSame('0000', $result);
    }

    public function test_get_last_four_digits_freelancer(): void
    {
        $result = $this->controller->getLastFourDigistsOfLicenseCode('Faveo Freelancer');
        $this->assertSame('0002', $result);
    }

    public function test_get_last_four_digits_startup(): void
    {
        $result = $this->controller->getLastFourDigistsOfLicenseCode('Faveo Startup');
        $this->assertSame('0005', $result);
    }

    public function test_get_last_four_digits_sme(): void
    {
        $result = $this->controller->getLastFourDigistsOfLicenseCode('Faveo SME');
        $this->assertSame('0010', $result);
    }

    public function test_get_last_four_digits_unknown_throws(): void
    {
        $this->expectException(\Exception::class);
        $this->controller->getLastFourDigistsOfLicenseCode('Unknown Product');
    }

    // =========================================================================
    // checkDomain() — null when domain not found
    // =========================================================================

    public function test_check_domain_returns_null_for_unknown_domain(): void
    {
        $result = $this->controller->checkDomain('https://nonexistent-domain-xyzzy.test/');
        $this->assertNull($result);
    }

    // =========================================================================
    // checkSerialKey() — null when order not found
    // =========================================================================

    public function test_check_serial_key_returns_null_for_invalid_order(): void
    {
        $result = $this->controller->checkSerialKey('FAKE-KEY', 'INVALID-ORDER-999');
        $this->assertNull($result);
    }

    // =========================================================================
    // verifyOrder() — null when not found
    // =========================================================================

    public function test_verify_order_returns_null_for_unknown_order(): void
    {
        $result = $this->controller->verifyOrder('INVALID-ORDER-999', 'INVALID-SERIAL');
        $this->assertNull($result);
    }

    // =========================================================================
    // updateLatestVersion() — fails when no license code matches
    // =========================================================================

    public function test_update_latest_version_returns_fails_for_unknown_license(): void
    {
        $request = new \Illuminate\Http\Request;
        $request->merge(['licenseCode' => 'INVALID_LICENSE_CODE_XYZ', 'version' => '1.0.0']);

        $result = $this->controller->updateLatestVersion($request);

        $this->assertIsArray($result);
        $this->assertEquals('fails', $result['status']);
    }

    // =========================================================================
    // getUpdatesExpiryDate / getLicenseExpiryDate / getSupportExpiryDate
    // =========================================================================

    public function test_get_updates_expiry_date_returns_empty_when_no_subscription(): void
    {
        $order = \App\Model\Order\Order::first() ?? \App\Model\Order\Order::create(['client' => 1, 'order_status' => 'executed', 'number' => mt_rand(10000000, 99999999)]);

        $result = $this->controller->getUpdatesExpiryDate($order);
        $this->assertTrue($result === '' || $result instanceof \Carbon\Carbon);
    }

    public function test_get_license_expiry_date_returns_empty_when_no_subscription(): void
    {
        $order = \App\Model\Order\Order::first() ?? \App\Model\Order\Order::create(['client' => 1, 'order_status' => 'executed', 'number' => mt_rand(10000000, 99999999)]);

        $result = $this->controller->getLicenseExpiryDate($order);
        $this->assertTrue($result === '' || $result instanceof \Carbon\Carbon);
    }

    public function test_get_support_expiry_date_returns_empty_when_no_subscription(): void
    {
        $order = \App\Model\Order\Order::first() ?? \App\Model\Order\Order::create(['client' => 1, 'order_status' => 'executed', 'number' => mt_rand(10000000, 99999999)]);

        $result = $this->controller->getSupportExpiryDate($order);
        $this->assertTrue($result === '' || $result instanceof \Carbon\Carbon);
    }

    // =========================================================================
    // checkSerialKey — with matching order returns serial key
    // =========================================================================

    public function test_check_serial_key_returns_key_when_matches(): void
    {
        $order = \App\Model\Order\Order::whereNotNull('serial_key')->whereNotNull('number')->first();
        if (! $order) {
            // Create an order with a known serial_key
            $order = \App\Model\Order\Order::create([
                'client' => 1, 'order_status' => 'executed',
                'number' => mt_rand(10000000, 99999999),
                'serial_key' => 'TESTSERIAL1234',
            ]);
        }

        $result = $this->controller->checkSerialKey($order->serial_key, (string) $order->number);
        $this->assertEquals($order->serial_key, $result);
    }

    public function test_check_serial_key_returns_null_when_key_mismatch(): void
    {
        $order = \App\Model\Order\Order::whereNotNull('number')->first() ?? \App\Model\Order\Order::create(['client' => 1, 'order_status' => 'executed', 'number' => mt_rand(10000000, 99999999)]);

        $result = $this->controller->checkSerialKey('WRONG_KEY_XYZ', (string) $order->number);
        $this->assertNull($result);
    }

    // =========================================================================
    // verifyOrder — with valid order number returns Order
    // =========================================================================

    public function test_verify_order_returns_order_when_found(): void
    {
        $order = \App\Model\Order\Order::first() ?? \App\Model\Order\Order::create(['client' => 1, 'order_status' => 'executed', 'number' => mt_rand(10000000, 99999999)]);

        $result = $this->controller->verifyOrder((string) $order->number, 'any_serial');
        $this->assertInstanceOf(\App\Model\Order\Order::class, $result);
        $this->assertEquals($order->id, $result->id);
    }

    // =========================================================================
    // verificationResult — with valid order+serial returns success
    // =========================================================================

    public function test_verification_result_returns_success_when_order_found(): void
    {
        $order = \App\Model\Order\Order::first() ?? \App\Model\Order\Order::create(['client' => 1, 'order_status' => 'executed', 'number' => mt_rand(10000000, 99999999)]);

        $result = $this->controller->verificationResult((string) $order->number, 'any_serial');

        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('this-is-a-valid-request', $result['message']);
    }
}
