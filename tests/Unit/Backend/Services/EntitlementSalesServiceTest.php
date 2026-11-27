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
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
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
        PlanPrice::factory()->create(['plan_id' => $this->plan->id, 'currency' => $this->currency, 'renew_price' => 100]);

        $this->order = Order::factory()->create(['product' => $this->product->id, 'client' => $this->customer->id, 'order_status' => 'executed']);
        Subscription::factory()->create([
            'order_id' => $this->order->id, 'plan_id' => $this->plan->id, 'product_id' => $this->product->id,
            'user_id' => $this->customer->id, 'ends_at' => now()->addDays(15)->addHour(),
        ]);
    }

    private function item(string $code, string $type, float $price = 2): Entitlement
    {
        $item = Entitlement::create([
            'code' => $code, 'label' => ucfirst($code),
            'type' => $type, 'default_value' => $type === 'boolean' ? '0' : '10',
        ]);
        $item->prices()->create(['plan_id' => $this->plan->id, 'currency' => $this->currency, 'unit_price' => $price]);

        return $item->fresh();
    }

    public function test_quote_charges_only_the_days_left(): void
    {
        $quote = $this->sales->quote($this->order, $this->item('storage', 'number'), 10);

        $this->assertSame(15, $quote['days_left']);
        $this->assertSame(10.0, $quote['amount']); // 2 × 10 units × 15/30 days
    }

    public function test_a_lifetime_licence_sells_extras_for_its_updates_term(): void
    {
        // One-time plan: the licence never expires (no ends_at); its term is the updates expiry.
        Subscription::where('order_id', $this->order->id)->update(['ends_at' => null, 'update_ends_at' => now()->addDays(15)->addHour()]);

        $quote = $this->sales->quote($this->order->fresh(), $this->item('storage', 'number'), 10);

        $this->assertSame(15, $quote['days_left']);
        $this->assertSame(10.0, $quote['amount']); // 2 × 10 units × 15/30 days — not "order expired"
    }

    public function test_quote_refuses_expired_orders_free_trials_and_a_second_feature(): void
    {
        $chat = $this->item('chat', 'boolean');
        OrderEntitlement::create(['order_id' => $this->order->id, 'entitlement_id' => $chat->id, 'quantity' => 1, 'unit_price' => 2, 'currency' => $this->currency, 'period_id' => $this->periodId]);
        $this->assertRefused(fn () => $this->sales->quote($this->order, $chat, 1));

        Subscription::where('order_id', $this->order->id)->update(['ends_at' => now()->subDay()]);
        $this->assertRefused(fn () => $this->sales->quote($this->order, $this->item('storage', 'number'), 1));
    }

    public function test_paid_invoice_adds_the_extra_once_and_never_creates_an_order(): void
    {
        Mail::fake();
        $storage = $this->item('storage', 'number');
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
        $storage = $this->item('storage', 'number');
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
        $storage = $this->item('storage', 'number');
        $sla = $this->item('sla', 'boolean');
        $reports = $this->item('reports', 'boolean');
        $own($storage, 10);
        $own($sla, 1);
        $own($reports, 1);

        $sla->update(['is_active' => false]);                                    // archived: gone from the license
        $this->plan->entitlements()->attach($reports->id, ['value' => '1']);      // the plan now includes it

        $this->assertSame(20.0, $this->sales->renewalCost($this->order->id, $this->currency, $this->plan)); // storage only: 10 × 2
    }

    public function test_an_on_off_extra_is_never_bought_twice(): void
    {
        $sla = $this->item('sla', 'boolean');
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
        $storage = $this->item('storage', 'number');
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
        $storage = $this->item('storage', 'number');
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
        $storage = $this->item('storage', 'number');
        $chat = $this->item('chat', 'boolean');
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

    /** A number feature the order's plan includes 5 of, with $bought more bought (0 = none, negative = a seeded "fewer" extra). */
    private function boughtOnTopOfFive(string $code, int $bought = 3): Entitlement
    {
        $item = $code === EntitlementService::AGENTS ? EntitlementService::agents()
            : (Entitlement::where('code', $code)->first() ?? Entitlement::create(['code' => $code, 'label' => ucfirst($code), 'type' => 'number', 'default_value' => '1']));
        $this->plan->entitlements()->attach($item->id, ['value' => '5']);
        $item->prices()->create(['plan_id' => $this->plan->id, 'currency' => $this->currency, 'unit_price' => 2]);
        if ($bought !== 0) {
            OrderEntitlement::create(['order_id' => $this->order->id, 'entitlement_id' => $item->id,
                'quantity' => $bought, 'unit_price' => 2, 'currency' => $this->currency, 'period_id' => $this->periodId]);
        }

        return $item->fresh();
    }

    /**
     * A plan to move to that includes $value of $item ('N', null = unlimited, false = no row), sold at 6
     * (renews at 4) in $currency; $currency false = no price at all.
     */
    private function target(Entitlement $item, string|null|false $value, string|false|null $currency = null): Plan
    {
        $plan = Plan::factory()->create(['product' => $this->product->id, 'days' => 30]);
        $plan->periods()->attach($this->periodId);
        if ($value !== false) {
            $plan->entitlements()->attach($item->id, ['value' => $value]);
        }
        if ($currency !== false) {
            $item->prices()->create(['plan_id' => $plan->id, 'currency' => $currency ?? $this->currency, 'unit_price' => 6, 'renew_price' => 4]);
        }

        return $plan;
    }

    /** Moves the order to $plan (carryExtras) and returns what the new order keeps of $item. */
    private function moveTo(Plan $plan, Entitlement $item): ?OrderEntitlement
    {
        $newOrder = Order::factory()->create(['product' => $this->product->id, 'client' => $this->customer->id]);
        Subscription::factory()->create(['order_id' => $newOrder->id, 'plan_id' => $plan->id, 'product_id' => $this->product->id, 'user_id' => $this->customer->id]);
        $this->sales->carryExtras($this->order, $newOrder, new EntitlementService);

        return OrderEntitlement::where('order_id', $newOrder->id)->where('entitlement_id', $item->id)->first();
    }

    /** @return array<string, array{string}> */
    public static function numberFeatures(): array
    {
        return ['agents' => [EntitlementService::AGENTS], 'storage' => ['storage']];
    }

    #[DataProvider('numberFeatures')]
    public function test_a_plan_change_keeps_only_what_was_bought_of_any_number_feature(string $code): void
    {
        $item = $this->boughtOnTopOfFive($code); // 5 included + 3 bought

        foreach (['10', '4'] as $included) { // upgrade or downgrade: the 3 bought move, the old plan's 5 don't
            $kept = $this->moveTo($this->target($item, $included), $item);
            $this->assertSame(3, $kept->quantity);
            $this->assertSame('4.0000', $kept->unit_price); // at the new plan's renewal price
        }
        $this->assertNull($this->moveTo($this->target($item, null), $item)); // unlimited: nothing left to pay for

        // What the new plan is worth for the order: its price + the carried extra at its purchase price.
        $this->assertSame(3 * 6.0, $this->sales->movedExtrasValue($this->order, $this->target($item, '4'), $this->currency));
        $this->assertSame(0.0, $this->sales->movedExtrasValue($this->order, $this->target($item, null), $this->currency));
    }

    #[DataProvider('numberFeatures')]
    public function test_a_plan_change_without_bought_extras_bills_nothing_more(string $code): void
    {
        $item = $this->boughtOnTopOfFive($code, bought: 0); // 5 included, nothing bought

        $this->assertNull($this->moveTo($this->target($item, '2'), $item)); // 100 GB → 50 GB plan = 50 GB
        $this->assertNull($this->moveTo($this->target($item, '10'), $item));
        $this->assertSame(0.0, $this->sales->movedExtrasValue($this->order, $this->target($item, '2'), $this->currency));
    }

    #[DataProvider('numberFeatures')]
    public function test_a_plan_change_drops_a_fewer_extra(string $code): void
    {
        $item = $this->boughtOnTopOfFive($code, bought: -2); // seeded: 2 fewer than the plan includes
        \Logger::spy();

        $this->assertNull($this->moveTo($this->target($item, '4'), $item)); // the new plan's own price applies
        \Logger::shouldNotHaveReceived('exception');
    }

    #[DataProvider('numberFeatures')]
    public function test_a_plan_change_drops_an_extra_the_new_plan_does_not_sell_in_the_currency_and_logs_it(string $code): void
    {
        $item = $this->boughtOnTopOfFive($code);
        $plan = $this->target($item, '2', currency: 'XYZ');
        \Logger::spy();

        $this->assertNull($this->moveTo($plan, $item));
        $this->assertSame(0.0, $this->sales->movedExtrasValue($this->order, $plan, $this->currency));
        \Logger::shouldHaveReceived('exception')->with(Mockery::on(fn (\Throwable $e): bool => str_contains($e->getMessage(), "from order {$this->order->id} to order")));
    }

    #[DataProvider('numberFeatures')]
    public function test_a_plan_change_keeps_a_pending_reduction(string $code): void
    {
        $item = $this->boughtOnTopOfFive($code);
        $this->sales->setRenewQuantity($this->order, $item, 1); // 1 from the next renewal

        $kept = $this->moveTo($this->target($item, '4'), $item);

        $this->assertSame(3, $kept->quantity);
        $this->assertSame(1, $kept->renew_quantity);
    }

    public function test_a_flat_target_drops_bought_extras_and_an_optional_one_keeps_them(): void
    {
        $agents = $this->boughtOnTopOfFive(EntitlementService::AGENTS);
        $storage = $this->boughtOnTopOfFive('storage');
        \Logger::spy();

        $this->assertNull($this->moveTo($this->target($agents, false, currency: false), $agents)); // a flat plan: no agents
        $this->assertNull($this->moveTo($this->target($storage, false, currency: false), $storage)); // not on the plan at all
        \Logger::shouldHaveReceived('exception')->with(Mockery::on(fn (\Throwable $e): bool => str_contains($e->getMessage(), 'dropped 3 Agents')));
        $this->assertSame(3, $this->moveTo($this->target($storage, false), $storage)->quantity); // sold by the unit
    }

    public function test_a_plan_including_no_agents_keeps_at_least_one(): void
    {
        $agents = EntitlementService::agents(); // the order's plan is flat: no agents

        $this->assertSame(1, $this->moveTo($this->target($agents, '0'), $agents)->quantity); // the license needs a count
        $this->assertSame(1 * 6.0, $this->sales->movedExtrasValue($this->order, $this->target($agents, '0'), $this->currency));
    }

    public function test_free_or_unlimited_agents_from_an_admin_are_not_carried_as_paid_agents(): void
    {
        $agents = $this->boughtOnTopOfFive(EntitlementService::AGENTS); // 5 + 3 bought
        $subscription = Subscription::where('order_id', $this->order->id)->firstOrFail();

        resolve(EntitlementService::class)->setOverride($subscription, $agents, '20', null);
        $this->assertSame(3, $this->moveTo($this->target($agents, '7'), $agents)->quantity); // only what was bought

        resolve(EntitlementService::class)->setOverride($subscription, $agents, null, null);
        $this->assertNull($this->moveTo($this->target($agents, '2'), $agents)); // stays unlimited through its override
    }

    public function test_stray_agent_prices_on_a_flat_plan_sell_and_carry_no_agents(): void
    {
        $agents = EntitlementService::agents();
        $agents->prices()->create(['plan_id' => $this->plan->id, 'currency' => $this->currency, 'unit_price' => 2]); // no Agents row
        $agents->refresh();

        $this->assertFalse($agents->canBeSoldOn($this->plan));
        $this->assertRefused(fn () => $this->sales->quote($this->order, $agents, 1));

        // An order with bought agents moving to a flat plan with stray prices keeps none.
        $this->plan->entitlements()->attach($agents->id, ['value' => '5']);
        OrderEntitlement::create(['order_id' => $this->order->id, 'entitlement_id' => $agents->id,
            'quantity' => 3, 'unit_price' => 2, 'currency' => $this->currency, 'period_id' => $this->periodId]);
        \Logger::spy();
        $this->assertNull($this->moveTo($this->target($agents, false), $agents));
    }

    #[DataProvider('numberFeatures')]
    public function test_a_number_feature_a_plan_includes_without_limit_is_not_sold(string $code): void
    {
        $item = $this->boughtOnTopOfFive($code, bought: 0);

        $this->assertTrue($item->canBeSoldOn($this->target($item, '4')));
        $this->assertFalse($item->fresh()->canBeSoldOn($this->target($item, null)));
    }

    public function test_the_plan_price_is_its_own_price_whatever_the_agents(): void
    {
        $planPrice = PlanPrice::where('plan_id', $this->plan->id)->firstOrFail();
        $planPrice->forceFill(['add_price' => 500, 'renew_price' => 400, 'offer_price' => 10])->save();

        $this->assertSame(450.0, $this->sales->planPrice($planPrice->refresh(), 'purchase')); // 500 less 10%
        $this->assertSame(400.0, $this->sales->planPrice($planPrice, 'renewal'));
    }

    /** The order's plan includes $included agents at 20 each; the order has an Agents extra of $quantity. */
    private function agentsExtra(string $included, int $quantity): Entitlement
    {
        $agents = EntitlementService::agents();
        $this->plan->entitlements()->attach($agents->id, ['value' => $included]);
        $agents->prices()->create(['plan_id' => $this->plan->id, 'currency' => $this->currency, 'unit_price' => 20]);
        OrderEntitlement::create(['order_id' => $this->order->id, 'entitlement_id' => $agents->id,
            'quantity' => $quantity, 'unit_price' => 20, 'currency' => $this->currency, 'period_id' => $this->periodId]);

        return $agents->fresh();
    }

    public function test_renewal_is_the_plan_price_plus_its_extras(): void
    {
        $this->agentsExtra('5', 3);
        $planPrice = PlanPrice::where('plan_id', $this->plan->id)->firstOrFail();

        $this->assertSame(160.0, new BaseRenewController()->renewalCost($this->order->id, $planPrice)); // 100 + 3 × 20
    }

    public function test_a_seeded_fewer_agents_extra_lowers_the_renewal(): void
    {
        $agents = $this->agentsExtra('5', -2);
        $this->order->update(['serial_key' => null]); // no license to rewrite here
        $planPrice = PlanPrice::where('plan_id', $this->plan->id)->firstOrFail();

        $this->assertSame(60.0, new BaseRenewController()->renewalCost($this->order->id, $planPrice)); // 100 − 2 × 20
        $line = new BaseRenewController()->generateInvoice($this->product, $this->customer, $this->order->id, $this->plan->id, 60, '', 3, $this->currency);
        $this->assertEquals([['entitlement_id' => $agents->id, 'label' => 'Fewer Agents −2', 'quantity' => -2, 'unit_price' => 20]],
            $line->fresh()->extras); // listed under the renewal line, taking 2 × 20 off

        $this->assertSame(10.0, $this->sales->quote($this->order, $agents, 1)['amount']); // 1 × 20 × 15/30
        $invoice = $this->sales->createPurchaseInvoice($this->order, $agents, 1);
        $this->sales->fulfil($invoice->fresh());

        $this->assertSame(-1, OrderEntitlement::where('order_id', $this->order->id)->value('quantity'));
    }

    public function test_the_plan_line_never_goes_below_zero(): void
    {
        $this->agentsExtra('9', -8);
        $planPrice = PlanPrice::where('plan_id', $this->plan->id)->firstOrFail();

        $this->assertSame(0.0, new BaseRenewController()->renewalCost($this->order->id, $planPrice)); // 100 − 8 × 20
    }

    public function test_a_zero_included_plan_cannot_reduce_below_one_agent(): void
    {
        $agents = $this->agentsExtra('0', 3);

        $this->assertRefused(fn () => $this->sales->setRenewQuantity($this->order, $agents, 0));
        $this->assertSame(1, $this->sales->setRenewQuantity($this->order, $agents, 1)->renew_quantity);
    }

    public function test_a_customer_cannot_deepen_a_fewer_agents_extra(): void
    {
        $agents = $this->agentsExtra('5', -2);

        $this->assertRefused(fn () => $this->sales->setRenewQuantity($this->order, $agents, -3));
        $this->assertNull($this->sales->setRenewQuantity($this->order, $agents, -2)->renew_quantity); // unchanged is fine
    }

    public function test_a_fewer_extra_of_any_feature_is_labelled_and_cannot_be_deepened(): void
    {
        $storage = $this->item('storage', 'number');
        $this->plan->entitlements()->attach($storage->id, ['value' => '10']);
        OrderEntitlement::create(['order_id' => $this->order->id, 'entitlement_id' => $storage->id,
            'quantity' => -2, 'unit_price' => 2, 'currency' => $this->currency, 'period_id' => $this->periodId]);

        $this->assertSame('Fewer Storage −2', $this->sales->renewalExtras($this->order->id, $this->currency, $this->plan)[0]['label']);
        $this->assertRefused(fn () => $this->sales->setRenewQuantity($this->order, $storage, -3));
        $this->assertNull($this->sales->setRenewQuantity($this->order, $storage, -2)->renew_quantity);
    }

    public function test_agents_are_not_sold_to_an_order_with_unlimited_agents(): void
    {
        $agents = $this->agentsExtra('5', 2);
        resolve(EntitlementService::class)->setOverride(Subscription::where('order_id', $this->order->id)->firstOrFail(),
            $agents, null, null);

        try {
            $this->sales->quote($this->order, $agents, 1);
            $this->fail('Agents must not be sold to an unlimited order.');
        } catch (ValidationException $exception) {
            $this->assertSame(__('message.extra_agents_unlimited'), $exception->validator->errors()->first());
        }
        $this->assertFalse(collect($this->sales->forOrder($this->order))->firstWhere('id', $agents->id)['can_buy']);
    }

    public function test_a_license_running_unlimited_agents_is_not_sold_agents(): void
    {
        $agents = EntitlementService::agents();
        $this->plan->entitlements()->attach($agents->id, ['value' => '10']); // the plan counts agents now
        $agents->prices()->create(['plan_id' => $this->plan->id, 'currency' => $this->currency, 'unit_price' => 1]);
        $this->order->update(['serial_key' => \Illuminate\Support\Facades\Crypt::encrypt('ABCDEFGHIJKL0000')]);

        $row = collect($this->sales->forOrder($this->order->fresh()))->firstWhere('id', $agents->id);
        $this->assertSame([false, __('message.extra_agents_unlimited')], [$row['can_buy'], $row['reason']]);
    }

    public function test_the_list_offers_only_what_can_be_bought(): void
    {
        $chat = $this->item('chat', 'boolean', 10);
        $chat->prices()->update(['renew_price' => 30, 'offer_percent' => 50]);

        $row = collect($this->sales->forOrder($this->order))->firstWhere('id', $chat->id);
        $this->assertSame([true, 30.0], [$row['can_buy'], $row['unit_price']]); // the price each billing period

        Subscription::where('order_id', $this->order->id)->update(['ends_at' => now()->subDay()]); // expired: can't be bought
        $row = collect($this->sales->forOrder($this->order))->firstWhere('id', $chat->id);
        $this->assertSame([false, __('message.extra_order_expired')], [$row['can_buy'], $row['reason']]);
    }

    public function test_bought_agents_must_fit_the_license_codes_four_digits(): void
    {
        $agents = $this->agentsExtra('5', 9990); // 9995 agents

        $this->assertSame(4, $this->sales->createPurchaseInvoice($this->order, $agents, 4)->metadata['quantity']);
        $this->assertRefused(fn () => $this->sales->quote($this->order, $agents, 5)); // 10000 would be written as 0000
    }

    public function test_each_plan_sells_extras_at_its_own_price(): void
    {
        $storage = $this->item('storage', 'number', 10);
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
