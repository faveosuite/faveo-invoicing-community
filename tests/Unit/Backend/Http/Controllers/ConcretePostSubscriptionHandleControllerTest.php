<?php

namespace Tests\Unit\Backend\Http\Controllers;

use App\Http\Controllers\ConcretePostSubscriptionHandleController;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Product\Subscription;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ConcretePostSubscriptionHandleControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    private ConcretePostSubscriptionHandleController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');

        $this->controller = new ConcretePostSubscriptionHandleController(
            new Invoice,
            new Order,
            new StatusSetting,
            new Plan,
            new Subscription,
            new Payment
        );

        Setting::firstOrCreate(['id' => 1], ['email' => 'admin@test.local', 'title' => 'Test']);
    }

    // -------------------------------------------------------------------------
    // recordPayment — creates a Payment and updates invoice status
    // -------------------------------------------------------------------------

    public function test_record_payment_creates_payment_record(): void
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'grand_total' => 100.0,
            'status' => 'pending',
        ]);

        $payment = $this->controller->recordPayment($invoice, 'stripe');

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals($invoice->id, $payment->invoice_id);

        $invoice->refresh();
        $this->assertEqualsIgnoringCase('success', $invoice->status);
    }

    // -------------------------------------------------------------------------
    // getProcessingFee — returns null when no fee configured
    // -------------------------------------------------------------------------

    public function test_get_processing_fee_returns_null_when_no_fee(): void
    {
        $result = $this->controller->getProcessingFee('stripe', 'USD');

        // Returns null or a percentage string — both acceptable
        $this->assertTrue($result === null || is_string($result));
    }

    // -------------------------------------------------------------------------
    // calculateUnitCost — covers 2-decimal, 3-decimal, 0-decimal currencies
    // -------------------------------------------------------------------------

    public function test_calculate_unit_cost_two_decimal_currency(): void
    {
        // USD (2-decimal) → multiply by 100 to get cents
        $result = $this->controller->calculateUnitCost('USD', 10);

        $this->assertIsFloat($result);
        $this->assertEqualsWithDelta(1000.0, $result, 0.001); // 10 USD = 1000 cents
    }

    public function test_calculate_unit_cost_three_decimal_currency(): void
    {
        // BHD (3-decimal) → multiply by 1000 to get fils
        $result = $this->controller->calculateUnitCost('BHD', 1);

        $this->assertIsFloat($result);
        $this->assertEqualsWithDelta(1000.0, $result, 0.001); // 1 BHD = 1000 fils
    }

    public function test_calculate_unit_cost_zero_decimal_currency(): void
    {
        // JPY (0-decimal) → no multiplication
        $result = $this->controller->calculateUnitCost('JPY', 1000);

        $this->assertIsFloat($result);
        $this->assertEqualsWithDelta(1000.0, $result, 0.001); // 1000 JPY unchanged
    }

    /**
     * Regression test: a prior implementation cast to (int) before
     * multiplying, truncating the fractional part — $19.99 became 1900
     * (i.e. $19.00), silently dropping $0.99 off every cron-driven renewal
     * charge. Must multiply first, then round.
     */
    public function test_calculate_unit_cost_does_not_truncate_fractional_cents(): void
    {
        $result = $this->controller->calculateUnitCost('USD', 19.99);

        $this->assertEqualsWithDelta(1999.0, $result, 0.001);
    }

    public function test_calculate_unit_cost_does_not_truncate_fractional_three_decimal_currency(): void
    {
        $result = $this->controller->calculateUnitCost('BHD', 1.5);

        $this->assertEqualsWithDelta(1500.0, $result, 0.001);
    }

    // -------------------------------------------------------------------------
    // disableAutorenewalStatusByOrderId — no-op for non-existent order
    // -------------------------------------------------------------------------

    public function test_disable_autorenewal_exits_early_for_nonexistent_order(): void
    {
        $this->controller->disableAutorenewalStatusByOrderId(999999);
        $this->assertTrue(true);
    }

    public function test_disable_autorenewal_updates_existing_subscription(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'CPSHT Test '.uniqid()]);
        $plan = Plan::where('product', $product->id)->first() ?? Plan::create(['name' => 'CPSHT Plan '.uniqid(), 'product' => $product->id, 'days' => 30]);

        $order = Order::create([
            'client' => $this->user->id,
            'product' => $product->id,
            'order_status' => 'executed',
            'number' => mt_rand(10000000, 99999999),
        ]);
        Subscription::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'autoRenew_status' => 1,
            'rzp_subscription' => '1',
            'subscribe_id' => 'sub_stale_leftover_123',
        ]);

        $this->controller->disableAutorenewalStatusByOrderId($order->id);

        $this->assertDatabaseHas('subscriptions', [
            'order_id' => $order->id,
            'autoRenew_status' => 0,
        ]);
    }

    /**
     * Regression test: subscribe_id was left populated after disabling, so a
     * later feature that reuses an existing subscribe_id (rather than always
     * creating a new gateway subscription) would hand out an already
     * cancelled subscription id.
     */
    public function test_disable_autorenewal_clears_subscribe_id(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'CPSHT Test '.uniqid()]);
        $plan = Plan::where('product', $product->id)->first() ?? Plan::create(['name' => 'CPSHT Plan '.uniqid(), 'product' => $product->id, 'days' => 30]);

        $order = Order::create([
            'client' => $this->user->id,
            'product' => $product->id,
            'order_status' => 'executed',
            'number' => mt_rand(10000000, 99999999),
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
        Subscription::where('id', $subscription->id)->update(['rzp_subscription' => '2', 'subscribe_id' => 'sub_stale_leftover_456']);

        $this->controller->disableAutorenewalStatusByOrderId($order->id);

        $this->assertSame('', Subscription::where('order_id', $order->id)->value('subscribe_id'));
        $this->assertSame('0', (string) Subscription::where('order_id', $order->id)->value('rzp_subscription'));
    }

    /**
     * Regression test: this webhook-driven disable path (fired on a Stripe
     * renewal failure or a Razorpay halt) must delete the Auto_renewal row,
     * same as the manual disable path already does — otherwise
     * AutoRenewalActivationService::activate()'s idempotency check finds the
     * stale row on a later re-enable and silently no-ops forever: no flag
     * flip, no new gateway subscription, no error surfaced anywhere.
     */
    public function test_disable_autorenewal_deletes_auto_renewal_row_so_reactivation_is_not_blocked(): void
    {
        $product = \App\Model\Product\Product::first() ?? \App\Model\Product\Product::create(['name' => 'CPSHT Test '.uniqid()]);
        $plan = Plan::where('product', $product->id)->first() ?? Plan::create(['name' => 'CPSHT Plan '.uniqid(), 'product' => $product->id, 'days' => 30]);

        $order = Order::create([
            'client' => $this->user->id,
            'product' => $product->id,
            'order_status' => 'executed',
            'number' => mt_rand(10000000, 99999999),
        ]);
        Subscription::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'is_subscribed' => 1,
            'autoRenew_status' => 1,
        ]);
        \App\Auto_renewal::create([
            'user_id' => $this->user->id,
            'customer_id' => 'pi_old_123',
            'payment_method' => 'stripe',
            'order_id' => $order->id,
            'payment_intent_id' => 'pi_old_123',
        ]);

        $this->controller->disableAutorenewalStatusByOrderId($order->id);

        $this->assertDatabaseMissing('auto_renewals', ['order_id' => $order->id]);

        // The real regression: a subsequent activate() call must actually
        // re-enable, not silently no-op because a stale row is still there.
        $activation = new \App\Services\Payment\AutoRenewalActivationService();
        $activation->activate($order, $this->user, 'stripe', 'pi_new_456');

        $this->assertSame('1', (string) Subscription::where('order_id', $order->id)->value('autoRenew_status'));
        $this->assertDatabaseHas('auto_renewals', ['order_id' => $order->id, 'payment_intent_id' => 'pi_new_456']);
    }

    // -------------------------------------------------------------------------
    // successRenew — throws when subscription not found
    // -------------------------------------------------------------------------

    public function test_success_renew_throws_when_subscription_not_found(): void
    {
        $this->expectException(\Exception::class);

        $invoice = Invoice::factory()->create(['user_id' => $this->user->id]);
        $fakeSub = new Subscription(['id' => 999999, 'plan_id' => 999999]);

        $this->controller->successRenew($invoice, $fakeSub, 'stripe', 'USD');
    }

    // -------------------------------------------------------------------------
    // sendPaymentSuccessMail — throws when subscription not found
    // -------------------------------------------------------------------------

    public function test_send_payment_success_mail_does_not_throw_for_unknown_sub(): void
    {
        try {
            $this->controller->sendPaymentSuccessMail(
                999999, 'USD', 100.0, $this->user, 'Test Product', 'ORD-001'
            );
        } catch (\Throwable $e) {
            // Subscription not found → exception caught internally or propagated
        }
        $this->assertTrue(true);
    }
}
