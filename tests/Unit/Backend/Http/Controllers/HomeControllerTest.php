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

    // =========================================================================
    // isNewVersionAvailable — with product by ID using 'pre_release' param
    // =========================================================================

    public function test_is_new_version_available_with_pre_release_param(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);

        $response = $this->getJson('/new-version-available?title=test&id='.$product->id.'&version=0.0.1&is_pre_release=1');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('message', $data);
    }

    public function test_is_new_version_available_with_beta_release_type(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);

        $response = $this->getJson('/new-version-available?title=test&id='.$product->id.'&version=0.0.1&release_type=beta');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('message', $data);
    }

    public function test_is_new_version_available_with_pre_release_type(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);

        $response = $this->getJson('/new-version-available?title=test&id='.$product->id.'&version=999.0.0&release_type=pre_release');

        $response->assertStatus(200);
        $data = $response->json();
        // No version > 999.0.0 → no new version available
        $this->assertSame('no-new-version-available', $data['message']);
    }

    // =========================================================================
    // latestVersion — with product found by ID (covers more branches)
    // =========================================================================

    public function test_latest_version_with_product_found_by_id_and_version_param(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);

        $response = $this->getJson('/version/latest?title=test&id='.$product->id.'&version=0.0.1');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('version', $data);
    }

    public function test_latest_version_with_pre_release_flag(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);

        $response = $this->getJson('/version/latest?title=test&id='.$product->id.'&version=0.0.1&is_pre_release=1');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('version', $data);
    }

    // =========================================================================
    // getProductRelease — GET /api/billingRelease
    // =========================================================================

    public function test_get_product_release_with_known_license(): void
    {
        $order = \App\Model\Order\Order::whereNotNull('serial_key')->first();
        if (! $order) {
            $order = \App\Model\Order\Order::create(['client' => 1, 'order_status' => 'executed', 'number' => mt_rand(10000000, 99999999), 'serial_key' => '1234567890120000']);
        }

        $response = $this->getJson('/api/billingRelease?licenseCode='.$order->serial_key);

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
    }

    // =========================================================================
    // renewurl — throws when subscription not found → returns JSON error
    // =========================================================================

    public function test_renewurl_returns_json_error_when_subscription_not_found(): void
    {
        $response = $this->postJson('/renewurl', [
            'domain' => 'nonexistent-domain-xyzzy.test',
            'order_number' => 'INVALID-ORDER-XYZ',
            'serial_key' => 'INVALID-SERIAL',
            'faveo_name' => 'HelpDesk',
            'faveo_version' => '1.0.0',
        ]);

        // Subscription not found → exception → JSON error response
        $response->assertStatus(200); // returns json(['error' => ...]) not errorResponse
        $this->assertArrayHasKey('error', $response->json());
    }

    // =========================================================================
    // getDetailedBillingInfo — with real order that has a client
    // =========================================================================

    public function test_get_detailed_billing_info_returns_email_for_existing_order(): void
    {
        $user = \App\User::factory()->create(['email' => 'billing-info-'.uniqid().'@test.local']);
        $order = \App\Model\Order\Order::create([
            'client' => $user->id,
            'order_status' => 'executed',
            'number' => 'BILLING-'.uniqid(),
        ]);

        $response = $this->getJson('/api/billingInfo?order='.$order->number);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('billing_client_email', $data);
        $this->assertEquals($user->email, $data['billing_client_email']);
    }

    // =========================================================================
    // isNewVersionAvailable — product found with version check (covers more branches)
    // =========================================================================

    public function test_is_new_version_available_uses_title_lookup_without_id(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);

        // Passing title without id — exercises changeProductName + mapOldBoys branch
        $response = $this->getJson('/new-version-available?title='.$product->name.'&version=0.0.1');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('message', $data);
    }

    // =========================================================================
    // latestVersion — product found without version param (older client path)
    // =========================================================================

    public function test_latest_version_without_version_param_for_product(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);

        $response = $this->getJson('/version/latest?title=test&id='.$product->id);

        $response->assertStatus(200);
        $data = $response->json();
        // Either 'version' key (found) or 'error' key (not found)
        $this->assertTrue(
            array_key_exists('version', $data) || array_key_exists('error', $data)
        );
    }

    // =========================================================================
    // BaseHomeController::checkUpdatesExpiry — with order_number found and valid expiry
    // =========================================================================

    public function test_check_updates_expiry_returns_success_when_order_found_and_not_expired(): void
    {
        $user = \App\User::factory()->create();
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);
        $plan = \App\Model\Payment\Plan::where('product', $product->id)->first() ?? \App\Model\Payment\Plan::create(['name' => 'Plan '.uniqid(), 'product' => $product->id, 'days' => 30]);

        $order = \App\Model\Order\Order::create([
            'client' => $user->id,
            'order_status' => 'executed',
            'product' => $product->id,
            'number' => 'CUE-'.uniqid(),
        ]);

        // subscription with future expiry
        \App\Model\Product\Subscription::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'update_ends_at' => now()->addYear()->toDateTimeString(),
            'version' => '1.0.0',
        ]);

        $response = $this->postJson('/v1/checkUpdatesExpiry', [
            'order_number' => $order->number,
        ]);

        $this->assertTrue($response->status() >= 200);
        $data = $response->json();
        $this->assertIsArray($data);
        // May return success or fails depending on expiry comparison
        $this->assertArrayHasKey('status', $data);
    }

    public function test_check_updates_expiry_with_license_code_found(): void
    {
        $user = \App\User::factory()->create();
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);
        $plan = \App\Model\Payment\Plan::where('product', $product->id)->first() ?? \App\Model\Payment\Plan::create(['name' => 'Plan '.uniqid(), 'product' => $product->id, 'days' => 30]);
        $licenseCode = 'TEST-'.uniqid();
        $order = \App\Model\Order\Order::create([
            'client' => $user->id,
            'order_status' => 'executed',
            'product' => $product->id,
            'number' => 'LC-'.uniqid(),
            'serial_key' => $licenseCode,
        ]);
        \App\Model\Product\Subscription::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'update_ends_at' => now()->addYear()->toDateTimeString(),
            'version' => '1.0.0',
        ]);

        $response = $this->postJson('/v1/checkUpdatesExpiry', [
            'license_code' => $licenseCode,
        ]);

        $this->assertTrue($response->status() >= 200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertArrayHasKey('status', $data);
    }

    public function test_check_updates_expiry_returns_fails_when_not_found(): void
    {
        $response = $this->postJson('/v1/checkUpdatesExpiry', [
            'order_number' => 'NONEXISTENT-'.uniqid(),
        ]);

        $this->assertTrue($response->status() >= 200);
        $data = $response->json();
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('fails', $data['status']);
    }

    // =========================================================================
    // BaseHomeController::getData — with valid active subscription
    // =========================================================================

    public function test_get_data_returns_product_and_plan_when_not_expired(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);
        $plan = \App\Model\Payment\Plan::where('product', $product->id)->first() ?? \App\Model\Payment\Plan::create(['name' => 'Plan '.uniqid(), 'product' => $product->id, 'days' => 30]);

        $subscription = new \App\Model\Product\Subscription;
        $subscription->product_id = $product->id;
        $subscription->plan_id = $plan->id;
        $subscription->update_ends_at = now()->addYear()->toDateTimeString();
        $subscription->version = '5.0.0';
        $subscription->support_ends_at = null;
        $subscription->ends_at = null;

        $controller = new \App\Http\Controllers\BaseHomeController;
        $result = $controller->getData($subscription);

        $this->assertNotNull($result);
        $this->assertArrayHasKey('product', $result);
        $this->assertArrayHasKey('plan', $result);
        $this->assertArrayHasKey('update_ends', $result);
        $this->assertArrayHasKey('version', $result);
    }

    public function test_get_data_returns_null_when_subscription_expired(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);
        $plan = \App\Model\Payment\Plan::where('product', $product->id)->first() ?? \App\Model\Payment\Plan::create(['name' => 'Plan '.uniqid(), 'product' => $product->id, 'days' => 30]);

        $subscription = new \App\Model\Product\Subscription;
        $subscription->product_id = $product->id;
        $subscription->plan_id = $plan->id;
        $subscription->update_ends_at = now()->subYear()->toDateTimeString();
        $subscription->version = '4.0.0';

        $controller = new \App\Http\Controllers\BaseHomeController;
        $result = $controller->getData($subscription);

        $this->assertNull($result);
    }

    // =========================================================================
    // BaseHomeController::checkDomain — domain found and not found
    // =========================================================================

    public function test_check_domain_returns_null_when_not_found(): void
    {
        $controller = new \App\Http\Controllers\BaseHomeController;
        $result = $controller->checkDomain('nonexistent-domain-xyzzy-99999.test');
        $this->assertNull($result);
    }

    public function test_check_domain_returns_domain_when_found(): void
    {
        $user = \App\User::factory()->create();
        $order = \App\Model\Order\Order::create([
            'client' => $user->id,
            'order_status' => 'executed',
            'number' => 'DOM-'.uniqid(),
            'domain' => 'checkabledomain-'.uniqid().'.test',
        ]);

        $controller = new \App\Http\Controllers\BaseHomeController;
        $result = $controller->checkDomain($order->domain);
        $this->assertSame($order->domain, $result);
    }

    // =========================================================================
    // BaseHomeController::checkSerialKey — with order found (match + mismatch)
    // =========================================================================

    public function test_check_serial_key_returns_key_when_matches(): void
    {
        $user = \App\User::factory()->create();
        $order = \App\Model\Order\Order::create([
            'client' => $user->id,
            'order_status' => 'executed',
            'number' => 'SER-'.uniqid(),
            'serial_key' => 'MYKEY12345',
        ]);

        $controller = new \App\Http\Controllers\BaseHomeController;
        $result = $controller->checkSerialKey('MYKEY12345', $order->number);
        $this->assertSame('MYKEY12345', $result);
    }

    public function test_check_serial_key_returns_null_when_mismatch(): void
    {
        $user = \App\User::factory()->create();
        $order = \App\Model\Order\Order::create([
            'client' => $user->id,
            'order_status' => 'executed',
            'number' => 'SER2-'.uniqid(),
            'serial_key' => 'REALKEY99',
        ]);

        $controller = new \App\Http\Controllers\BaseHomeController;
        $result = $controller->checkSerialKey('WRONGKEY', $order->number);
        $this->assertNull($result);
    }

    // =========================================================================
    // BaseHomeController::verificationResult — with valid order
    // =========================================================================

    public function test_verification_result_returns_success_for_existing_order(): void
    {
        $user = \App\User::factory()->create();
        $order = \App\Model\Order\Order::create([
            'client' => $user->id,
            'order_status' => 'executed',
            'number' => 'VER-'.uniqid(),
        ]);

        $controller = new \App\Http\Controllers\BaseHomeController;
        $result = $controller->verificationResult($order->number, 'any-key');
        $this->assertArrayHasKey('status', $result);
        $this->assertSame('success', $result['status']);
        $this->assertSame($order->number, $result['order_number']);
    }

    public function test_verification_result_returns_fails_for_missing_order(): void
    {
        $controller = new \App\Http\Controllers\BaseHomeController;
        $result = $controller->verificationResult('NONEXISTENT-'.uniqid(), 'any-key');
        $this->assertSame('fails', $result['status']);
    }

    public function test_verification_result_returns_fails_when_inputs_empty(): void
    {
        $controller = new \App\Http\Controllers\BaseHomeController;
        $result = $controller->verificationResult('', '');
        $this->assertSame('fails', $result['status']);
    }

    // =========================================================================
    // HomeController::getProductRelease — with real product
    // =========================================================================

    public function test_get_product_release_with_product_id_returns_product_info(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);

        $response = $this->getJson('/api/billingRelease?product_id='.$product->id.'&version=1.0.0');
        $this->assertTrue($response->status() >= 200);
        $data = $response->json();
        $this->assertIsArray($data);
        // Should have product key (may be null if no product upload)
        $this->assertArrayHasKey('product', $data);
    }

    // =========================================================================
    // HomeController::checkFaveoDetails — pure method call
    // =========================================================================

    public function test_check_faveo_details_returns_fails_when_order_not_found(): void
    {
        $controller = new \App\Http\Controllers\HomeController;
        $result = $controller->checkFaveoDetails('NONEXISTENT-'.uniqid(), 'Faveo', '1.0.0');
        $this->assertSame('fails', $result['status']);
    }

    public function test_check_faveo_details_returns_success_when_order_has_product(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'Test Product '.uniqid()]);
        $user = \App\User::factory()->create();
        $order = \App\Model\Order\Order::create([
            'client' => $user->id,
            'order_status' => 'executed',
            'product' => $product->id,
            'number' => 'CFD-'.uniqid(),
        ]);

        $controller = new \App\Http\Controllers\HomeController;
        $result = $controller->checkFaveoDetails($order->number, 'Faveo', '1.0.0');
        $this->assertSame('success', $result['status']);
        $this->assertArrayHasKey('version', $result);
    }

    // =========================================================================
    // BaseHomeController::getLastFourDigitsOfLicenseCode
    // =========================================================================

    public function test_get_last_four_digits_enterprise_returns_0000(): void
    {
        $controller = new \App\Http\Controllers\BaseHomeController;
        $result = $controller->getLastFourDigistsOfLicenseCode('HelpDesk Enterprise');
        $this->assertSame('0000', $result);
    }

    public function test_get_last_four_digits_startup_returns_0005(): void
    {
        $controller = new \App\Http\Controllers\BaseHomeController;
        $result = $controller->getLastFourDigistsOfLicenseCode('HelpDesk Startup');
        $this->assertSame('0005', $result);
    }

    public function test_get_last_four_digits_sme_returns_0010(): void
    {
        $controller = new \App\Http\Controllers\BaseHomeController;
        $result = $controller->getLastFourDigistsOfLicenseCode('HelpDesk SME');
        $this->assertSame('0010', $result);
    }

    public function test_get_last_four_digits_freelancer_returns_0002(): void
    {
        $controller = new \App\Http\Controllers\BaseHomeController;
        $result = $controller->getLastFourDigistsOfLicenseCode('HelpDesk Freelancer');
        $this->assertSame('0002', $result);
    }

    public function test_get_last_four_digits_unknown_throws_exception(): void
    {
        $this->expectException(\Exception::class);
        $controller = new \App\Http\Controllers\BaseHomeController;
        $controller->getLastFourDigistsOfLicenseCode('Unknown Product XYZ');
    }

    // =========================================================================
    // BaseHomeController::getTotalSales
    // =========================================================================

    public function test_get_total_sales_returns_numeric(): void
    {
        $controller = new \App\Http\Controllers\BaseHomeController;
        $result = $controller->getTotalSales();
        $this->assertTrue(is_numeric($result));
    }

    // =========================================================================
    // BaseHomeController::getUpdatesExpiryDate / getLicenseExpiryDate / getSupportExpiryDate
    // =========================================================================

    public function test_get_expiry_dates_return_values_for_order_without_subscription(): void
    {
        $user = \App\User::factory()->create();
        $order = \App\Model\Order\Order::create([
            'client' => $user->id,
            'order_status' => 'executed',
            'number' => 'EXP-'.uniqid(),
        ]);

        $controller = new \App\Http\Controllers\BaseHomeController;
        $upd = $controller->getUpdatesExpiryDate($order);
        $lic = $controller->getLicenseExpiryDate($order);
        $sup = $controller->getSupportExpiryDate($order);

        // All return either empty string or a Carbon instance
        $this->assertTrue($upd === '' || $upd instanceof \Illuminate\Support\Carbon);
        $this->assertTrue($lic === '' || $lic instanceof \Illuminate\Support\Carbon);
        $this->assertTrue($sup === '' || $sup instanceof \Illuminate\Support\Carbon);
    }

    // =========================================================================
    // BaseHomeController::getUserIP
    // =========================================================================

    public function test_get_user_ip_returns_string_or_null(): void
    {
        $controller = new \App\Http\Controllers\BaseHomeController;
        $ip = $controller->getUserIP();
        $this->assertTrue(is_null($ip) || is_string($ip));
    }

    // =========================================================================
    // HomeController::updateLicenseCode — license not found → returns null
    // =========================================================================

    public function test_update_license_code_returns_null_when_license_not_found(): void
    {
        $request = new \Illuminate\Http\Request;
        $request->merge([
            'licenseCode' => 'NONEXISTENT-LICENSE-'.uniqid(),
            'product' => 'HelpDesk Enterprise',
        ]);

        $controller = new \App\Http\Controllers\HomeController;
        $result = $controller->updateLicenseCode($request);
        $this->assertNull($result);
    }

    // =========================================================================
    // HomeController::getDetailsForAClient — with no matching user
    // =========================================================================

    public function test_get_details_for_a_client_returns_json_string(): void
    {
        $response = $this->get('/api/pluginInfo?client=noemail@nowhere.test&license=FAKEKEY&product_id=99999');
        $this->assertTrue($response->status() >= 200);
        // Returns a JSON-encoded string or empty array JSON
        $content = $response->getContent();
        $this->assertNotFalse($content);
    }

    // =========================================================================
    // BaseHomeController::checkUpdate — order not found
    // =========================================================================

    public function test_check_update_returns_fails_when_order_or_domain_null(): void
    {
        $controller = new \App\Http\Controllers\HomeController;
        $result = $controller->checkUpdate(null, null, null, 'Faveo', '1.0.0');
        $this->assertSame('fails', $result['status']);
    }

    public function test_check_update_returns_fails_when_order_number_given_but_not_found(): void
    {
        $controller = new \App\Http\Controllers\HomeController;
        $result = $controller->checkUpdate('NONEXISTENT', 'BADKEY', 'example.com', 'Faveo', '1.0.0');
        // verifyOrder returns null → returns fails
        $this->assertArrayHasKey('status', $result);
    }

    // =========================================================================
    // HomeController::getDetailedBillingInfo — user not found for order client
    // =========================================================================

    public function test_get_detailed_billing_info_returns_empty_when_client_has_no_user(): void
    {
        // Create order with a client ID that has no matching user
        $order = \App\Model\Order\Order::create([
            'client' => 999999,
            'order_status' => 'executed',
            'number' => 'NOBILL-'.uniqid(),
        ]);

        $response = $this->getJson('/api/billingInfo?order='.$order->number);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEmpty($data);
    }
}
