<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers;

use App\Http\Controllers\RazorpayController;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Plugins\Payment\Dto\SubscriptionRequest;
use App\Plugins\Payment\Dto\SubscriptionResult;
use App\Services\Payment\SubscriptionService;
use App\User;
use Illuminate\Support\Facades\Date;
use Mockery;
use Tests\DBTestCase;

class RazorpayControllerTest extends DBTestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_handle_rzp_auto_pay_immediate_skips_upfront_charge_and_starts_at_period_end(): void
    {
        $subscription = Subscription::factory()->create(['update_ends_at' => '2027-07-29 00:00:00']);
        $invoice = Invoice::factory()->create();
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $product = Product::factory()->create();

        /** @var SubscriptionService&Mockery\MockInterface $serviceMock */
        $serviceMock = Mockery::mock(SubscriptionService::class);
        $serviceMock->shouldReceive('createSubscription')
            ->once()
            ->withArgs(function (string $gateway, SubscriptionRequest $request) {
                return $gateway === 'Razorpay'
                    && $request->includeUpfrontCharge === false
                    && $request->startAt === Date::parse('2027-07-29 00:00:00')->timestamp;
            })
            ->andReturn(new SubscriptionResult('Razorpay', 'sub_test', 'created', []));
        $this->app->instance(SubscriptionService::class, $serviceMock);

        $controller = new RazorpayController;
        $controller->handleRzpAutoPay(1000, 30, 'Test Plan', $invoice, 'INR', $subscription, $user, $order, null, $product, immediate: true);

        $this->assertTrue(true);
    }

    public function test_handle_rzp_auto_pay_cron_includes_upfront_charge_and_starts_after_next_cycle(): void
    {
        $subscription = Subscription::factory()->create(['update_ends_at' => '2027-07-29 00:00:00']);
        $invoice = Invoice::factory()->create();
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $product = Product::factory()->create();

        /** @var SubscriptionService&Mockery\MockInterface $serviceMock */
        $serviceMock = Mockery::mock(SubscriptionService::class);
        $serviceMock->shouldReceive('createSubscription')
            ->once()
            ->withArgs(function (string $gateway, SubscriptionRequest $request) {
                return $gateway === 'Razorpay'
                    && $request->includeUpfrontCharge === true
                    && $request->startAt === Date::parse('2027-07-29 00:00:00')->addDays(30)->timestamp;
            })
            ->andReturn(new SubscriptionResult('Razorpay', 'sub_test', 'created', []));
        $this->app->instance(SubscriptionService::class, $serviceMock);

        $controller = new RazorpayController;
        $controller->handleRzpAutoPay(1000, 30, 'Test Plan', $invoice, 'INR', $subscription, $user, $order, null, $product);

        $this->assertTrue(true);
    }
}
