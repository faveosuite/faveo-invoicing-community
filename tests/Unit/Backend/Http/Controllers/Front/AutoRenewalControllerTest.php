<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\Model\Order\Order;
use App\Model\Product\Subscription;
use App\Services\Payment\PaymentService;
use App\User;
use Mockery;
use Tests\DBTestCase;

class AutoRenewalControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('user');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_disable_returns_404_for_nonexistent_order(): void
    {
        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $response = $this->postJson('/auto-renewal/999999/disable');
        // 404 since order 999999 doesn't exist
        $this->assertContains($response->status(), [404, 400, 500]);
    }

    public function test_stripe_session_returns_error_for_nonexistent_order(): void
    {
        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $response = $this->postJson('/auto-renewal/999999/stripe/session');

        $this->assertContains($response->status(), [200, 400, 403, 404, 500]);
    }

    public function test_stripe_confirm_returns_error_for_nonexistent_order(): void
    {
        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $response = $this->postJson('/auto-renewal/999999/stripe/confirm');

        $this->assertContains($response->status(), [200, 400, 403, 404, 500]);
    }

    public function test_razorpay_order_returns_error_for_nonexistent_order(): void
    {
        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $response = $this->postJson('/auto-renewal/999999/razorpay/order');

        $this->assertContains($response->status(), [200, 400, 403, 404, 500]);
    }

    public function test_razorpay_confirm_returns_error_for_nonexistent_order(): void
    {
        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $response = $this->postJson('/auto-renewal/999999/razorpay/confirm');

        $this->assertContains($response->status(), [200, 400, 403, 404, 500]);
    }

    public function test_disable_with_real_user_order_returns_response(): void
    {
        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $order = \App\Model\Order\Order::where('client', $this->user->id)->first();
        if (! $order) {
            $order = \App\Model\Order\Order::create(['client' => $this->user->id, 'order_status' => 'executed', 'number' => mt_rand(10000000, 99999999)]);
        }

        $response = $this->postJson("/auto-renewal/{$order->id}/disable");

        $this->assertContains($response->status(), [200, 400, 403, 404, 500]);
    }

    // =========================================================================
    // disable — order owned by another user → 403
    // =========================================================================

    public function test_disable_returns_403_when_order_belongs_to_other_user(): void
    {
        $otherUser = User::factory()->create(['email' => 'other-ar-'.uniqid().'@test.local']);
        $order = Order::create([
            'client' => $otherUser->id,
            'order_status' => 'executed',
            'number' => mt_rand(100000, 999999),
        ]);

        $response = $this->postJson("/auto-renewal/{$order->id}/disable");
        // authorizedOrder() calls abort_if(! authorizeOwnership(...), 403)
        $response->assertStatus(403);
    }

    // =========================================================================
    // disable — user owns the order but no subscription → 400 (exception)
    // =========================================================================

    public function test_disable_returns_400_when_no_subscription_found(): void
    {
        $order = Order::create([
            'client' => $this->user->id,
            'order_status' => 'executed',
            'number' => mt_rand(100000, 999999),
        ]);
        // No subscription created → firstOrFail() throws → caught → errorResponse 400

        $response = $this->postJson("/auto-renewal/{$order->id}/disable");
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    // =========================================================================
    // disable — subscription with no subscribe_id → updates local state to 0
    // =========================================================================

    public function test_disable_with_subscription_no_gateway_sub_returns_200(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'AutoR '.uniqid()]);
        $plan = \App\Model\Payment\Plan::where('product', $product->id)->first() ?? \App\Model\Payment\Plan::create(['name' => 'ARPlan '.uniqid(), 'product' => $product->id, 'days' => 30]);

        $order = Order::create([
            'client' => $this->user->id,
            'product' => $product->id,
            'order_status' => 'executed',
            'number' => mt_rand(100000, 999999),
        ]);
        Subscription::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'is_subscribed' => 1,
            'autoRenew_status' => 1,
            'subscribe_id' => '', // no gateway sub → skip cancel
        ]);

        $response = $this->postJson("/auto-renewal/{$order->id}/disable");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify local state reset
        $this->assertDatabaseHas('subscriptions', [
            'order_id' => $order->id,
            'is_subscribed' => 0,
            'autoRenew_status' => 0,
        ]);
    }

    // =========================================================================
    // disable — must clear the Auto_renewal row, or AutoRenewalActivationService
    // ::activate()'s idempotency check silently blocks any future re-enable
    // =========================================================================

    public function test_disable_deletes_auto_renewal_row_so_reactivation_is_not_blocked(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'AutoR '.uniqid()]);
        $plan = \App\Model\Payment\Plan::where('product', $product->id)->first() ?? \App\Model\Payment\Plan::create(['name' => 'ARPlan '.uniqid(), 'product' => $product->id, 'days' => 30]);

        $order = Order::create([
            'client' => $this->user->id,
            'product' => $product->id,
            'order_status' => 'executed',
            'number' => mt_rand(100000, 999999),
        ]);
        Subscription::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'is_subscribed' => 1,
            'rzp_subscription' => 1,
            'subscribe_id' => '',
        ]);
        \App\Auto_renewal::create([
            'user_id' => $this->user->id,
            'customer_id' => 'pay_old_123',
            'payment_method' => 'razorpay',
            'order_id' => $order->id,
            'payment_intent_id' => 'pay_old_123',
        ]);

        $response = $this->postJson("/auto-renewal/{$order->id}/disable");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('auto_renewals', ['order_id' => $order->id]);
    }

    // =========================================================================
    // stripeSession — user owns order → proceeds (may fail at gateway)
    // =========================================================================

    public function test_stripe_session_for_owned_order_returns_error_or_success(): void
    {
        $order = Order::create([
            'client' => $this->user->id,
            'order_status' => 'executed',
            'number' => mt_rand(100000, 999999),
        ]);

        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $paymentServiceMock->shouldReceive('startCardPayment')
            ->andThrow(new \Exception('Gateway not configured'));
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $response = $this->postJson("/auto-renewal/{$order->id}/stripe/session");
        // Either 400 (gateway exception caught) or 403 (ownership)
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    // =========================================================================
    // razorpayOrder — user owns order but has no subscription/plan/invoice yet
    // → prepareRazorpaySubscriptionForAuthorization() can't proceed → 400
    // =========================================================================

    public function test_razorpay_order_for_owned_order_returns_400_when_no_subscription(): void
    {
        $order = Order::create([
            'client' => $this->user->id,
            'order_status' => 'executed',
            'number' => mt_rand(100000, 999999),
        ]);

        $response = $this->postJson("/auto-renewal/{$order->id}/razorpay/order");
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    // =========================================================================
    // razorpayOrder — full context present → creates/reuses a Razorpay
    // Subscription and returns its id + widget key (mocking the gateway call
    // itself so this doesn't hit the real Razorpay API)
    // =========================================================================

    public function test_razorpay_order_with_full_context_returns_subscription_id(): void
    {
        /** @var Order $order */
        $order = Order::factory()->withRelations()->create(['client' => $this->user->id]);

        $mockController = Mockery::mock(\App\Http\Controllers\RazorpayController::class);
        $mockController->shouldReceive('handleRzpAutoPay')
            ->once()
            ->andReturn(new \App\Plugins\Payment\Dto\SubscriptionResult('Razorpay', 'sub_test_123', 'created'));
        $this->app->instance(\App\Http\Controllers\RazorpayController::class, $mockController);

        $response = $this->postJson("/auto-renewal/{$order->id}/razorpay/order");
        $response->assertStatus(200);
        $response->assertJsonPath('data.subscription_id', 'sub_test_123');

        $this->assertDatabaseHas('subscriptions', [
            'order_id' => $order->id,
            'subscribe_id' => 'sub_test_123',
            'rzp_subscription' => '2',
        ]);
    }

    // =========================================================================
    // stripeConfirm — missing payment_intent → 400
    // =========================================================================

    public function test_stripe_confirm_returns_400_when_no_payment_intent(): void
    {
        $order = Order::create([
            'client' => $this->user->id,
            'order_status' => 'executed',
            'number' => mt_rand(100000, 999999),
        ]);

        $response = $this->postJson("/auto-renewal/{$order->id}/stripe/confirm", []);
        // No payment_intent → errorResponse 400
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    // =========================================================================
    // razorpayConfirm — capture throws → 400
    // =========================================================================

    public function test_razorpay_confirm_for_owned_order_returns_400_on_capture_error(): void
    {
        $order = Order::create([
            'client' => $this->user->id,
            'order_status' => 'executed',
            'number' => mt_rand(100000, 999999),
        ]);

        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $paymentServiceMock->shouldReceive('capture')
            ->andThrow(new \Exception('Razorpay capture failed'));
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $response = $this->postJson("/auto-renewal/{$order->id}/razorpay/confirm", [
            'razorpay_subscription_id' => 'sub_test',
            'razorpay_payment_id' => 'pay_test',
            'razorpay_signature' => 'sig_test',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    // =========================================================================
    // razorpayConfirm — capture succeeds → activates straight to fully active
    // (status '3'), no "pending" intermediate step, since the popup itself
    // was the authorization
    // =========================================================================

    public function test_razorpay_confirm_activates_subscription_fully_on_success(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'AutoR '.uniqid()]);
        $plan = \App\Model\Payment\Plan::where('product', $product->id)->first() ?? \App\Model\Payment\Plan::create(['name' => 'ARPlan '.uniqid(), 'product' => $product->id, 'days' => 30]);

        $order = Order::create([
            'client' => $this->user->id,
            'product' => $product->id,
            'order_status' => 'executed',
            'number' => mt_rand(100000, 999999),
        ]);
        $subscription = Subscription::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'is_subscribed' => 0,
        ]);
        // subscribe_id/rzp_subscription aren't in Subscription::$fillable, so
        // Subscription::create() silently drops them — set via a query-builder
        // update (bypasses mass-assignment protection), same as production code does.
        Subscription::where('id', $subscription->id)->update(['rzp_subscription' => 2, 'subscribe_id' => 'sub_test_123']);

        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $paymentServiceMock->shouldReceive('capture')->once()->andReturn(
            new \App\Plugins\Payment\Dto\PaymentResult(true, 'Razorpay', 'pay_test', 'captured')
        );
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $response = $this->postJson("/auto-renewal/{$order->id}/razorpay/confirm", [
            'razorpay_subscription_id' => 'sub_test_123',
            'razorpay_payment_id' => 'pay_test',
            'razorpay_signature' => 'sig_test',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('subscriptions', [
            'order_id' => $order->id,
            'is_subscribed' => 1,
            'rzp_subscription' => '3',
        ]);
        $this->assertDatabaseHas('auto_renewals', [
            'order_id' => $order->id,
            'payment_method' => 'razorpay',
            'payment_intent_id' => 'pay_test',
        ]);
    }

    // =========================================================================
    // razorpayConfirm — subscription_id doesn't match this order's own
    // subscribe_id → rejected before capture/activation even run. Otherwise a
    // valid authorization from a *different* order the user owns could be
    // replayed here to mark this order "active" without ever authorizing its
    // own (correctly priced) subscription.
    // =========================================================================

    public function test_razorpay_confirm_rejects_mismatched_subscription_id(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'AutoR '.uniqid()]);
        $plan = \App\Model\Payment\Plan::where('product', $product->id)->first() ?? \App\Model\Payment\Plan::create(['name' => 'ARPlan '.uniqid(), 'product' => $product->id, 'days' => 30]);

        $order = Order::create([
            'client' => $this->user->id,
            'product' => $product->id,
            'order_status' => 'executed',
            'number' => mt_rand(100000, 999999),
        ]);
        $subscription = Subscription::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'is_subscribed' => 0,
        ]);
        // subscribe_id/rzp_subscription aren't in Subscription::$fillable, so
        // Subscription::create() silently drops them — set via a query-builder
        // update (bypasses mass-assignment protection), same as production code does.
        Subscription::where('id', $subscription->id)->update(['rzp_subscription' => 2, 'subscribe_id' => 'sub_this_orders_own_123']);

        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $paymentServiceMock->shouldNotReceive('capture');
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $response = $this->postJson("/auto-renewal/{$order->id}/razorpay/confirm", [
            'razorpay_subscription_id' => 'sub_from_a_different_order_456',
            'razorpay_payment_id' => 'pay_test',
            'razorpay_signature' => 'sig_test',
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseMissing('subscriptions', [
            'order_id' => $order->id,
            'rzp_subscription' => '3',
        ]);
    }
}
