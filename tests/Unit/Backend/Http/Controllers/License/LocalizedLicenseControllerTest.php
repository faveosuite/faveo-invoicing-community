<?php

namespace Tests\Unit\Backend\Http\Controllers\License;

use App\License\Models\License;
use App\License\Models\LicensePlugin;
use App\License\Services\Ed25519SigningService;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\User;
use Tests\DBTestCase;

class LocalizedLicenseControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    private function createProduct(): Product
    {
        return Product::factory()->create();
    }

    private function createOrder(int $clientId, array $overrides = []): Order
    {
        return Order::create(array_merge([
            'client' => $clientId,
            'order_status' => 'executed',
            'number' => (string) mt_rand(10000000, 99999999),
        ], $overrides));
    }

    private function createLicense(string $orderNumber, int $productId, array $overrides = []): License
    {
        return License::create(array_merge([
            'product_id' => $productId,
            'license_code' => 'LIC-'.uniqid(),
            'license_order_number' => $orderNumber,
            'license_status' => 1,
        ], $overrides));
    }

    // =========================================================================
    // downloadFile
    // =========================================================================

    public function test_download_file_returns_401_when_not_authenticated(): void
    {
        auth()->logout();
        $response = $this->get('/downloadLicenseFile?orderNo=ORD001');
        $response->assertStatus(401);
    }

    public function test_download_file_returns_403_when_order_not_owned(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $order = $this->createOrder($owner->id);

        $this->getLoggedInUser('user');

        $response = $this->get('/downloadLicenseFile?orderNo='.$order->number);
        $response->assertStatus(403);
    }

    public function test_download_file_returns_404_when_no_license(): void
    {
        $this->getLoggedInUser('user');
        $order = $this->createOrder($this->user->id);

        $response = $this->get('/downloadLicenseFile?orderNo='.$order->number);
        $response->assertStatus(404);
    }

    public function test_download_file_returns_signed_file_when_owned(): void
    {
        $this->getLoggedInUser('user');
        $product = $this->createProduct();
        $order = $this->createOrder($this->user->id, ['product' => $product->id]);
        $this->createLicense($order->number, $product->id);

        $response = $this->get('/downloadLicenseFile?orderNo='.$order->number);

        $response->assertStatus(200);
        $file = json_decode($response->streamedContent(), true);
        $this->assertArrayHasKey('license', $file);
        $this->assertArrayHasKey('signature', $file);
        $this->assertTrue(
            resolve(Ed25519SigningService::class)->verify($file['license'], $file['signature'])
        );
    }

    // =========================================================================
    // downloadFileAdmin
    // =========================================================================

    public function test_download_file_admin_returns_404_when_no_license(): void
    {
        $response = $this->get('/LocalizedLicense/downloadLicense/NO_SUCH_ORDER');
        $response->assertStatus(404);
    }

    public function test_download_file_admin_returns_signed_file(): void
    {
        $product = $this->createProduct();
        $order = $this->createOrder($this->user->id, ['product' => $product->id]);
        $this->createLicense($order->number, $product->id);

        $response = $this->get('/LocalizedLicense/downloadLicense/'.$order->number);

        $response->assertStatus(200);
        $file = json_decode($response->streamedContent(), true);
        $this->assertArrayHasKey('license', $file);
        $this->assertArrayHasKey('signature', $file);
    }

    // =========================================================================
    // submitLicenseBinding
    // =========================================================================

    public function test_submit_license_binding_returns_401_when_not_authenticated(): void
    {
        auth()->logout();
        $response = $this->postJson('/license-binding', ['orderNo' => 'ORD001']);
        $response->assertStatus(401);
    }

    public function test_submit_license_binding_returns_403_when_not_owner_and_not_admin(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $order = $this->createOrder($owner->id);

        $this->getLoggedInUser('user');

        $response = $this->postJson('/license-binding', [
            'orderNo' => $order->number,
            'domain' => 'example.com',
            'machine_id' => 'machine-1',
        ]);
        $response->assertStatus(403);
    }

    public function test_submit_license_binding_returns_400_for_invalid_input(): void
    {
        $order = $this->createOrder($this->user->id);

        $response = $this->postJson('/license-binding', [
            'orderNo' => $order->number,
            'domain' => '',
            'machine_id' => '',
        ]);
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_submit_license_binding_returns_404_when_no_license_row(): void
    {
        $order = $this->createOrder($this->user->id);

        $response = $this->postJson('/license-binding', [
            'orderNo' => $order->number,
            'domain' => 'example.com',
            'machine_id' => 'machine-1',
        ]);
        $response->assertStatus(404);
    }

    public function test_submit_license_binding_updates_license_successfully(): void
    {
        $product = $this->createProduct();
        $order = $this->createOrder($this->user->id, ['product' => $product->id]);
        $license = $this->createLicense($order->number, $product->id);

        $response = $this->postJson('/license-binding', [
            'orderNo' => $order->number,
            'domain' => 'example.com',
            'machine_id' => 'machine-123',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('licenses', [
            'id' => $license->id,
            'license_domain' => 'example.com',
            'license_machine_id' => 'machine-123',
        ]);
    }

    // =========================================================================
    // pluginsForOrder
    // =========================================================================

    public function test_plugins_for_order_returns_401_when_not_authenticated(): void
    {
        auth()->logout();
        $response = $this->get('/LocalizedLicense/ORD001/plugins');
        $response->assertStatus(401);
    }

    public function test_plugins_for_order_returns_403_when_not_owner_and_not_admin(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $order = $this->createOrder($owner->id);

        $this->getLoggedInUser('user');

        $response = $this->get('/LocalizedLicense/'.$order->number.'/plugins');
        $response->assertStatus(403);
    }

    public function test_plugins_for_order_returns_404_when_no_license(): void
    {
        $order = $this->createOrder($this->user->id);

        $response = $this->get('/LocalizedLicense/'.$order->number.'/plugins');
        $response->assertStatus(404);
    }

    public function test_plugins_for_order_returns_addon_products(): void
    {
        $product = $this->createProduct();
        $addon = $this->createProduct();
        $order = $this->createOrder($this->user->id, ['product' => $product->id]);
        $license = $this->createLicense($order->number, $product->id);
        LicensePlugin::create(['license_id' => $license->id, 'product_id' => $addon->id]);

        $response = $this->get('/LocalizedLicense/'.$order->number.'/plugins');

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $addon->id]);
    }

    // =========================================================================
    // listFileModeOrders
    // =========================================================================

    public function test_list_file_mode_orders_returns_orders_in_file_mode(): void
    {
        $product = $this->createProduct();
        $fileModeOrder = $this->createOrder($this->user->id, [
            'product' => $product->id,
            'license_mode' => 'File',
        ]);
        $this->createOrder($this->user->id, [
            'product' => $product->id,
            'license_mode' => 'Database',
        ]);

        $response = $this->getJson('/localized-license/orders');

        $response->assertStatus(200);
        $numbers = collect($response->json('data.data'))->pluck('number');
        $this->assertContains((int) $fileModeOrder->number, $numbers);
    }

    public function test_list_file_mode_orders_filters_by_search_query(): void
    {
        $matchingProduct = Product::factory()->create(['name' => 'UniqueSearchable'.uniqid()]);
        $otherProduct = Product::factory()->create(['name' => 'OtherProduct'.uniqid()]);
        $match = $this->createOrder($this->user->id, [
            'product' => $matchingProduct->id,
            'license_mode' => 'File',
        ]);
        $this->createOrder($this->user->id, [
            'product' => $otherProduct->id,
            'license_mode' => 'File',
        ]);

        $response = $this->getJson('/localized-license/orders?search-query='.$matchingProduct->name);

        $response->assertStatus(200);
        $numbers = collect($response->json('data.data'))->pluck('number');
        $this->assertContains((int) $match->number, $numbers);
        $this->assertNotContains((int) $otherProduct->id, $numbers);
        $this->assertCount(1, $numbers);
    }

    // =========================================================================
    // bulkDisableLicenseMode
    // =========================================================================

    public function test_bulk_disable_license_mode_returns_error_when_no_ids_selected(): void
    {
        $response = $this->postJson('/localized-license/bulk-disable', []);
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_bulk_disable_license_mode_updates_orders_to_database_mode(): void
    {
        $order = $this->createOrder($this->user->id, ['license_mode' => 'File']);

        $response = $this->postJson('/localized-license/bulk-disable', ['select' => [$order->id]]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'license_mode' => 'Database',
        ]);
    }

    // =========================================================================
    // chooseLicenseMode
    // =========================================================================

    public function test_choose_license_mode_sets_to_file(): void
    {
        $order = $this->createOrder($this->user->id, ['license_mode' => 'Database']);

        $response = $this->postJson('/switch-license-mode', [
            'orderNo' => $order->number,
            'choose' => true,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'license_mode' => 'File',
        ]);
    }

    public function test_choose_license_mode_sets_to_database(): void
    {
        $order = $this->createOrder($this->user->id, ['license_mode' => 'File']);

        $response = $this->postJson('/switch-license-mode', [
            'orderNo' => $order->number,
            'choose' => false,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'license_mode' => 'Database',
        ]);
    }
}
