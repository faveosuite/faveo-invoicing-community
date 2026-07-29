<?php

namespace Tests\Unit\Backend\Services\Payment;

use App\Auto_renewal;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Subscription;
use App\Services\Payment\AutoRenewalActivationService;
use App\User;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\DBTestCase;

class AutoRenewalActivationServiceTest extends DBTestCase
{
    use DatabaseTransactions;

    private AutoRenewalActivationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AutoRenewalActivationService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_activate_creates_auto_renewal_and_flips_stripe_flag(): void
    {
        /** @var Order $order */
        $order = Order::factory()->withRelations()->create();
        $user = User::factory()->create();

        $this->service->activate($order, $user, 'stripe', 'pi_test_123');

        $this->assertDatabaseHas('auto_renewals', [
            'order_id' => $order->id,
            'user_id' => $user->id,
            'payment_method' => 'stripe',
            'payment_intent_id' => 'pi_test_123',
        ]);

        $subscription = Subscription::where('order_id', $order->id)->first();
        $this->assertSame('1', (string) $subscription->is_subscribed);
        $this->assertSame('1', (string) $subscription->autoRenew_status);
    }

    public function test_activate_flips_razorpay_flag_not_stripe_flag(): void
    {
        /** @var Order $order */
        $order = Order::factory()->withRelations()->create();
        $user = User::factory()->create();

        // activate() triggers immediate Razorpay subscription creation as a
        // side effect — mock it out so this test doesn't hit the live
        // Razorpay API (that behavior has its own dedicated tests below).
        // Both methods must be stubbed: razorpayRecurringUnitCost() calls
        // calculateRenewalCost() before handleRazorpaySubscription() — leaving
        // it unstubbed throws BadMethodCallException, which
        // createRazorpaySubscriptionNow() silently swallows, meaning
        // handleRazorpaySubscription() below would never actually be reached
        // even though this test would still (wrongly) pass.
        $mockController = Mockery::mock(SubscriptionController::class);
        $mockController->shouldReceive('calculateRenewalCost')->andReturn(10.0);
        $mockController->shouldReceive('handleRazorpaySubscription')->once()->andReturnNull();
        $this->app->instance(SubscriptionController::class, $mockController);

        $this->service->activate($order, $user, 'razorpay', 'invoice_test_123');

        $subscription = Subscription::where('order_id', $order->id)->first();
        $this->assertSame('1', (string) $subscription->is_subscribed);
        $this->assertSame('1', (string) $subscription->rzp_subscription);
        $this->assertNotSame('1', (string) $subscription->autoRenew_status);
    }

    public function test_activate_triggers_immediate_razorpay_subscription_creation(): void
    {
        /** @var Order $order */
        $order = Order::factory()->withRelations()->create();
        $user = User::factory()->create();

        $mockController = Mockery::mock(SubscriptionController::class);
        $mockController->shouldReceive('calculateRenewalCost')->andReturn(10.0);
        $mockController->shouldReceive('handleRazorpaySubscription')->once();
        $this->app->instance(SubscriptionController::class, $mockController);

        $this->service->activate($order, $user, 'razorpay', 'pay_test_123');

        // The mock's ->once() expectation (verified via Mockery::close() in
        // tearDown) is the real check here; this just keeps PHPUnit from
        // flagging the test as risky for having no assertions of its own.
        $this->assertSame('1', (string) Subscription::where('order_id', $order->id)->value('rzp_subscription'));
    }

    public function test_activate_does_not_trigger_subscription_creation_for_stripe(): void
    {
        /** @var Order $order */
        $order = Order::factory()->withRelations()->create();
        $user = User::factory()->create();

        $mockController = Mockery::mock(SubscriptionController::class);
        $mockController->shouldNotReceive('handleRazorpaySubscription');
        $this->app->instance(SubscriptionController::class, $mockController);

        $this->service->activate($order, $user, 'stripe', 'pi_test_123');

        $this->assertSame('1', (string) Subscription::where('order_id', $order->id)->value('autoRenew_status'));
    }

