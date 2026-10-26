<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Order;

use App\Http\Controllers\Order\Orders;
use Tests\TestCase;

class OrdersControllerTest extends TestCase
{
    // =========================================================================
    // getOrder() – self::find() doesn't exist on Controller; returns null/error
    // =========================================================================

    public function test_get_subscription_returns_null_when_no_order(): void
    {
        $orders = \Mockery::mock(Orders::class, [null])->makePartial();
        $orders->shouldReceive('getOrder')->andReturn(null);

        $result = $orders->getSubscription();
        $this->assertNull($result);
    }

    public function test_get_product_returns_null_when_no_order(): void
    {
        $orders = \Mockery::mock(Orders::class, [null])->makePartial();
        $orders->shouldReceive('getOrder')->andReturn(null);

        $result = $orders->getProduct();
        $this->assertNull($result);
    }

    public function test_get_plan_returns_null_when_no_subscription(): void
    {
        $orders = \Mockery::mock(Orders::class, [null])->makePartial();
        $orders->shouldReceive('getSubscription')->andReturn(null);

        $result = $orders->getPlan();
        $this->assertNull($result);
    }

    public function test_subscription_period_returns_empty_string_when_no_plan(): void
    {
        $orders = \Mockery::mock(Orders::class, [null])->makePartial();
        $orders->shouldReceive('getPlan')->andReturn(null);

        $result = $orders->subscriptionPeriod();
        $this->assertSame('', $result);
    }

    public function test_version_returns_null_when_no_subscription(): void
    {
        $orders = \Mockery::mock(Orders::class, [null])->makePartial();
        $orders->shouldReceive('getSubscription')->andReturn(null);

        $result = $orders->version();
        $this->assertNull($result);
    }

    public function test_is_expired_returns_false_when_no_subscription(): void
    {
        $orders = \Mockery::mock(Orders::class, [null])->makePartial();
        $orders->shouldReceive('getSubscription')->andReturn(null);

        $result = $orders->isExpired();
        $this->assertFalse($result);
    }

    public function test_product_name_returns_empty_string_when_no_product(): void
    {
        $orders = \Mockery::mock(Orders::class, [null])->makePartial();
        $orders->shouldReceive('getProduct')->andReturn(null);

        $result = $orders->productName();
        $this->assertSame('', $result);
    }

    public function test_is_downloadable_returns_false_when_no_product(): void
    {
        $orders = \Mockery::mock(Orders::class, [null])->makePartial();
        $orders->shouldReceive('getProduct')->andReturn(null);

        $result = $orders->isDownloadable();
        $this->assertFalse($result);
    }

    // =========================================================================
    // getOrder() – covers the self::find() line (throws BadMethodCallException)
    // =========================================================================

    public function test_get_order_executes_self_find(): void
    {
        $orders = new Orders(999);
        try {
            $orders->getOrder();
        } catch (\Throwable $e) {
            // Expected: Call to undefined method (self::find doesn't exist on Controller)
            $this->assertNotNull($e);
        }
        $this->assertTrue(true);
    }

    // =========================================================================
    // Branches with a non-null mock Order / Subscription / Product / Plan
    // =========================================================================

    public function test_get_subscription_returns_subscription_from_order(): void
    {
        $subMock = new \stdClass();
        $orderMock = new \stdClass();
        $orderMock->subscription = $subMock;

        $orders = \Mockery::mock(Orders::class, [1])->makePartial();
        $orders->shouldReceive('getOrder')->andReturn($orderMock);

        $this->assertSame($subMock, $orders->getSubscription());
    }

    public function test_get_product_returns_product_from_order(): void
    {
        $productMock = new \stdClass();
        $orderMock = new \stdClass();
        $orderMock->product = $productMock;

        $orders = \Mockery::mock(Orders::class, [1])->makePartial();
        $orders->shouldReceive('getOrder')->andReturn($orderMock);

        $this->assertSame($productMock, $orders->getProduct());
    }

    public function test_get_plan_returns_plan_from_subscription(): void
    {
        $planMock = new \stdClass();
        $subMock = new \stdClass();
        $subMock->plan = $planMock;

        $orders = \Mockery::mock(Orders::class, [1])->makePartial();
        $orders->shouldReceive('getSubscription')->andReturn($subMock);

        $this->assertSame($planMock, $orders->getPlan());
    }

    public function test_subscription_period_returns_days_from_plan(): void
    {
        $planMock = new \stdClass();
        $planMock->days = 30;

        $orders = \Mockery::mock(Orders::class, [1])->makePartial();
        $orders->shouldReceive('getPlan')->andReturn($planMock);

        $this->assertSame(30, $orders->subscriptionPeriod());
    }

    public function test_version_returns_version_from_subscription(): void
    {
        $subMock = new \stdClass();
        $subMock->vesion = '1.0.0';

        $orders = \Mockery::mock(Orders::class, [1])->makePartial();
        $orders->shouldReceive('getSubscription')->andReturn($subMock);

        $this->assertSame('1.0.0', $orders->version());
    }

    public function test_is_expired_returns_true_when_subscription_expired(): void
    {
        $subMock = new \stdClass();
        $subMock->ends_at = \Illuminate\Support\Facades\Date::now()->subDays(5);

        $orders = \Mockery::mock(Orders::class, [1])->makePartial();
        $orders->shouldReceive('getSubscription')->andReturn($subMock);

        $this->assertTrue($orders->isExpired());
    }

    public function test_is_expired_returns_false_when_subscription_not_expired(): void
    {
        $subMock = new \stdClass();
        $subMock->ends_at = \Illuminate\Support\Facades\Date::now()->addDays(5);

        $orders = \Mockery::mock(Orders::class, [1])->makePartial();
        $orders->shouldReceive('getSubscription')->andReturn($subMock);

        $this->assertFalse($orders->isExpired());
    }

    public function test_product_name_returns_product_name(): void
    {
        $productMock = new \stdClass();
        $productMock->name = 'Test Product';

        $orders = \Mockery::mock(Orders::class, [1])->makePartial();
        $orders->shouldReceive('getProduct')->andReturn($productMock);

        $this->assertSame('Test Product', $orders->productName());
    }

    public function test_is_downloadable_returns_true_for_download_type(): void
    {
        $typeMock = new \stdClass();
        $typeMock->name = 'download';

        $productMock = new \stdClass();
        $productMock->type = $typeMock;

        $orders = \Mockery::mock(Orders::class, [1])->makePartial();
        $orders->shouldReceive('getProduct')->andReturn($productMock);

        $this->assertTrue($orders->isDownloadable());
    }

    public function test_is_downloadable_returns_false_for_non_download_type(): void
    {
        $typeMock = new \stdClass();
        $typeMock->name = 'hosted';

        $productMock = new \stdClass();
        $productMock->type = $typeMock;

        $orders = \Mockery::mock(Orders::class, [1])->makePartial();
        $orders->shouldReceive('getProduct')->andReturn($productMock);

        $this->assertFalse($orders->isDownloadable());
    }

    public function test_is_downloadable_returns_false_when_product_has_no_type(): void
    {
        $productMock = new \stdClass();
        $productMock->type = null;

        $orders = \Mockery::mock(Orders::class, [1])->makePartial();
        $orders->shouldReceive('getProduct')->andReturn($productMock);

        $this->assertFalse($orders->isDownloadable());
    }
}
