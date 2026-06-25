<?php

namespace Tests\Unit\Backend\Http\Controllers\Order;

use App\Http\Controllers\Order\ExtendedOrderController;
use App\Model\Order\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ExtendedOrderControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    private ExtendedOrderController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
        $this->controller = new ExtendedOrderController;
    }

    // -------------------------------------------------------------------------
    // generateSerialKey — pure string generation with agent count
    // -------------------------------------------------------------------------

    public function test_generate_serial_key_with_1_agent(): void
    {
        $key = $this->controller->generateSerialKey(1, 5);
        $this->assertIsString($key);
        $this->assertEquals(16, strlen($key));
        $this->assertStringEndsWith('0005', $key);
    }

    public function test_generate_serial_key_with_2_digit_agents(): void
    {
        $key = $this->controller->generateSerialKey(1, 50);
        $this->assertStringEndsWith('0050', $key);
    }

    public function test_generate_serial_key_with_3_digit_agents(): void
    {
        $key = $this->controller->generateSerialKey(1, 500);
        $this->assertStringEndsWith('0500', $key);
    }

    public function test_generate_serial_key_with_4_digit_agents(): void
    {
        $key = $this->controller->generateSerialKey(1, 5000);
        $this->assertStringEndsWith('5000', $key);
    }

    public function test_generate_serial_key_with_large_agents_uses_default(): void
    {
        $key = $this->controller->generateSerialKey(1, 50000);
        $this->assertStringEndsWith('0000', $key); // default branch
    }

    // -------------------------------------------------------------------------
    // generateNumber — returns 8-digit random int
    // -------------------------------------------------------------------------

    public function test_generate_number_returns_8_digit_int(): void
    {
        $number = $this->controller->generateNumber();
        $this->assertIsInt($number);
        $this->assertGreaterThanOrEqual(10000000, $number);
        $this->assertLessThanOrEqual(99999999, $number);
    }

    // -------------------------------------------------------------------------
    // reissueLicense — missing id → 422
    // -------------------------------------------------------------------------

    public function test_reissue_license_returns_422_when_id_missing(): void
    {
        $response = $this->patchJson('/reissue-license', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id']);
    }

    public function test_reissue_license_returns_400_for_nonexistent_order(): void
    {
        $response = $this->patchJson('/reissue-license', ['id' => 999999]);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_reissue_license_returns_200_for_own_order(): void
    {
        // Must have serial_key for reissueLicense to call LicenseService::findByCode(string)
        $order = Order::create([
            'client'       => $this->user->id,
            'order_status' => 'executed',
            'number'       => mt_rand(10000000, 99999999),
            'serial_key'   => 'REISSUE'.mt_rand(100000, 999999),
        ]);

        $response = $this->patchJson('/reissue-license', ['id' => $order->id]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