    /**
     * Proves the actual DB-level guarantee activate()'s race-safety depends
     * on: two rows for the same (order_id, payment_method) can no longer
     * coexist at all, regardless of what any application code does. Without
     * this constraint, two near-simultaneous triggers for the same
     * order+gateway (a redirect-confirm racing a webhook) could both pass a
     * plain exists() check before either insert landed — duplicate rows were
     * found in production before this migration existed. A true concurrent
     * race isn't practical to reproduce in a single-process test; this
     * verifies the schema-level guarantee that makes claimActivation()'s
     * firstOrCreate() + catch (QueryException) pattern actually safe.
     */
    public function test_auto_renewals_table_rejects_duplicate_order_and_gateway(): void
    {
        /** @var Order $order */
        $order = Order::factory()->withRelations()->create();
        $user = User::factory()->create();

        Auto_renewal::create([
            'user_id' => $user->id, 'customer_id' => 'pi_first', 'payment_method' => 'stripe',
            'order_id' => $order->id, 'payment_intent_id' => 'pi_first',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Auto_renewal::create([
            'user_id' => $user->id, 'customer_id' => 'pi_second', 'payment_method' => 'stripe',
            'order_id' => $order->id, 'payment_intent_id' => 'pi_second',
        ]);
    }

    public function test_activate_is_idempotent_for_repeat_calls(): void
    {
        /** @var Order $order */
        $order = Order::factory()->withRelations()->create();
        $user = User::factory()->create();

        $this->service->activate($order, $user, 'stripe', 'pi_first');
        $this->service->activate($order, $user, 'stripe', 'pi_second');

        // Second call is a no-op — still only the first reference is recorded.
        $this->assertSame(1, Auto_renewal::where('order_id', $order->id)->where('payment_method', 'stripe')->count());
        $this->assertDatabaseHas('auto_renewals', ['order_id' => $order->id, 'payment_intent_id' => 'pi_first']);
    }

    public function test_prepare_razorpay_subscription_creates_new_when_none_exists(): void
    {
        /** @var Order $order */
        $order = Order::factory()->withRelations()->create();
        $user = User::factory()->create();

        $mockController = Mockery::mock(\App\Http\Controllers\RazorpayController::class);
        $mockController->shouldReceive('handleRzpAutoPay')->once()
            ->andReturn(new \App\Plugins\Payment\Dto\SubscriptionResult('Razorpay', 'sub_new_123', 'created'));
        $this->app->instance(\App\Http\Controllers\RazorpayController::class, $mockController);

        $config = $this->service->prepareRazorpaySubscriptionForAuthorization($order, $user);

        $this->assertSame('sub_new_123', $config['subscription_id']);
        $this->assertDatabaseHas('subscriptions', [
            'order_id' => $order->id,
            'subscribe_id' => 'sub_new_123',
            'rzp_subscription' => '2',
        ]);
    }

    /**
     * Regression test: an earlier version reused an existing not-yet-
     * authorized subscription instead of creating a fresh one on every call.
     * That "optimization" caused two real, separate bugs in practice — it
     * handed back a subscription Razorpay had already cancelled, and (worse)
     * it kept handing back a subscription whose price had gone stale after a
     * pricing fix landed, since Razorpay locks a Plan's amount in at creation
     * and never rechecks it. Every call must create its own fresh
     * subscription, full stop — regardless of what's already sitting on the
     * local row.
     */
    public function test_prepare_razorpay_subscription_never_reuses_an_existing_one(): void
    {
        /** @var Order $order */
        $order = Order::factory()->withRelations()->create();
        $user = User::factory()->create();
        Subscription::where('order_id', $order->id)->update(['subscribe_id' => 'sub_old_stale_123', 'rzp_subscription' => '2']);

        $mockController = Mockery::mock(\App\Http\Controllers\RazorpayController::class);
        $mockController->shouldReceive('handleRzpAutoPay')->once()
            ->andReturn(new \App\Plugins\Payment\Dto\SubscriptionResult('Razorpay', 'sub_fresh_456', 'created'));
        $this->app->instance(\App\Http\Controllers\RazorpayController::class, $mockController);

        $config = $this->service->prepareRazorpaySubscriptionForAuthorization($order, $user);

        $this->assertSame('sub_fresh_456', $config['subscription_id']);
        $this->assertDatabaseHas('subscriptions', [
            'order_id' => $order->id,
            'subscribe_id' => 'sub_fresh_456',
            'rzp_subscription' => '2',
        ]);
    }

    /**
     * Regression test: the recurring amount must include the gateway's
     * processing fee (and tax, though a fresh factory product/user defaults
     * to 0% tax so that leg isn't independently observable here) on top of
     * the base renewal price — an earlier version of this code passed the
     * bare renewal price straight through, silently under-charging every
     * future auto-renewal by the fee amount.
     */
    public function test_prepare_razorpay_subscription_includes_processing_fee_in_recurring_amount(): void
    {
        /** @var Order $order */
        $order = Order::factory()->withRelations()->create();
        $user = User::factory()->create();

        DB::table('razorpay')->update(['processing_fee' => 10]);

        $mockSubscriptionController = Mockery::mock(SubscriptionController::class);
        $mockSubscriptionController->shouldReceive('calculateRenewalCost')->andReturn(100.0);
        $this->app->instance(SubscriptionController::class, $mockSubscriptionController);

        $subscription = Subscription::where('order_id', $order->id)->first();
        $plan = Plan::find($subscription->plan_id);
        $planDetails = userCurrencyAndPrice($user->id, $plan);
        // 100 base + 10% processing fee, 0% tax (fresh product has no tax rule)
        $expectedUnitCost = calculateUnitCost($planDetails['currency'], 110.0);

        $capturedCost = null;
        $mockRazorpayController = Mockery::mock(\App\Http\Controllers\RazorpayController::class);
        $mockRazorpayController->shouldReceive('handleRzpAutoPay')
            ->once()
            ->withArgs(function ($cost) use (&$capturedCost) {
                $capturedCost = $cost;

                return true;
            })
            ->andReturn(new \App\Plugins\Payment\Dto\SubscriptionResult('Razorpay', 'sub_fee_test', 'created'));
        $this->app->instance(\App\Http\Controllers\RazorpayController::class, $mockRazorpayController);

        $this->service->prepareRazorpaySubscriptionForAuthorization($order, $user);

        $this->assertSame($expectedUnitCost, $capturedCost);
    }
}
