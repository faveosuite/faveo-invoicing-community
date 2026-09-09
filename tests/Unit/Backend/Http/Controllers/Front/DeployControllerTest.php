<?php

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\Http\Controllers\Front\DeployController;
use App\License\Services\ProductBundleStampingService;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
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
    }

    private function controller(?ProductBundleStampingService $stampingService = null): DeployController
    {
        return new DeployController($stampingService ?? Mockery::mock(ProductBundleStampingService::class));
    }

    // -------------------------------------------------------------------------
    // getVersions
    // -------------------------------------------------------------------------

    public function test_get_versions_denies_a_user_who_does_not_own_the_order(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $stranger = User::factory()->create(['role' => 'user']);
        $product = Product::factory()->create();
        $order = Order::factory()->create(['product' => $product->id, 'client' => $owner->id]);

        $this->be($stranger);

        $response = $this->controller()->getVersions($order->id);

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_get_versions_returns_only_public_versions_with_a_file(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $product = Product::factory()->create();
        $order = Order::factory()->create(['product' => $product->id, 'client' => $owner->id]);

        $visible = ProductUpload::factory()->create(['product_id' => $product->id, 'is_private' => 0, 'file' => 'release.zip']);
        ProductUpload::factory()->create(['product_id' => $product->id, 'is_private' => 1, 'file' => 'private.zip']);
        ProductUpload::factory()->create(['product_id' => $product->id, 'is_private' => 0, 'file' => '']);

        $this->be($owner);

        $response = $this->controller()->getVersions($order->id);
        $data = json_decode((string) $response->getContent(), true);

        $this->assertTrue($data['success']);
        $ids = array_column($data['data'], 'id');
        $this->assertSame([$visible->id], $ids);
    }

    // -------------------------------------------------------------------------
    // stepUpload (invoked directly — reached from deployStep() after full
    // deploy-form validation, which is exercised separately by request tests)
    // -------------------------------------------------------------------------

    public function test_step_upload_denies_a_user_who_does_not_own_the_order(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $stranger = User::factory()->create(['role' => 'user']);
        $product = Product::factory()->create();
        $order = Order::factory()->create(['product' => $product->id, 'client' => $owner->id]);

        $this->be($stranger);

        $request = Request::create('/deploy-product-step', 'POST', ['order_id' => $order->id]);
        $controller = $this->controller();
        $response = $this->getPrivateMethod($controller, 'stepUpload', [$request, 'unused']);

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_step_upload_errors_when_resolved_file_is_not_found_in_storage(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $product = Product::factory()->create();
        $order = Order::factory()->create(['product' => $product->id, 'client' => $owner->id]);
        ProductUpload::factory()->create(['product_id' => $product->id, 'is_private' => 0, 'file' => 'release.zip']);

        Storage::shouldReceive('exists')->once()->with('products/release.zip')->andReturn(false);

        $this->be($owner);

        $request = Request::create('/deploy-product-step', 'POST', ['order_id' => $order->id]);
        $controller = $this->controller();
        $response = $this->getPrivateMethod($controller, 'stepUpload', [$request, 'unused']);

        $data = json_decode((string) $response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertSame(trans('message.deploy_file_not_found'), $data['message']);
    }

    public function test_step_upload_errors_when_stamping_throws(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $product = Product::factory()->create();
        $order = Order::factory()->create(['product' => $product->id, 'client' => $owner->id]);
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
        $response = $this->getPrivateMethod($controller, 'stepUpload', [$request, 'unused']);

        $data = json_decode((string) $response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertSame(trans('message.deploy_upload_failed'), $data['message']);
    }
}
