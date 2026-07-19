<?php

namespace Tests\Unit\Backend\Http\Controllers\Product;

use App\Facades\Attach;
use App\Http\Controllers\Product\BaseProductController;
use App\License\Services\ProductBundleStampingService;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\User;
use Illuminate\Http\Response;
use Mockery;
use Tests\DBTestCase;

class BaseProductControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
    }

    private function controller(?ProductBundleStampingService $stampingService = null): BaseProductController
    {
        return new BaseProductController($stampingService ?? Mockery::mock(ProductBundleStampingService::class));
    }

    private function orderWithSubscription(Product $product, User $owner, array $subscriptionOverrides = []): Order
    {
        $order = Order::factory()->create(['product' => $product->id, 'client' => $owner->id]);

        Subscription::factory()->create(array_merge([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'user_id' => $owner->id,
        ], $subscriptionOverrides));

        return $order->fresh();
    }

    public function test_user_download_denies_a_user_who_is_neither_admin_nor_the_order_owner(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $stranger = User::factory()->create(['role' => 'user']);
        $product = Product::factory()->create();
        $order = $this->orderWithSubscription($product, $owner);

        $this->be($stranger);

        $response = $this->controller()->userDownload($order->id);

        $data = json_decode((string) $response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertSame(trans('message.no_permission_for_action'), $data['message']);
    }

    public function test_user_download_errors_when_order_has_no_subscription(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $product = Product::factory()->create();
        $order = Order::factory()->create(['product' => $product->id, 'client' => $owner->id]);

        $this->be($owner);

        $response = $this->controller()->userDownload($order->id);

        $data = json_decode((string) $response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertSame(trans('message.no_order_exists_invoice'), $data['message']);
    }

    public function test_user_download_errors_when_subscription_update_window_has_expired(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $product = Product::factory()->create();
        $order = $this->orderWithSubscription($product, $owner, ['update_ends_at' => now()->subDay()]);

        $this->be($owner);

        $response = $this->controller()->userDownload($order->id);

        $data = json_decode((string) $response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertSame(trans('message.renew_subscription_download'), $data['message']);
    }

    public function test_user_download_delegates_to_download_for_the_owning_client(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $product = Product::factory()->create();
        $order = $this->orderWithSubscription($product, $owner, ['update_ends_at' => now()->addMonth()]);
        ProductUpload::factory()->create([
            'product_id' => $product->id,
            'file' => 'release.zip',
            'is_private' => 0,
            'created_at' => now()->subDay(),
        ]);

        Attach::shouldReceive('exists')->once()->with('products/release.zip')->andReturn(true);

        $stampedResponse = new Response('stamped-bytes');
        $stampingService = Mockery::mock(ProductBundleStampingService::class);
        $stampingService->shouldReceive('downloadResponseFor')
            ->once()
            ->withArgs(fn ($v, $p, $path, $passedOrder) => $p->is($product) && $passedOrder->is($order))
            ->andReturn($stampedResponse);

        $this->be($owner);

        $response = $this->controller($stampingService)->userDownload($order->id);

        $this->assertSame($stampedResponse, $response);
    }

    public function test_user_download_allows_an_admin_regardless_of_order_owner(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();
        $order = $this->orderWithSubscription($product, $owner, ['update_ends_at' => now()->addMonth()]);
        ProductUpload::factory()->create([
            'product_id' => $product->id,
            'file' => 'release.zip',
            'is_private' => 0,
            'created_at' => now()->subDay(),
        ]);

        Attach::shouldReceive('exists')->once()->andReturn(true);

        $stampingService = Mockery::mock(ProductBundleStampingService::class);
        $stampingService->shouldReceive('downloadResponseFor')->once()->andReturn(new Response('stamped-bytes'));

        $this->be($admin);

        $response = $this->controller($stampingService)->userDownload($order->id);

        $this->assertInstanceOf(Response::class, $response);
    }
}
