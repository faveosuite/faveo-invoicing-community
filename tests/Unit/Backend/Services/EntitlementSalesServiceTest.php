<?php

namespace Tests\Unit\Backend\Services;

use App\Http\Controllers\Order\BaseRenewController;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\OrderEntitlement;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Order\Payment;
use App\Model\Order\PaymentInvoice;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Entitlement;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Services\EntitlementSalesService;
use App\Services\EntitlementService;
use App\Services\Payment\CreditBalanceService;
use App\Services\Payment\PostPaymentService;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Tests\DBTestCase;

class EntitlementSalesServiceTest extends DBTestCase
{
    private EntitlementSalesService $sales;

    private Product $product;

    private Plan $plan;

    private User $customer;

    private Order $order;

    private string $currency;

    private int $periodId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = new EntitlementSalesService;
        $this->product = Product::factory()->create();
        $this->plan = Plan::factory()->create(['product' => $this->product->id, 'days' => 30]);
        $this->periodId = (int) DB::table('periods')->insertGetId(['name' => 'Test 30 days', 'days' => '30']);
        $this->plan->periods()->attach($this->periodId);
        $this->customer = User::factory()->create(['country' => 'IN']);
        $this->currency = (string) getCurrencyForClient('IN');
        PlanPrice::factory()->create(['plan_id' => $this->plan->id, 'currency' => $this->currency, 'renew_price' => 100, 'no_of_agents' => 0]);

