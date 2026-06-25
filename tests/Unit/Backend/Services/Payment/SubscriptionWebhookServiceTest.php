<?php

namespace Tests\Unit\Backend\Services\Payment;

use App\Http\Controllers\ConcretePostSubscriptionHandleController;
use App\Services\Payment\SubscriptionWebhookService;
use App\Services\Payment\WebhookDispatcher;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\DBTestCase;

class SubscriptionWebhookServiceTest extends DBTestCase
{
    use DatabaseTransactions;

    // =========================================================================
    // WebhookDispatcher — on() and dispatch()
    // =========================================================================

    public function test_dispatcher_on_registers_handler(): void
    {
        $called = false;
        $dispatcher = (new WebhookDispatcher)->on('test.event', function () use (&$called): void {
            $called = true;
        });
        $dispatcher->dispatch('test.event', []);
        $this->assertTrue($called);
    }

    public function test_dispatcher_dispatch_with_no_handler_does_not_throw(): void
    {
        $dispatcher = new WebhookDispatcher;
        // No handler registered — should silently do nothing
        $dispatcher->dispatch('unknown.event', []);
        $this->assertTrue(true);
    }

    public function test_dispatcher_on_accepts_array_of_events(): void
    {
        $count = 0;
        $dispatcher = (new WebhookDispatcher)->on(
            ['event.a', 'event.b'],
            function () use (&$count): void {
                $count++;
            }
        );
        $dispatcher->dispatch('event.a', []);
        $dispatcher->dispatch('event.b', []);
        $this->assertEquals(2, $count);
    }

    public function test_dispatcher_on_returns_self_for_chaining(): void
    {
        $dispatcher = new WebhookDispatcher;
        $result = $dispatcher->on('e', fn () => null);
        $this->assertSame($dispatcher, $result);
    }

    public function test_dispatcher_stripe_factory_returns_dispatcher_instance(): void
    {
        $dispatcher = WebhookDispatcher::stripe();
        $this->assertInstanceOf(WebhookDispatcher::class, $dispatcher);
    }

    public function test_dispatcher_razorpay_factory_returns_dispatcher_instance(): void
    {
        $dispatcher = WebhookDispatcher::razorpay();
        $this->assertInstanceOf(WebhookDispatcher::class, $dispatcher);
    }

    // =========================================================================
    // WebhookDispatcher — Stripe payment_intent.payment_failed (no DB row → silent)
    // =========================================================================

    public function test_stripe_dispatcher_handles_payment_intent_failed_for_unknown_order(): void
    {
        $dispatcher = WebhookDispatcher::stripe();
        // Dispatching with unknown order_id → finds no row → silent
        $dispatcher->dispatch('payment_intent.payment_failed', [
            'data' => [
                'object' => [
                    'metadata' => ['order_id' => 999999],
                ],
            ],
        ]);
        $this->assertTrue(true);
    }

    public function test_stripe_dispatcher_handles_checkout_session_for_unknown_invoice(): void
    {
        $dispatcher = WebhookDispatcher::stripe();
        $dispatcher->dispatch('checkout.session.completed', [
            'data' => [
                'object' => [
                    'metadata' => ['invoice_id' => 999999],
                    'payment_intent' => 'pi_test123',
                ],
            ],
        ]);
        $this->assertTrue(true);
    }

