<?php

namespace Tests\Unit\Backend\Http\Controllers\Product;

use App\Facades\Attach;
use App\Http\Controllers\Product\ExtendedBaseProductController;
use App\License\Services\ProductBundleStampingService;
use App\Model\License\LicensePermission;
use App\Model\License\LicenseType;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Exception;
use Illuminate\Http\Response;
use Mockery;
use Tests\DBTestCase;

class ExtendedBaseProductControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
    }

    private function controller(?ProductBundleStampingService $stampingService = null): ExtendedBaseProductController
    {
        return new ExtendedBaseProductController($stampingService ?? Mockery::mock(ProductBundleStampingService::class));
    }

    public function test_download_throws_when_github_product_has_no_resolvable_tag(): void
    {
        $product = Product::factory()->create(['github_owner' => 'faveo', 'github_repository' => 'helpdesk']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(trans('message.file_not_exist'));

        $this->controller()->download($product, null, null);
    }

    public function test_download_throws_when_version_resolves_to_no_file(): void
    {
        $product = Product::factory()->create();
        $version = ProductUpload::factory()->create(['product_id' => $product->id, 'file' => '']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(trans('message.file_not_exist'));

        $this->controller()->download($product, $version);
    }

    public function test_download_throws_when_resolved_file_is_not_found_in_storage(): void
    {
        $product = Product::factory()->create();
        $version = ProductUpload::factory()->create(['product_id' => $product->id, 'file' => 'release.zip']);

        Attach::shouldReceive('exists')->once()->with('products/release.zip')->andReturn(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(trans('message.file_not_exist'));

        $this->controller()->download($product, $version);
    }

    public function test_download_delegates_to_stamping_service_and_returns_its_response(): void
    {
        $product = Product::factory()->create();
        $version = ProductUpload::factory()->create(['product_id' => $product->id, 'file' => 'release.zip']);

        Attach::shouldReceive('exists')->once()->with('products/release.zip')->andReturn(true);

        $stampedResponse = new Response('stamped-bytes');
        $stampingService = Mockery::mock(ProductBundleStampingService::class);
        $stampingService->shouldReceive('downloadResponseFor')
            ->once()
            ->withArgs(fn ($v, $p, $path, $order) => $v->is($version) && $p->is($product) && $path === 'products/release.zip' && $order === null)
            ->andReturn($stampedResponse);

        $response = $this->controller($stampingService)->download($product, $version);

        $this->assertSame($stampedResponse, $response);
    }

    public function test_download_passes_the_order_through_to_the_stamping_service(): void
    {
        $product = Product::factory()->create();
        $version = ProductUpload::factory()->create(['product_id' => $product->id, 'file' => 'release.zip']);
        $order = Order::factory()->create(['product' => $product->id]);

        Attach::shouldReceive('exists')->once()->andReturn(true);

        $stampingService = Mockery::mock(ProductBundleStampingService::class);
        $stampingService->shouldReceive('downloadResponseFor')
            ->once()
            ->withArgs(fn ($v, $p, $path, $passedOrder) => $passedOrder !== null && $passedOrder->is($order))
            ->andReturn(new Response('stamped-bytes'));

        $this->controller($stampingService)->download($product, $version, order: $order);
    }

    public function test_admin_download_returns_error_when_download_permission_is_denied(): void
    {
        $product = Product::factory()->create();
        // No license type / permissions attached, so downloadPermission resolves to 0.

        $response = $this->controller()->adminDownload($product->id);

        $data = json_decode((string) $response->getContent(), true);
        $this->assertFalse($data['success']);
    }

    public function test_admin_download_delegates_to_download_when_permission_is_granted(): void
    {
        $permission = LicensePermission::create(['permissions' => 'Can be Downloaded']);
        $type = LicenseType::factory()->create();
        $type->permissions()->attach($permission->id);

        $product = Product::factory()->create(['type' => $type->id]);
        $version = ProductUpload::factory()->create(['product_id' => $product->id, 'file' => 'release.zip', 'release_type' => 'official', 'is_private' => 0]);

        Attach::shouldReceive('exists')->once()->with('products/release.zip')->andReturn(true);

        $stampedResponse = new Response('stamped-bytes');
        $stampingService = Mockery::mock(ProductBundleStampingService::class);
        $stampingService->shouldReceive('downloadResponseFor')->once()->andReturn($stampedResponse);

        $response = $this->controller($stampingService)->adminDownload($product->id);

        $this->assertSame($stampedResponse, $response);
    }
}