        $this->order = Order::factory()->create(['product' => $this->product->id, 'client' => $this->customer->id, 'order_status' => 'executed']);
        Subscription::factory()->create([
            'order_id' => $this->order->id, 'plan_id' => $this->plan->id, 'product_id' => $this->product->id,
            'user_id' => $this->customer->id, 'ends_at' => now()->addDays(15)->addHour(),
        ]);
    }

    private function item(string $feature, string $key, string $type, float $price = 2): Entitlement
    {
        $item = Entitlement::create([
            'feature' => $feature, 'key' => $key, 'label' => ucfirst($feature),
            'type' => $type, 'default_value' => $type === 'boolean' ? '0' : '10',
        ]);
        $item->prices()->create(['plan_id' => $this->plan->id, 'currency' => $this->currency, 'unit_price' => $price]);

        return $item->fresh();
    }

    public function test_quote_charges_only_the_days_left(): void
    {
        $quote = $this->sales->quote($this->order, $this->item('storage', 'gb', 'number'), 10);

        $this->assertSame(15, $quote['days_left']);
        $this->assertSame(10.0, $quote['amount']); // 2 × 10 units × 15/30 days
    }

    public function test_a_lifetime_licence_sells_extras_for_its_updates_term(): void
    {
        // One-time plan: the licence never expires (no ends_at); its term is the updates expiry.
        Subscription::where('order_id', $this->order->id)->update(['ends_at' => null, 'update_ends_at' => now()->addDays(15)->addHour()]);

        $quote = $this->sales->quote($this->order->fresh(), $this->item('storage', 'gb', 'number'), 10);

        $this->assertSame(15, $quote['days_left']);
        $this->assertSame(10.0, $quote['amount']); // 2 × 10 units × 15/30 days — not "order expired"
    }

    public function test_quote_refuses_expired_orders_free_trials_and_a_second_feature(): void
    {
        $chat = $this->item('chat', 'enabled', 'boolean');
        OrderEntitlement::create(['order_id' => $this->order->id, 'entitlement_id' => $chat->id, 'quantity' => 1, 'unit_price' => 2, 'currency' => $this->currency, 'period_id' => $this->periodId]);
        $this->assertRefused(fn () => $this->sales->quote($this->order, $chat, 1));

        Subscription::where('order_id', $this->order->id)->update(['ends_at' => now()->subDay()]);
        $this->assertRefused(fn () => $this->sales->quote($this->order, $this->item('storage', 'gb', 'number'), 1));
    }

    public function test_paid_invoice_adds_the_extra_once_and_never_creates_an_order(): void
    {
        Mail::fake();
        $storage = $this->item('storage', 'gb', 'number');
        $invoice = $this->sales->createPurchaseInvoice($this->order, $storage, 4);

        $this->assertSame(EntitlementSalesService::INVOICE_TYPE, $invoice->type);
        $this->assertFalse(OrderInvoiceRelation::where('invoice_id', $invoice->id)->exists());
        $ordersBefore = Order::count();

        resolve(PostPaymentService::class)->handle($invoice->fresh(), 'Credit Balance');
        $this->sales->fulfil($invoice->fresh()); // a second call changes nothing

        $this->assertSame($ordersBefore, Order::count());
        $this->assertSame(4, OrderEntitlement::where('order_id', $this->order->id)->value('quantity'));
    }

    public function test_an_extras_invoice_paid_after_the_term_changed_goes_to_credit_not_the_order(): void
    {
        // Priced for 15 days left; the order then renewed. Applying it would give the extra for the
        // whole new term at the 15-day price.
        $storage = $this->item('storage', 'gb', 'number');
        $invoice = $this->sales->createPurchaseInvoice($this->order, $storage, 100);
        Subscription::where('order_id', $this->order->id)->update(['ends_at' => now()->addDays(45)]); // renewed
        $payment = Payment::create(['user_id' => $this->customer->id, 'amount' => $invoice->grand_total, 'payment_status' => 'success', 'payment_method' => 'Stripe', 'currency' => $this->currency]);
        PaymentInvoice::create(['payment_id' => $payment->id, 'invoice_id' => $invoice->id, 'amount' => $invoice->grand_total]);
        $credit = resolve(CreditBalanceService::class);
        $before = $credit->balance($this->customer->id, $this->currency);

        $this->sales->fulfil($invoice->fresh());
        $this->sales->fulfil($invoice->fresh()); // once only

        $this->assertFalse(OrderEntitlement::where('order_id', $this->order->id)->exists());
        $this->assertEqualsWithDelta($before + (float) $invoice->grand_total, $credit->balance($this->customer->id, $this->currency), 0.001);
    }

    public function test_renewal_does_not_bill_archived_extras_or_ones_the_plan_now_includes(): void
    {
        $own = fn (Entitlement $item, int $quantity) => OrderEntitlement::create(['order_id' => $this->order->id, 'entitlement_id' => $item->id,
            'quantity' => $quantity, 'unit_price' => 2, 'currency' => $this->currency, 'period_id' => $this->periodId]);
        $storage = $this->item('storage', 'gb', 'number');
        $sla = $this->item('sla', 'enabled', 'boolean');
        $reports = $this->item('reports', 'enabled', 'boolean');
        $own($storage, 10);
        $own($sla, 1);
        $own($reports, 1);

        $sla->update(['is_active' => false]);                                    // archived: gone from the license
        $this->plan->entitlements()->attach($reports->id, ['value' => '1']);      // the plan now includes it

        $this->assertSame(20.0, $this->sales->renewalCost($this->order->id, $this->currency, $this->plan)); // storage only: 10 × 2
    }

    public function test_an_on_off_extra_is_never_bought_twice(): void
    {
        $sla = $this->item('sla', 'enabled', 'boolean');
        $first = $this->sales->createPurchaseInvoice($this->order, $sla, 1);

        // A second invoice while the first is unpaid is refused…
        try {
            $this->sales->createPurchaseInvoice($this->order, $sla, 1);
            $this->fail('A second invoice for the same on/off extra was created.');
        } catch (ValidationException $e) {
            $this->assertSame(__('message.extra_already_owned'), $e->errors()['quantity'][0]);
        }

        // …and one paid when the order already has it (an older duplicate) is credited, not stored as 2.
        $this->sales->fulfil($first->fresh());
        $duplicate = Invoice::factory()->create(['user_id' => $this->customer->id, 'currency' => $this->currency, 'grand_total' => 5, 'status' => 'success',
            'metadata' => [...$first->metadata, 'applied' => false]]);
        $payment = Payment::create(['user_id' => $this->customer->id, 'amount' => 5, 'payment_status' => 'success', 'payment_method' => 'Stripe', 'currency' => $this->currency]);
        PaymentInvoice::create(['payment_id' => $payment->id, 'invoice_id' => $duplicate->id, 'amount' => 5]);
        $before = resolve(CreditBalanceService::class)->balance($this->customer->id, $this->currency);

        $this->sales->fulfil($duplicate);

        $this->assertSame(1, OrderEntitlement::where('order_id', $this->order->id)->value('quantity'));
        $this->assertEqualsWithDelta($before + 5, resolve(CreditBalanceService::class)->balance($this->customer->id, $this->currency), 0.001);
    }

    public function test_renewing_in_another_currency_never_reuses_the_old_amount(): void
    {
        $storage = $this->item('storage', 'gb', 'number');
        OrderEntitlement::create(['order_id' => $this->order->id, 'entitlement_id' => $storage->id, 'quantity' => 1, 'unit_price' => 800, 'currency' => $this->currency, 'period_id' => $this->periodId]);
        $other = $this->currency === 'USD' ? 'INR' : 'USD';

        // No price in the new currency: stop, rather than charge 800 of the wrong currency.
        try {
            $this->sales->renewalCost($this->order->id, $other, $this->plan);
            $this->fail('Renewed an extra with an amount from another currency.');
        } catch (\RuntimeException $e) {
            $this->assertSame(__('message.extra_no_price_in_currency', ['name' => 'Storage', 'currency' => $other]), $e->getMessage());
        }

        $storage->prices()->create(['plan_id' => $this->plan->id, 'currency' => $other, 'unit_price' => 12, 'renew_price' => 10]);
        $this->assertSame(10.0, $this->sales->renewalCost($this->order->id, $other, $this->plan)); // the new currency's own price
        $this->assertSame(800.0, $this->sales->renewalCost($this->order->id, $this->currency, $this->plan)); // same currency: their price
    }

    public function test_extras_are_billed_at_renewal_and_reductions_apply_then(): void
    {
        $storage = $this->item('storage', 'gb', 'number');
        OrderEntitlement::create(['order_id' => $this->order->id, 'entitlement_id' => $storage->id, 'quantity' => 10, 'unit_price' => 2, 'currency' => $this->currency, 'period_id' => $this->periodId]);
        $planPrice = PlanPrice::where('plan_id', $this->plan->id)->first();

        $this->assertSame(120.0, new BaseRenewController()->renewalCost($this->order->id, $planPrice)); // 100 + 10 × 2

        $this->sales->setRenewQuantity($this->order, $storage, 3);
        $this->assertSame(106.0, new BaseRenewController()->renewalCost($this->order->id, $planPrice));
        $this->assertSame(10, OrderEntitlement::where('order_id', $this->order->id)->value('quantity')); // kept until renewal

        $this->sales->applyPendingReductions($this->order->id);
        $this->assertSame(3, OrderEntitlement::where('order_id', $this->order->id)->value('quantity'));

        $this->sales->setRenewQuantity($this->order, $storage, 0);
        $this->sales->applyPendingReductions($this->order->id);
        $this->assertFalse(OrderEntitlement::where('order_id', $this->order->id)->exists());
    }

    public function test_plan_change_keeps_extras_unless_the_new_plan_includes_the_feature(): void
    {
        $storage = $this->item('storage', 'gb', 'number');
        $chat = $this->item('chat', 'enabled', 'boolean');
        foreach ([$storage, $chat] as $item) {
            OrderEntitlement::create(['order_id' => $this->order->id, 'entitlement_id' => $item->id, 'quantity' => $item->type === 'boolean' ? 1 : 5, 'unit_price' => 2, 'currency' => $this->currency, 'period_id' => $this->periodId]);
        }
        $bigPlan = Plan::factory()->create(['product' => $this->product->id]);
        $bigPlan->entitlements()->attach($chat->id, ['value' => '1']);
        $storage->prices()->create(['plan_id' => $bigPlan->id, 'currency' => $this->currency, 'unit_price' => 1.5, 'renew_price' => 1]);
        $newOrder = Order::factory()->create(['product' => $this->product->id, 'client' => $this->customer->id]);
        Subscription::factory()->create(['order_id' => $newOrder->id, 'plan_id' => $bigPlan->id, 'product_id' => $this->product->id]);

        $this->sales->carryExtras($this->order, $newOrder, new EntitlementService);

        $this->assertSame([$storage->id], OrderEntitlement::where('order_id', $newOrder->id)->pluck('entitlement_id')->all());
        $this->assertSame('1.0000', OrderEntitlement::where('order_id', $newOrder->id)->value('unit_price')); // the new plan's renewal price
    }

    public function test_agents_are_priced_from_the_agents_price_points(): void
    {
        $planPrice = PlanPrice::where('plan_id', $this->plan->id)->firstOrFail();
        $planPrice->update(['add_price' => 500, 'renew_price' => 400, 'offer_price' => 0, 'no_of_agents' => 5]);
        $planPrice->refresh();

        // No agent prices yet: plan price ÷ its agents, as before (100 each, 80 on renewal).
        $this->assertSame(700.0, $this->sales->planCost($planPrice, 7, 'purchase'));
        $this->assertSame(560.0, $this->sales->planCost($planPrice, 7, 'renewal'));

        EntitlementService::agents()->prices()->create(['plan_id' => $this->plan->id, 'currency' => $this->currency, 'unit_price' => 120, 'renew_price' => 90, 'offer_percent' => 50]);

        $this->assertSame(500.0, $this->sales->planCost($planPrice, 5, 'purchase'));        // the included agents: plan price
        $this->assertSame(620.0, $this->sales->planCost($planPrice, 7, 'purchase'));        // + 2 × 60 (120 less 50%)
        $this->assertSame(380.0, $this->sales->planCost($planPrice, 3, 'purchase'));        // − 2 × 60
        $this->assertSame(580.0, $this->sales->planCost($planPrice, 7, 'renewal'));         // 400 + 2 × 90

        // An agent price above the plan's share (100) must not push fewer agents below 0.
        EntitlementService::agents()->prices()->where('plan_id', $this->plan->id)->update(['unit_price' => 300, 'offer_percent' => 0]);
        $this->assertSame(100.0, $this->sales->planCost($planPrice, 1, 'purchase'));        // 500 − 4 × 100, not 500 − 4 × 300
        $this->assertSame(800.0, $this->sales->planCost($planPrice, 6, 'purchase'));        // more agents still at 300

        $planPrice->update(['no_of_agents' => 0]);                                          // unlimited plan
        $this->assertSame(500.0, $this->sales->planCost($planPrice->refresh(), 9, 'purchase'));
    }

    public function test_each_plan_sells_extras_at_its_own_price(): void
    {
        $storage = $this->item('storage', 'gb', 'number', 10);
        $otherPlan = Plan::factory()->create(['product' => $this->product->id]);
        $storage->prices()->create(['plan_id' => $otherPlan->id, 'currency' => $this->currency, 'unit_price' => 6, 'renew_price' => 5]);
        $storage->refresh();

        $this->assertSame(10.0, $storage->priceIn($this->currency, $this->plan));
        $this->assertSame(5.0, $storage->priceIn($this->currency, $otherPlan, 'renewal'));
        $this->assertNull($storage->priceIn('XYZ', $otherPlan));
        $this->assertFalse($storage->canBeSoldOn(Plan::factory()->create(['product' => $this->product->id])));
        $this->assertSame(50.0, $this->sales->quote($this->order, $storage, 10)['amount']); // 10 × 10 × 15/30
    }

    private function assertRefused(callable $fn): void
    {
        try {
            $fn();
            $this->fail('Expected the purchase to be refused.');
        } catch (ValidationException) {
            $this->addToAssertionCount(1);
        }
    }
}
