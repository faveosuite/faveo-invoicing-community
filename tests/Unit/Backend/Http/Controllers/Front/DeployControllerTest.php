<?php

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\Http\Controllers\Front\DeployController;
use App\License\Services\ProductBundleStampingService;
use App\Model\Common\Setting;
use App\Model\License\LicensePermission;
use App\Model\License\LicenseType;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\DBTestCase;

class DeployControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        Setting::where('id', 1)->update(['deployment_enabled' => 1]);
    }

    private function controller(?ProductBundleStampingService $stampingService = null): DeployController
    {
        return new DeployController($stampingService ?? Mockery::mock(ProductBundleStampingService::class));
    }

    /** @param  array<int, string>  $permissionLabels */
    private function productWithPermissions(array $permissionLabels = ['Can be Downloaded']): Product
    {
        $type = LicenseType::factory()->create();

        foreach ($permissionLabels as $label) {
            $permission = LicensePermission::firstOrCreate(['permissions' => $label]);
            $type->permissions()->attach($permission->id);
        }

        return Product::factory()->create(['type' => $type->id]);
    }

    /** @param  array<string, mixed>  $subscriptionOverrides */
    private function orderWithSubscription(Product $product, User $owner, array $subscriptionOverrides = []): Order
    {
        $order = Order::factory()->create(['product' => $product->id, 'client' => $owner->id]);

        Subscription::factory()->create(array_merge([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'user_id' => $owner->id,
            'update_ends_at' => null,
        ], $subscriptionOverrides));

        return $order->fresh();
    }

    /** @param  array<string, mixed>  $overrides */
    private function stepPayload(Order $order, array $overrides = []): array
    {
        return array_merge([
            'step' => 'verify',
            'order_id' => $order->id,
            'host' => '8.8.8.8',
            'port' => 22,
            'username' => 'deploy',
            'auth_method' => 'password',
            'password' => 'secret',
            'deploy_mode' => 'extract_only',
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // getVersions
    // -------------------------------------------------------------------------

    public function test_get_versions_denies_a_user_who_does_not_own_the_order(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $stranger = User::factory()->create(['role' => 'user']);
        $order = $this->orderWithSubscription($this->productWithPermissions(), $owner);

        $this->be($stranger);

        $response = $this->controller()->getVersions($order->id);

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_get_versions_returns_only_public_versions_with_a_file(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $product = $this->productWithPermissions();
        $order = $this->orderWithSubscription($product, $owner);

        $visible = ProductUpload::factory()->create(['product_id' => $product->id, 'is_private' => 0, 'file' => 'release.zip']);
        ProductUpload::factory()->create(['product_id' => $product->id, 'is_private' => 1, 'file' => 'private.zip']);
        ProductUpload::factory()->create(['product_id' => $product->id, 'is_private' => 0, 'file' => '']);

        $this->be($owner);

        $data = json_decode((string) $this->controller()->getVersions($order->id)->getContent(), true);

        $this->assertTrue($data['success']);
        $this->assertSame([$visible->id], array_column($data['data'], 'id'));
    }

    public function test_get_versions_hides_versions_released_after_updates_expired(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $product = $this->productWithPermissions(['Can be Downloaded', 'Allow Downloads Before Updates Expire']);
        $order = $this->orderWithSubscription($product, $owner, ['update_ends_at' => now()->subDays(5)]);

        $before = ProductUpload::factory()->create(['product_id' => $product->id, 'is_private' => 0, 'file' => 'old.zip', 'created_at' => now()->subDays(10)]);
        ProductUpload::factory()->create(['product_id' => $product->id, 'is_private' => 0, 'file' => 'new.zip', 'created_at' => now()]);

        $this->be($owner);

        $data = json_decode((string) $this->controller()->getVersions($order->id)->getContent(), true);

        $this->assertSame([$before->id], array_column($data['data'], 'id'));
    }

    public function test_get_versions_blocks_an_expired_order_without_download_till_expiry(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $order = $this->orderWithSubscription($this->productWithPermissions(), $owner, ['update_ends_at' => now()->subDay()]);

        $this->be($owner);

        $response = $this->controller()->getVersions($order->id);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame(trans('message.renew_subscription_download'), json_decode((string) $response->getContent(), true)['message']);
    }

    public function test_get_versions_blocks_when_deployment_is_disabled(): void
    {
        Setting::where('id', 1)->update(['deployment_enabled' => 0]);
        $owner = User::factory()->create(['role' => 'user']);
        $order = $this->orderWithSubscription($this->productWithPermissions(), $owner);

        $this->be($owner);

        $this->assertSame(403, $this->controller()->getVersions($order->id)->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // deployStep guards (run before any SSH connection is attempted)
    // -------------------------------------------------------------------------

    public function test_deploy_step_denies_a_user_who_does_not_own_the_order(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $stranger = User::factory()->create(['role' => 'user']);
        $order = $this->orderWithSubscription($this->productWithPermissions(), $owner);

        $this->be($stranger);

        $this->postJson('deploy-product-step', $this->stepPayload($order))->assertStatus(403);
    }

    public function test_deploy_step_rejects_private_and_loopback_hosts(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $order = $this->orderWithSubscription($this->productWithPermissions(), $owner);

        $this->be($owner);

        foreach (['127.0.0.1', '10.0.0.5', '169.254.169.254', 'localhost'] as $host) {
            $this->postJson('deploy-product-step', $this->stepPayload($order, ['host' => $host]))
                ->assertStatus(422)
                ->assertJson(['message' => trans('message.deploy_host_not_allowed')]);
        }
    }

    public function test_whitelist_deploy_ip_opens_only_the_listed_range(): void
    {
        config(['custom.whitelist_deploy_ip' => ['192.168.1.0/24']]);
        $controller = $this->controller();

        $this->assertSame('192.168.1.20', $this->getPrivateMethod($controller, 'publicIp', ['192.168.1.20']));
        $this->assertNull($this->getPrivateMethod($controller, 'publicIp', ['192.168.2.20']));
        $this->assertNull($this->getPrivateMethod($controller, 'publicIp', ['127.0.0.1']));
    }

    public function test_deploy_step_requires_install_fields_for_the_install_step(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $order = $this->orderWithSubscription($this->productWithPermissions(), $owner);

        $this->be($owner);

        $this->postJson('deploy-product-step', $this->stepPayload($order, ['step' => 'install', 'deploy_mode' => 'fresh_install', 'ssl_type' => 'C']))
            ->assertStatus(412)
            ->assertJsonValidationErrors(['install_domain', 'install_email', 'web_server', 'ssl_cert_path', 'ssl_key_path'], 'message');
    }

    // -------------------------------------------------------------------------
    // stepUpload (invoked directly — deployStep has already checked ownership)
    // -------------------------------------------------------------------------

    public function test_step_upload_errors_when_resolved_file_is_not_found_in_storage(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $product = $this->productWithPermissions();
        $order = $this->orderWithSubscription($product, $owner);
        ProductUpload::factory()->create(['product_id' => $product->id, 'is_private' => 0, 'file' => 'release.zip']);

        Storage::shouldReceive('exists')->once()->with('products/release.zip')->andReturn(false);

        $this->be($owner);

        $request = Request::create('/deploy-product-step', 'POST', ['order_id' => $order->id]);
        $controller = $this->controller();
        $response = $this->getPrivateMethod($controller, 'stepUpload', [$request, $order]);

        $data = json_decode((string) $response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertSame(trans('message.deploy_file_not_found'), $data['message']);
    }

    public function test_step_upload_errors_when_stamping_throws(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $product = $this->productWithPermissions();
        $order = $this->orderWithSubscription($product, $owner);
        $upload = ProductUpload::factory()->create(['product_id' => $product->id, 'is_private' => 0, 'file' => 'release.zip']);

        Storage::shouldReceive('exists')->once()->with('products/release.zip')->andReturn(true);

        $stampingService = Mockery::mock(ProductBundleStampingService::class);
        $stampingService->shouldReceive('stampToLocalFile')
            ->once()
            ->withArgs(fn ($path, $p, $version, $passedOrder) => $path === 'products/release.zip' && $p->is($product) && $version === $upload->version && $passedOrder->is($order))
            ->andThrow(new RuntimeException('stamping failed'));

        $this->be($owner);

        $request = Request::create('/deploy-product-step', 'POST', ['order_id' => $order->id]);
        $controller = $this->controller($stampingService);
        $response = $this->getPrivateMethod($controller, 'stepUpload', [$request, $order]);

        $data = json_decode((string) $response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertSame(trans('message.deploy_upload_failed'), $data['message']);
    }
}
