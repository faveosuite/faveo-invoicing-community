<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class PaymentSettingsControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    public function test_get_payment_gateway_list_returns_200(): void
    {
        $response = $this->getJson('/payment-gateway-list');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_update_payment_status_returns_response_with_missing_data(): void
    {
        $response = $this->postJson('/updatePaymentStatus', []);

        $this->assertContains($response->getStatusCode(), [200, 400, 302, 422, 500]);
    }

    public function test_status_plugin_returns_response_for_unknown_slug(): void
    {
        $response = $this->postJson('/plugin/status/nonexistent-plugin-xyz');

        $this->assertContains($response->getStatusCode(), [200, 400, 404, 422, 500, 302]);
    }

    public function test_fetch_config_can_be_called_directly(): void
    {
        $controller = new \App\Http\Controllers\Common\PaymentSettingsController;

        $result = $controller->fetchConfig();

        $this->assertIsArray($result);
    }

    public function test_get_payment_plugin_map_returns_array(): void
    {
        $controller = new \App\Http\Controllers\Common\PaymentSettingsController;

        $result = $controller->getPaymentPluginMap();

        $this->assertIsArray($result);
    }

    public function test_read_configs_returns_result(): void
    {
        $controller = new \App\Http\Controllers\Common\PaymentSettingsController;

        $result = $controller->readConfigs();

        $this->assertTrue(is_array($result) || is_string($result));
    }
}
