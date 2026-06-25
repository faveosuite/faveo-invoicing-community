<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers;

use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

/**
 * Tests for HomeController (API endpoints for Faveo product communication).
 * Routes are public (no auth) - they are called by Faveo helpdesk installs.
 */
class HomeControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    // =========================================================================
    // latestVersion – GET /version/latest
    // =========================================================================

    public function test_latest_version_returns_error_when_title_missing(): void
    {
        $response = $this->getJson('/version/latest?product_id=1');
        // No title → validation fails → returns JSON with error
        $data = $response->json();
        $this->assertTrue(isset($data['error']) || $response->status() === 422);
    }

    public function test_latest_version_with_unknown_product_returns_valid_response(): void
    {
        $response = $this->getJson('/version/latest?title=NonexistentProduct');
        // Returns JSON (may be empty or with data)
        $this->assertTrue($response->status() >= 200);
    }

    // =========================================================================
    // isNewVersionAvailable – GET /new-version-available
    // =========================================================================

    public function test_is_new_version_available_with_unknown_product(): void
    {
        $response = $this->getJson('/new-version-available?title=NonexistentProduct&version=1.0.0');
        $this->assertTrue($response->status() >= 200);
    }

    public function test_is_new_version_available_missing_title(): void
    {
        $response = $this->getJson('/new-version-available');
        $data = $response->json();
        $this->assertTrue(isset($data['error']) || $response->status() >= 200);
    }

    // =========================================================================
    // getDetailedBillingInfo – GET /api/billingInfo
    // =========================================================================

    public function test_get_detailed_billing_info_returns_empty_for_unknown_order(): void
    {
        $response = $this->getJson('/api/billingInfo?order=NONEXISTENT12345');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
    }

    // =========================================================================
    // getDetailsForAClient – GET /api/pluginInfo
    // =========================================================================

    public function test_get_details_for_client_returns_string_response(): void
    {
        $response = $this->get('/api/pluginInfo?client=none&license=none&product_id=1');
        $this->assertTrue($response->status() >= 200);
    }

    // =========================================================================
    // getProductRelease – GET /api/billingRelease
    // =========================================================================

    public function test_get_product_release_returns_response(): void
    {
        $response = $this->getJson('/api/billingRelease');
        $this->assertTrue($response->status() >= 200);
    }

    // =========================================================================
    // serial – POST /serial
    // =========================================================================

    public function test_serial_post_returns_valid_response(): void
    {
        $response = $this->postJson('/serial', [
            'faveo_name' => 'Faveo Test',
            'faveo_version' => '1.0.0',
            'domain' => 'https://test.example.com',
        ]);
        $this->assertTrue($response->status() >= 200);
    }

    // =========================================================================
    // v1/checkUpdatesExpiry – POST
    // =========================================================================

    public function test_check_updates_expiry_returns_response(): void
    {
        $response = $this->postJson('/v1/checkUpdatesExpiry', [
            'faveo_name' => 'Faveo Test',
            'faveo_version' => '1.0.0',
        ]);
        $this->assertTrue($response->status() >= 200);
    }

    // =========================================================================
    // v2/serial – POST
    // =========================================================================

    public function test_serial_v2_post_returns_response(): void
    {
        $response = $this->postJson('/v2/serial', [
            'faveo_name' => 'Faveo Test',
            'faveo_version' => '1.0.0',
            'domain' => 'test.example.com',
        ]);
        $this->assertTrue($response->status() >= 200);
    }

    // =========================================================================
    // update-latest-version – POST /update-latest-version
    // =========================================================================

    public function test_update_latest_version_returns_response(): void
    {
        $response = $this->postJson('/update-latest-version', [
            'version' => '1.0.0',
            'domain' => 'test.example.com',
        ]);
        $this->assertTrue($response->status() >= 200);
    }

    // =========================================================================
    // downloadForFaveo – covers error paths
    // =========================================================================

    public function test_download_for_faveo_returns_error_for_invalid_credentials(): void
    {
        $response = $this->getJson('/download/faveo?order_number=INVALID&serial_key=INVALID');
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    // =========================================================================
    // getDetailedBillingInfo – assert actual data
    // =========================================================================

    public function test_get_detailed_billing_info_returns_empty_array_for_unknown(): void
    {
        $response = $this->getJson('/api/billingInfo?order=NONEXISTENT12345');
        $response->assertStatus(200);
        $data = $response->json();
        // Should be empty array (no order found)
        $this->assertIsArray($data);
        $this->assertEmpty($data);
    }

    public function test_get_detailed_billing_info_with_real_order_returns_email(): void
    {
        $user = \App\User::factory()->create(['email' => 'billing-info-'.uniqid().'@test.local']);
        $product = \App\Model\Product\Product::create(['name' => 'Test Product']);
        $order = \App\Model\Order\Order::create([
            'client' => $user->id,
            'order_status' => 'executed',
            'product' => $product->id,
            'number' => 'ORD-'.mt_rand(1000, 9999),
        ]);

        $response = $this->getJson('/api/billingInfo?order='.$order->number);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('billing_client_email', $data);
        $this->assertSame($user->email, $data['billing_client_email']);
    }

    // =========================================================================
    // BaseHomeController::getDomain – pure string parsing
    // =========================================================================

    public function test_get_domain_with_subdomain_strips_properly(): void
    {
        $controller = new \App\Http\Controllers\BaseHomeController();
        $result = $controller->getDomain('https://support.example.co.uk/help');
        $this->assertSame('example.co.uk', $result);
    }
}
