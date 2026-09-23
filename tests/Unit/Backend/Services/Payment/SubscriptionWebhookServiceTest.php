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
        \Logger::shouldReceive('exception')->andReturn(null);

        $service = $this->makeWebhookService();
        $service->handleStripeEvent([
            'type' => 'invoice.payment_succeeded',
            'data' => ['object' => ['billing_reason' => 'subscription_cycle', 'amount_paid' => 100]],
        ]);
        $this->assertTrue(true);
    }

    public function test_handle_stripe_invoice_paid_cycle_with_nonexistent_subscription(): void
    {
        \Logger::shouldReceive('exception')->andReturn(null);

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
        \Logger::shouldReceive('exception')->andReturn(null);

        $service = $this->makeWebhookService();
        $service->handleRazorpayEvent([
            'event' => 'subscription.charged',
            'payload' => ['subscription' => ['entity' => ['id' => 'sub_nonexistent_xyz_'.uniqid()]]],
        ]);
        $this->assertTrue(true);
    }

    public function test_handle_razorpay_subscription_halted_with_no_match(): void
    {
        \Logger::shouldReceive('exception')->andReturn(null);

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
            'user_id' => $this->user->id,
            'is_subscribed' => 1,
            'autoRenew_status' => 1,
        ]);
        // subscribe_id not in fillable — set directly
        \Illuminate\Support\Facades\DB::table('subscriptions')->where('id', $sub1->id)->update(['subscribe_id' => $subId]);

        // Real handler — customer.subscription.deleted now also notifies (via
        // sendFailedPayment), so a bare Mockery mock with no expectations
        // can no longer stand in for it.
        $handler = new \App\Http\Controllers\ConcretePostSubscriptionHandleController(
            new \App\Model\Order\Invoice, new \App\Model\Order\Order,
            new \App\Model\Common\StatusSetting, new \App\Model\Payment\Plan,
            new \App\Model\Product\Subscription, new \App\Model\Order\Payment
        );
        $service = new \App\Services\Payment\SubscriptionWebhookService($handler);

        $service->handleStripeEvent([
            'type' => 'customer.subscription.deleted',
            'data' => ['object' => ['id' => $subId]],
        ]);

        // Fetch fresh from DB and verify update
        $updated = \App\Model\Product\Subscription::where('order_id', $order->id)->first();
        $this->assertNotNull($updated, 'Subscription should exist');
        $this->assertEquals(0, (int) $updated->is_subscribed, 'is_subscribed should be 0 after deletion');
        $this->assertEquals(0, (int) $updated->autoRenew_status, 'autoRenew_status should be 0 after deletion');
        $this->assertSame('', (string) $updated->subscribe_id, 'subscribe_id should be cleared after deletion');
    }

    // =========================================================================
    // onStripeInvoiceFailed — a single failed attempt must NOT deactivate
    // =========================================================================

    public function test_handle_stripe_invoice_failed_leaves_subscription_untouched(): void
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
        $sub = \App\Model\Product\Subscription::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'user_id' => $this->user->id,
            'is_subscribed' => 1,
            'autoRenew_status' => 1,
        ]);
        \Illuminate\Support\Facades\DB::table('subscriptions')
            ->where('id', $sub->id)
            ->update(['subscribe_id' => $subId]);

        // A single failed attempt isn't terminal — Stripe's own Smart Retries
        // may still recover it over the next few days, so this event must
        // leave subscription state alone (only customer.subscription.deleted
        // should deactivate). Bare mock is enough: no handler method should
        // be called at all.
        $service = $this->makeWebhookService();
        $service->handleStripeEvent([
            'type' => 'invoice.payment_failed',
            'data' => ['object' => ['subscription' => $subId, 'currency' => 'usd']],
        ]);

        $updated = \App\Model\Product\Subscription::find($sub->id);
        $this->assertEquals(1, (int) $updated->autoRenew_status,
            'autoRenew_status should be untouched after a single failed attempt — Stripe may still retry');
        $this->assertEquals(1, (int) $updated->is_subscribed,
            'is_subscribed should be untouched after a single failed attempt');
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
    // onRazorpayHalted — must NOT cancel at the gateway (halted is recoverable
    // if the customer updates their card — Razorpay owns that recovery)
    // =========================================================================

    public function test_handle_razorpay_halted_does_not_cancel_at_gateway(): void
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

        $subId = 'sub_halt_live_'.uniqid();
        $sub = \App\Model\Product\Subscription::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'user_id' => $this->user->id,
            'is_subscribed' => 1,
            'autoRenew_status' => 0,
        ]);
        \Illuminate\Support\Facades\DB::table('subscriptions')
            ->where('id', $sub->id)
            ->update(['subscribe_id' => $subId, 'rzp_subscription' => '1']);

        $this->mock(\App\Services\Payment\SubscriptionService::class, function ($mock): void {
            $mock->shouldNotReceive('cancelSubscription');
        });

        $handler = new \App\Http\Controllers\ConcretePostSubscriptionHandleController(
            new \App\Model\Order\Invoice, new \App\Model\Order\Order,
            new \App\Model\Common\StatusSetting, new \App\Model\Payment\Plan,
            new \App\Model\Product\Subscription, new \App\Model\Order\Payment
        );
        $service = new \App\Services\Payment\SubscriptionWebhookService($handler);

        $service->handleRazorpayEvent([
            'event' => 'subscription.halted',
            'payload' => ['subscription' => ['entity' => ['id' => $subId]]],
        ]);

        $updated = \App\Model\Product\Subscription::find($sub->id);
        $this->assertEquals(0, (int) $updated->is_subscribed, 'is_subscribed should still be reset locally');
        $this->assertSame($subId, (string) $updated->subscribe_id,
            'subscribe_id must survive a halt — Razorpay can still recover it, and a later subscription.charged for this id is matched by subscribe_id');
    }

    // =========================================================================
    // onRazorpayHalted — a later recovery charge must still be fulfilled
    // =========================================================================

    public function test_razorpay_subscription_recovers_after_halt_and_still_gets_fulfilled(): void
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

        // A pending, is_renewed=1 invoice already linked to this order is
        // findOrCreateRenewalInvoice()'s shortcut — reused as-is instead of
        // going through full invoice generation, which needs unrelated setup
        // (tax config, numbering) this test isn't about.
        $pendingRenewalInvoice = \App\Model\Order\Invoice::create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'is_renewed' => 1,
            'grand_total' => 100,
            'currency' => 'USD',
        ]);
        \Illuminate\Support\Facades\DB::table('order_invoice_relations')->insert([
            'order_id' => $order->id, 'invoice_id' => $pendingRenewalInvoice->id,
        ]);
        \Illuminate\Support\Facades\DB::table('invoice_items')->insert([
            'invoice_id' => $pendingRenewalInvoice->id, 'product_id' => $product->id, 'agents' => 1,
        ]);

        $subId = 'sub_recover_'.uniqid();
        $sub = \App\Model\Product\Subscription::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'user_id' => $this->user->id,
            'is_subscribed' => 1,
            'autoRenew_status' => 0,
        ]);
        \Illuminate\Support\Facades\DB::table('subscriptions')
            ->where('id', $sub->id)
            ->update(['subscribe_id' => $subId, 'rzp_subscription' => '1']);

        $this->mock(\App\Services\Payment\SubscriptionService::class, function ($mock): void {
            $mock->shouldNotReceive('cancelSubscription');
        });

        $handler = new \App\Http\Controllers\ConcretePostSubscriptionHandleController(
            new \App\Model\Order\Invoice, new \App\Model\Order\Order,
            new \App\Model\Common\StatusSetting, new \App\Model\Payment\Plan,
            new \App\Model\Product\Subscription, new \App\Model\Order\Payment
        );
        $service = new \App\Services\Payment\SubscriptionWebhookService($handler);

        // First, the halt.
        $service->handleRazorpayEvent([
            'event' => 'subscription.halted',
            'payload' => ['subscription' => ['entity' => ['id' => $subId]]],
        ]);

        // Then, Razorpay's own recovery: the customer updated their card and
        // the subscription successfully charged again.
        $service->handleRazorpayEvent([
            'event' => 'subscription.charged',
            'payload' => [
                'subscription' => ['entity' => ['id' => $subId]],
                'payment' => ['entity' => ['id' => 'pay_recover_'.uniqid(), 'amount' => 10000]],
            ],
        ]);

        // Proves the bug is actually fixed: the halt didn't erase subscribe_id,
        // so this later charged event still matched the subscription and got
        // fulfilled — the pending renewal invoice is now paid.
        $this->assertEqualsIgnoringCase('success', $pendingRenewalInvoice->refresh()->status,
            'the recovered charge must still be found and fulfilled after a halt');
    }

    // =========================================================================
    // handleRazorpayEvent — subscription.pending → logged only, no state change
    // =========================================================================

    public function test_handle_razorpay_pending_leaves_subscription_untouched(): void
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

        $subId = 'sub_pending_'.uniqid();
        $sub = \App\Model\Product\Subscription::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'user_id' => $this->user->id,
            'is_subscribed' => 1,
            'autoRenew_status' => 0,
        ]);
        \Illuminate\Support\Facades\DB::table('subscriptions')
            ->where('id', $sub->id)
            ->update(['subscribe_id' => $subId, 'rzp_subscription' => '1']);

        // Bare mock is enough — pending is too early to act on, only logged;
        // no handler method should be called at all.
        $service = $this->makeWebhookService();
        $service->handleRazorpayEvent([
            'event' => 'subscription.pending',
            'payload' => ['subscription' => ['entity' => ['id' => $subId]]],
        ]);

        $updated = \App\Model\Product\Subscription::find($sub->id);
        $this->assertEquals(1, (int) $updated->is_subscribed, 'is_subscribed should be untouched while Razorpay is still retrying');
        $this->assertSame($subId, (string) $updated->subscribe_id, 'subscribe_id should be untouched while Razorpay is still retrying');
    }

    public function test_handle_razorpay_pending_without_subscription_id_does_not_throw(): void
    {
        $service = $this->makeWebhookService();
        $service->handleRazorpayEvent([
            'event' => 'subscription.pending',
            'payload' => ['subscription' => ['entity' => []]],
        ]);
        $this->assertTrue(true);
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
        \Logger::shouldReceive('exception')->andReturn(null);

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

    // =========================================================================
    // Duplicate webhook delivery — regression tests
    //
    // Stripe/Razorpay both explicitly guarantee only at-least-once webhook
    // delivery, so the exact same renewal event can be redelivered later.
    // Before claimEvent() existed, a redelivery re-ran fulfillRenewal() in
    // full: a second invoice, a second payment, and the subscription's dates
    // extended a second time for a single real charge.
    // =========================================================================

    public function test_duplicate_stripe_event_id_only_fulfils_renewal_once(): void
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

        $subId = 'sub_dup_'.uniqid();
        $sub = \App\Model\Product\Subscription::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'user_id' => $this->user->id,
            'is_subscribed' => 1,
            'autoRenew_status' => 3,
        ]);
        \Illuminate\Support\Facades\DB::table('subscriptions')->where('id', $sub->id)->update(['subscribe_id' => $subId]);

        $handler = Mockery::mock(ConcretePostSubscriptionHandleController::class);
        $handler->shouldReceive('successRenew')->once()->andReturn(1);
        $handler->shouldReceive('recordPayment')->once();
        $handler->shouldReceive('sendPaymentSuccessMail')->zeroOrMoreTimes();
        $handler->shouldReceive('PaymentSuccessMailtoAdmin')->zeroOrMoreTimes();

        $service = new SubscriptionWebhookService($handler);

        $event = [
            'id' => 'evt_dup_'.uniqid(),
            'type' => 'invoice.payment_succeeded',
            'data' => ['object' => [
                'billing_reason' => 'subscription_cycle',
                'subscription' => $subId,
                'amount_paid' => 1999,
                'currency' => 'usd',
            ]],
        ];

        $service->handleStripeEvent($event);
        $service->handleStripeEvent($event); // same event id — must be a no-op

        // The ->once() expectations above (verified by Mockery::close() in
        // tearDown) are the real assertion here.
        $this->assertTrue(true);
    }

    public function test_duplicate_razorpay_payment_id_only_fulfils_renewal_once(): void
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

        $subId = 'sub_rzp_dup_'.uniqid();
        $sub = \App\Model\Product\Subscription::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'user_id' => $this->user->id,
            'is_subscribed' => 1,
            'rzp_subscription' => 3,
        ]);
        \Illuminate\Support\Facades\DB::table('subscriptions')->where('id', $sub->id)->update(['subscribe_id' => $subId]);

        $handler = Mockery::mock(ConcretePostSubscriptionHandleController::class);
        $handler->shouldReceive('successRenew')->once()->andReturn(1);
        $handler->shouldReceive('recordPayment')->once();
        $handler->shouldReceive('sendPaymentSuccessMail')->zeroOrMoreTimes();
        $handler->shouldReceive('PaymentSuccessMailtoAdmin')->zeroOrMoreTimes();

        $service = new SubscriptionWebhookService($handler);

        $event = [
            'event' => 'subscription.charged',
            'payload' => [
                'subscription' => ['entity' => ['id' => $subId]],
                'payment' => ['entity' => ['id' => 'pay_dup_'.uniqid(), 'amount' => 1999]],
            ],
        ];

        $service->handleRazorpayEvent($event);
        $service->handleRazorpayEvent($event); // same payment id — must be a no-op

        $this->assertTrue(true);
    }
}