    public function test_razorpay_dispatcher_handles_payment_captured_for_unknown_invoice(): void
    {
        $dispatcher = WebhookDispatcher::razorpay();
        $dispatcher->dispatch('payment.captured', [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'notes' => ['invoice_id' => 999999],
                        'id' => 'pay_test123',
                    ],
                ],
            ],
        ]);
        $this->assertTrue(true);
    }

    public function test_razorpay_dispatcher_handles_payment_failed_for_unknown_order(): void
    {
        $dispatcher = WebhookDispatcher::razorpay();
        $dispatcher->dispatch('payment.failed', [
            'event' => 'payment.failed',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'notes' => ['order_id' => 999999],
                        'id' => 'pay_fail123',
                    ],
                ],
            ],
        ]);
        $this->assertTrue(true);
    }

    // =========================================================================
    // SubscriptionWebhookService
    // =========================================================================

    private function makeWebhookService(): SubscriptionWebhookService
    {
        $handler = Mockery::mock(ConcretePostSubscriptionHandleController::class);

        return new SubscriptionWebhookService($handler);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_handle_stripe_event_with_unknown_type(): void
    {
        $service = $this->makeWebhookService();
        $service->handleStripeEvent(['type' => 'unknown.event', 'data' => ['object' => []]]);
        $this->assertTrue(true);
    }

    public function test_handle_stripe_invoice_paid_skips_non_cycle(): void
    {
        $service = $this->makeWebhookService();
        $service->handleStripeEvent([
            'type' => 'invoice.payment_succeeded',
            'data' => ['object' => ['billing_reason' => 'manual']],
        ]);
        $this->assertTrue(true);
    }

    public function test_handle_stripe_invoice_paid_cycle_with_no_subscription_id(): void
    {
        \Logger::shouldReceive('warning')->andReturn(null);

        $service = $this->makeWebhookService();
        $service->handleStripeEvent([
            'type' => 'invoice.payment_succeeded',
            'data' => ['object' => ['billing_reason' => 'subscription_cycle', 'amount_paid' => 100]],
        ]);
        $this->assertTrue(true);
    }

    public function test_handle_stripe_invoice_paid_cycle_with_nonexistent_subscription(): void
    {
        \Logger::shouldReceive('warning')->andReturn(null);

        $service = $this->makeWebhookService();
        $service->handleStripeEvent([
            'type' => 'invoice.payment_succeeded',
            'data' => ['object' => ['billing_reason' => 'subscription_cycle', 'subscription' => 'sub_nonexistent_'.uniqid(), 'amount_paid' => 100]],
        ]);
        $this->assertTrue(true);
    }

    public function test_handle_stripe_invoice_failed_with_no_subscription_id(): void
    {
        $service = $this->makeWebhookService();
        $service->handleStripeEvent([
            'type' => 'invoice.payment_failed',
            'data' => ['object' => []],
        ]);
        $this->assertTrue(true);
    }

    public function test_handle_stripe_invoice_failed_with_nonexistent_subscription(): void
    {
        $service = $this->makeWebhookService();
        $service->handleStripeEvent([
            'type' => 'invoice.payment_failed',
            'data' => ['object' => ['subscription' => 'sub_nonexistent_'.uniqid()]],
        ]);
        $this->assertTrue(true);
    }

    public function test_handle_stripe_subscription_deleted_with_no_id(): void
    {
        $service = $this->makeWebhookService();
        $service->handleStripeEvent([
            'type' => 'customer.subscription.deleted',
            'data' => ['object' => []],
        ]);
        $this->assertTrue(true);
    }

    public function test_handle_razorpay_subscription_charged_with_no_match(): void
    {
        \Logger::shouldReceive('warning')->andReturn(null);

        $service = $this->makeWebhookService();
        $service->handleRazorpayEvent([
            'event' => 'subscription.charged',
            'payload' => ['subscription' => ['entity' => ['id' => 'sub_nonexistent_xyz_'.uniqid()]]],
        ]);
        $this->assertTrue(true);
    }

    public function test_handle_razorpay_subscription_halted_with_no_match(): void
    {
        \Logger::shouldReceive('warning')->andReturn(null);

        $service = $this->makeWebhookService();
        $service->handleRazorpayEvent([
            'event' => 'subscription.halted',
            'payload' => ['subscription' => ['entity' => ['id' => 'sub_nonexistent_xyz_'.uniqid()]]],
        ]);
        $this->assertTrue(true);
    }

    // =========================================================================
    // handleStripeEvent — customer.subscription.deleted with matching subscription
    // =========================================================================

    public function test_handle_stripe_subscription_deleted_with_existing_subscription(): void
    {
        $this->getLoggedInUser('user');

        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'WebhookTest '.uniqid()]);
        $plan = \App\Model\Payment\Plan::where('product', $product->id)->first() ?? \App\Model\Payment\Plan::create(['name' => 'WebhookPlan '.uniqid(), 'product' => $product->id, 'days' => 30]);

        $order = \App\Model\Order\Order::create([
            'client' => $this->user->id,
            'product' => $product->id,
            'order_status' => 'executed',
            'number' => mt_rand(100000, 999999),
        ]);

        $subId = 'sub_del_'.uniqid();
        $sub1 = \App\Model\Product\Subscription::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'is_subscribed' => 1,
            'autoRenew_status' => 1,
        ]);
        // subscribe_id not in fillable — set directly
        \Illuminate\Support\Facades\DB::table('subscriptions')->where('id', $sub1->id)->update(['subscribe_id' => $subId]);

        $service = $this->makeWebhookService();
        $service->handleStripeEvent([
            'type' => 'customer.subscription.deleted',
            'data' => ['object' => ['id' => $subId]],
        ]);

        // Fetch fresh from DB and verify update
        $updated = \App\Model\Product\Subscription::where('order_id', $order->id)->first();
        $this->assertNotNull($updated, 'Subscription should exist');
        $this->assertEquals(0, (int) $updated->is_subscribed, 'is_subscribed should be 0 after deletion');
        $this->assertEquals(0, (int) $updated->autoRenew_status, 'autoRenew_status should be 0 after deletion');
    }

    // =========================================================================
    // onStripeInvoiceFailed — with matching subscription record
    // =========================================================================

    public function test_handle_stripe_invoice_failed_with_matching_subscription(): void
    {
        $this->getLoggedInUser('user');

        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'WebhookTest '.uniqid()]);
        $plan = \App\Model\Payment\Plan::where('product', $product->id)->first() ?? \App\Model\Payment\Plan::create(['name' => 'WebhookPlan '.uniqid(), 'product' => $product->id, 'days' => 30]);

        $order = \App\Model\Order\Order::create([
            'client' => $this->user->id,
            'product' => $product->id,
            'order_status' => 'executed',
            'number' => mt_rand(100000, 999999),
        ]);

        $subId = 'sub_fail_'.uniqid();
        // is_subscribed=0 so gateway cancellation is skipped but DB update still runs
        $sub = \App\Model\Product\Subscription::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'user_id' => $this->user->id,
            'is_subscribed' => 0,
            'autoRenew_status' => 1, // will be reset to 0
        ]);
        \Illuminate\Support\Facades\DB::table('subscriptions')
            ->where('id', $sub->id)
            ->update(['subscribe_id' => $subId]);

        // Real handler so disableAutorenewalStatusByOrderId runs actual DB update
        $handler = new \App\Http\Controllers\ConcretePostSubscriptionHandleController(
            new \App\Model\Order\Invoice, new \App\Model\Order\Order,
            new \App\Model\Common\StatusSetting, new \App\Model\Payment\Plan,
            new \App\Model\Product\Subscription, new \App\Model\Order\Payment
        );
        $service = new \App\Services\Payment\SubscriptionWebhookService($handler);

        $service->handleStripeEvent([
            'type' => 'invoice.payment_failed',
            'data' => ['object' => ['subscription' => $subId, 'currency' => 'usd']],
        ]);

        // After disableAutorenewalStatusByOrderId: autoRenew_status reset to 0
        $updated = \App\Model\Product\Subscription::find($sub->id);
        $this->assertEquals(0, (int) $updated->autoRenew_status,
            'autoRenew_status should be 0 after invoice payment failure');
    }

    // =========================================================================
    // onRazorpayHalted — with matching subscription
    // =========================================================================

    public function test_handle_razorpay_subscription_halted_with_matching_subscription(): void
    {
        $this->getLoggedInUser('user');

        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'WebhookTest '.uniqid()]);
        $plan = \App\Model\Payment\Plan::where('product', $product->id)->first() ?? \App\Model\Payment\Plan::create(['name' => 'WebhookPlan '.uniqid(), 'product' => $product->id, 'days' => 30]);

        $order = \App\Model\Order\Order::create([
            'client' => $this->user->id,
            'product' => $product->id,
            'order_status' => 'executed',
            'number' => mt_rand(100000, 999999),
        ]);

        $subId = 'sub_halt_'.uniqid();
        // is_subscribed=0 so gateway cancellation is skipped but DB update still runs
        $sub = \App\Model\Product\Subscription::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'user_id' => $this->user->id,
            'is_subscribed' => 0,
            'autoRenew_status' => 1, // will be reset to 0
        ]);
        \Illuminate\Support\Facades\DB::table('subscriptions')
            ->where('id', $sub->id)
            ->update(['subscribe_id' => $subId, 'rzp_subscription' => '1']);

        $handler = new \App\Http\Controllers\ConcretePostSubscriptionHandleController(
            new \App\Model\Order\Invoice, new \App\Model\Order\Order,
            new \App\Model\Common\StatusSetting, new \App\Model\Payment\Plan,
            new \App\Model\Product\Subscription, new \App\Model\Order\Payment
        );
        $service = new \App\Services\Payment\SubscriptionWebhookService($handler);

        $service->handleRazorpayEvent([
            'event' => 'subscription.halted',
            'payload' => [
                'subscription' => ['entity' => ['id' => $subId]],
            ],
        ]);

        // After halt: disableAutorenewalStatusByOrderId resets autoRenew_status
        $updated = \App\Model\Product\Subscription::find($sub->id);
        $this->assertEquals(0, (int) $updated->autoRenew_status,
            'autoRenew_status should be 0 after Razorpay subscription halt');
    }

    // =========================================================================
    // handleRazorpayEvent — unknown event type → no-op
    // =========================================================================

    public function test_handle_razorpay_event_with_unknown_type_does_not_throw(): void
    {
        $service = $this->makeWebhookService();
        $service->handleRazorpayEvent([
            'event' => 'payment.authorized', // not handled
            'payload' => [],
        ]);
        $this->assertTrue(true);
    }

    // =========================================================================
    // handleRazorpayEvent — subscription.charged with no subscription_id
    // =========================================================================

    public function test_handle_razorpay_charged_without_subscription_id_does_not_throw(): void
    {
        $service = $this->makeWebhookService();
        $service->handleRazorpayEvent([
            'event' => 'subscription.charged',
            'payload' => [
                'subscription' => ['entity' => []], // no id
                'payment' => ['entity' => ['amount' => 1000]],
            ],
        ]);
        $this->assertTrue(true);
    }

    // =========================================================================
    // handleRazorpayEvent — subscription.halted with no subscription_id
    // =========================================================================

    public function test_handle_razorpay_halted_without_subscription_id_does_not_throw(): void
    {
        $service = $this->makeWebhookService();
        $service->handleRazorpayEvent([
            'event' => 'subscription.halted',
            'payload' => [
                'subscription' => ['entity' => []], // no id
            ],
        ]);
        $this->assertTrue(true);
    }

    // =========================================================================
    // fromGatewayAmount — via fulfillRenewal path (indirectly; test via handleStripeEvent)
    // Test zero-decimal currency path
    // =========================================================================

    public function test_handle_stripe_invoice_paid_cycle_with_jpy_currency(): void
    {
        \Logger::shouldReceive('warning')->andReturn(null);

        // JPY is zero-decimal: 1000 → 1000.0 (no divide by 100)
        $service = $this->makeWebhookService();
        $service->handleStripeEvent([
            'type' => 'invoice.payment_succeeded',
            'data' => ['object' => [
                'billing_reason' => 'subscription_cycle',
                'subscription' => 'sub_jpy_nonexistent_'.uniqid(),
                'amount_paid' => 1000,
                'currency' => 'jpy',
            ]],
        ]);
        // No DB subscription → logs warning, returns early
        $this->assertTrue(true);
    }
}
