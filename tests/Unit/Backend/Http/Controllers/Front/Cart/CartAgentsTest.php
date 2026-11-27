<?php

namespace Tests\Unit\Backend\Http\Controllers\Front\Cart;

use App\Http\Controllers\Front\Cart\CartService;
use App\Http\Controllers\Front\Cart\GuestCart;
use App\Http\Controllers\Order\ExtendedOrderController;
use App\Model\Cart\Cart;
use App\Model\Cart\CartItem;
use App\Model\Common\Country;
use App\Model\Order\Invoice;
use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Payment\PromoProductRelation;
use App\Model\Payment\Promotion;
use App\Model\Payment\PromotionType;
use App\Model\Payment\TaxOption;
use App\Model\Product\Product;
use App\Services\EntitlementService;
use App\Services\Payment\InvoicePaymentService;
use App\Services\Tax\TaxService;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\DBTestCase;

/**
 * Agents are a plan feature: the plan's Agents row is how many its price includes ('N', '0' =
 * every agent paid, null = unlimited); more agents are an Agents extra on the line, like Storage.
 * A plan without the row is flat-priced. B54: Freelancer (a 2-agent plan) was bought with 1 agent
 * because the store hard-coded agents: 1.
 */
class CartAgentsTest extends DBTestCase
{
    use DatabaseTransactions;

    private CartService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('user');
        $this->service = new CartService(new GuestCart, Mockery::mock(InvoicePaymentService::class));
        Cart::where('user_id', $this->user->id)->delete();
    }

    private function request(bool $guest = false): Request
    {
        $request = Request::create('/cart', 'POST');
        $request->setLaravelSession(resolve('session.store'));
        if (! $guest) {
            $request->setUserResolver(fn () => $this->user);
        }

        return $request;
    }

    /**
     * A product with one plan priced in USD and INR. $perAgent adds the plan's Agents row with
     * $included as its value, and an Agents price in $agentCurrencies when $agentPrice is set.
     *
     * @param  list<string>  $agentCurrencies
     * @return array{0: Product, 1: Plan}
     */
    private function product(float $price = 100, bool $perAgent = false, ?string $included = null, ?float $agentPrice = null, array $agentCurrencies = ['USD', 'INR']): array
    {
        $product = Product::factory()->create();
        $plan = Plan::factory()->create(['product' => $product->id]);
        foreach (['USD', 'INR'] as $currency) {
            PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => $currency, 'add_price' => $price, 'offer_price' => 0]);
        }
        if ($perAgent) {
            $plan->entitlements()->attach(EntitlementService::agents()->id, ['value' => $included]);
        }
        foreach ($agentPrice === null ? [] : $agentCurrencies as $currency) {
            EntitlementService::agents()->prices()->create(['plan_id' => $plan->id, 'currency' => $currency, 'unit_price' => $agentPrice]);
        }

        return [$product, $plan];
    }

    private function line(): CartItem
    {
        $cart = $this->service->resolveCart($this->request());
        $cart->update(['currency' => 'USD']);

        return $this->service->resolveCart($this->request())->items->sole();
    }

    public function test_a_stale_cart_currency_follows_the_user_and_drops_lines_it_cannot_price(): void
    {
        // QA case: cart created as INR, user now gets GBP — the cart must switch, and a line
        // with no price in the new currency is dropped rather than charged 0.
        $currency = getCurrencyForClient($this->user->country);
        $old = $currency === 'EUR' ? 'JPY' : 'EUR';
        [$priced, $pricedPlan] = $this->product();
        PlanPrice::where('plan_id', $pricedPlan->id)->delete();
        PlanPrice::factory()->create(['plan_id' => $pricedPlan->id, 'currency' => $currency, 'add_price' => 100, 'offer_price' => 0]);
        PlanPrice::factory()->create(['plan_id' => $pricedPlan->id, 'currency' => $old, 'add_price' => 100, 'offer_price' => 0]);
        [$unpriced, $unpricedPlan] = $this->product();
        PlanPrice::where('plan_id', $unpricedPlan->id)->delete();
        PlanPrice::factory()->create(['plan_id' => $unpricedPlan->id, 'currency' => $old, 'add_price' => 100, 'offer_price' => 0]);

        $cart = Cart::create(['user_id' => $this->user->id, 'currency' => $old]);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $priced->id, 'plan_id' => $pricedPlan->id, 'quantity' => 1]);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $unpriced->id, 'plan_id' => $unpricedPlan->id, 'quantity' => 1]);

        $cart = $this->service->resolveCart($this->request());

        $this->assertSame($currency, $cart->currency);
        $this->assertSame([$priced->id], $cart->items->pluck('product_id')->map(fn ($id): int => (int) $id)->all());
    }

    public function test_guest_usd_cart_becomes_inr_with_the_inr_price_when_an_indian_user_logs_in(): void
    {
        // QA case: guest adds a $50 product, an Indian user (whose old cart row is USD) logs in.
        $this->user->update(['country' => 'IN']);
        $inr = Currency::updateOrCreate(['code' => 'INR'], ['name' => 'Indian Rupee', 'symbol' => '₹', 'status' => 1]);
        Country::where('country_code_char2', 'IN')->update(['currency_id' => $inr->id]);
        [$product, $plan] = $this->product();
        PlanPrice::where('plan_id', $plan->id)->where('currency', 'USD')->update(['add_price' => 50]);
        PlanPrice::where('plan_id', $plan->id)->where('currency', 'INR')->update(['add_price' => 4000]);
        Cart::create(['user_id' => $this->user->id, 'currency' => 'USD']);
        $guest = new GuestCart;
        $guest->clear();
        $guest->add(['product_id' => $product->id, 'plan_id' => $plan->id, 'agents' => 1], 'USD');

        $this->service->mergeGuestCart($this->user);
        $cart = $this->service->resolveCart($this->request());

        $this->assertSame('INR', $cart->currency);
        $this->assertEqualsWithDelta(4000.0, $cart->items->sole()->lineTotal(), 0.001);
    }

    public function test_a_deleted_product_leaves_the_cart_instead_of_costing_zero(): void
    {
        // QA case: product added, then the admin deletes it before checkout.
        [$kept, $keptPlan] = $this->product();
        [$gone, $gonePlan] = $this->product();
        $this->service->addItem($this->request(), ['product_id' => $kept->id, 'plan_id' => $keptPlan->id]);
        $this->service->addItem($this->request(), ['product_id' => $gone->id, 'plan_id' => $gonePlan->id]);

        $gone->delete();

        $cart = $this->service->resolveCart($this->request());

        $this->assertTrue($cart->itemsRemoved);
        $this->assertSame([$kept->id], $cart->items->pluck('product_id')->map(fn ($id): int => (int) $id)->all());
        $this->assertFalse($this->service->resolveCart($this->request())->itemsRemoved); // told once
    }

    public function test_a_guest_line_whose_price_is_gone_leaves_the_guest_cart(): void
    {
        [$product, $plan] = $this->product();
        $guest = new GuestCart;
        $guest->clear();
        $guest->add(['product_id' => $product->id, 'plan_id' => $plan->id], 'USD');
        PlanPrice::where('plan_id', $plan->id)->delete();

        $cart = $guest->toCart();

        $this->assertTrue($cart->itemsRemoved);
        $this->assertCount(0, $cart->items);
        $this->assertSame([], $guest->all());
    }

    public function test_place_order_stops_when_lines_were_just_removed(): void
    {
        [$product, $plan] = $this->product();
        $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id]);
        $product->delete();

        $this->actingAs($this->user)->postJson('/my-cart/place-order')
            ->assertStatus(422)
            ->assertJson(['message' => __('message.cart_items_unavailable_removed')]);
    }

    public function test_more_agents_are_an_agents_extra_on_the_line(): void
    {
        [$product, $plan] = $this->product(price: 100, perAgent: true, included: '5', agentPrice: 20);
        $agents = EntitlementService::agents()->id;
        $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id]);
        $this->assertEqualsWithDelta(100.0, $this->line()->lineTotal(), 0.001); // the plan includes 5

        $this->service->updateItem($this->request(), $this->line()->id, ['extras' => [$agents => 2]]);

        $line = $this->line();
        $this->assertEqualsWithDelta(140.0, $line->lineTotal(), 0.001); // 100 + 2 × 20
        $this->assertSame(7, $line->totalAgents('USD'));

        $item = $this->cartSummary()['items'][0];
        $this->assertSame(7, $item['agents']); // → licence serial …0007
        $row = collect($item['extras'])->firstWhere('entitlement_id', $agents);
        $this->assertSame(2, $row['quantity']);
        $this->assertEqualsWithDelta(20.0, $row['unit_price'], 0.001);
    }

    public function test_agents_cannot_go_below_the_included_count(): void
    {
        [$product, $plan] = $this->product(price: 100, perAgent: true, included: '5', agentPrice: 20);
        $agents = EntitlementService::agents()->id;
        $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id]);

        $this->service->updateItem($this->request(), $this->line()->id, ['extras' => [$agents => -1], 'agents' => 3]);

        $line = $this->line();
        $this->assertArrayNotHasKey($agents, $line->extras ?? []);
        $this->assertSame(5, $line->totalAgents('USD'));
        $this->assertEqualsWithDelta(100.0, $line->lineTotal(), 0.001); // no "fewer agents" discount
    }

    public function test_a_zero_included_plan_starts_with_one_paid_agent(): void
    {
        [$product, $plan] = $this->product(price: 100, perAgent: true, included: '0', agentPrice: 30);
        $agents = EntitlementService::agents()->id;
        $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id]);

        $line = $this->line();
        $this->assertSame(1, collect($line->chosenExtras('USD'))->firstWhere('entitlement_id', $agents)['quantity']);
        $this->assertSame(1, collect($line->availableExtras('USD'))->firstWhere('entitlement_id', $agents)['min']);
        $this->assertEqualsWithDelta(130.0, $line->lineTotal(), 0.001);
        $this->assertSame(1, $line->totalAgents('USD')); // never 0000 = unlimited

        $this->service->updateItem($this->request(), $line->id, ['extras' => [$agents => 0]]);

        $line = $this->line();
        $this->assertSame(1, $line->extras[$agents] ?? null);
        $this->assertEqualsWithDelta(130.0, $line->lineTotal(), 0.001);
    }

    public function test_a_zero_included_plan_without_an_agent_price_in_the_currency_is_not_sold(): void
    {
        // Each agent would cost 0 in that currency, so any number of agents would be free.
        [$product, $plan] = $this->product(perAgent: true, included: '0', agentPrice: 500, agentCurrencies: ['INR']);
        $item = new CartItem(['product_id' => $product->id, 'plan_id' => $plan->id]);

        $this->assertTrue($item->isAvailable('INR'));
        $this->assertFalse($item->isAvailable('USD')); // no USD agent price: the line is dropped, not free
        $plan->update(['status' => 1, 'days' => 365]);
        PlanPrice::where('plan_id', $plan->id)->update(['renew_price' => 100]);
        $this->assertSame(1, Plan::renewableFor($product->id, false, 'INR')->whereKey($plan->id)->count());
        $this->assertSame(0, Plan::renewableFor($product->id, false, 'USD')->whereKey($plan->id)->count());
    }

    public function test_unlimited_plan_sells_no_agents(): void
    {
        [$product, $plan] = $this->product(price: 100, perAgent: true, included: null, agentPrice: 20);
        $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id]);

        $line = $this->line();
        $this->assertNull(collect($line->availableExtras('USD'))->firstWhere('entitlement_id', EntitlementService::agents()->id));
        $this->assertSame(0, $line->totalAgents('USD'));
        $this->assertEqualsWithDelta(100.0, $line->lineTotal(), 0.001);

        // Licence serial's last four digits are the agent count; 0000 reads as Unlimited.
        $this->assertStringEndsWith('0000', (new ExtendedOrderController)->generateSerialKey($product->id, 0));
    }

    public function test_a_flat_plan_has_no_agents(): void
    {
        [$product, $plan] = $this->product();
        $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id, 'agents' => 3]);

        $line = $this->line();
        $this->assertSame(0, $line->totalAgents('USD'));
        $this->assertEqualsWithDelta(100.0, $line->lineTotal(), 0.001);
    }

    public function test_stray_agent_prices_on_a_flat_plan_sell_no_agents(): void
    {
        [$product, $plan] = $this->product(agentPrice: 20); // no Agents row: the license can't count them
        $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id]);

        $this->assertNull(collect($this->line()->availableExtras('USD'))->firstWhere('entitlement_id', EntitlementService::agents()->id));
    }

    // ── C2: the plan must be this product's own, and priced in the cart's currency ──

    private function assertRefused(callable $add): void
    {
        try {
            $add();
            $this->fail('Expected the cart to refuse this line.');
        } catch (HttpException $httpException) {
            $this->assertSame(422, $httpException->getStatusCode());
        }

        $this->assertCount(0, $this->service->resolveCart($this->request())->items);
    }

    public function test_another_products_plan_is_refused(): void
    {
        [$paid] = $this->product(price: 500);
        [, $freePlan] = $this->product(price: 0);

        // The exploit: a paid product on someone else's free plan.
        $this->assertRefused(fn () => $this->service->addItem($this->request(), ['product_id' => $paid->id, 'plan_id' => $freePlan->id]));
    }

    public function test_a_plan_that_does_not_exist_is_refused(): void
    {
        [$product] = $this->product();

        $this->assertRefused(fn () => $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => 99999999]));
    }

    public function test_a_plan_with_no_price_in_the_carts_currency_is_refused(): void
    {
        [$product, $plan] = $this->product();
        $cartCurrency = $this->service->resolveCart($this->request())->currency;
        PlanPrice::where('plan_id', $plan->id)->where('currency', $cartCurrency)->delete();

        $this->assertRefused(fn () => $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id]));
    }

    public function test_a_guest_line_the_cart_now_refuses_is_dropped_on_login_not_thrown(): void
    {
        [$paid] = $this->product(price: 500);
        [, $freePlan] = $this->product(price: 0);
        [$good, $goodPlan] = $this->product();
        $guest = new GuestCart;
        $guest->clear();
        $guest->add(['product_id' => $paid->id, 'plan_id' => $freePlan->id], 'USD'); // saved before the fix
        $guest->add(['product_id' => $good->id, 'plan_id' => $goodPlan->id], 'USD');

        $this->service->mergeGuestCart($this->user); // must not throw — it runs inside login

        $items = $this->service->resolveCart($this->request())->items;
        $this->assertSame([$good->id], $items->pluck('product_id')->all());
    }

    // ── H4: a coupon discounts only the products it is linked to ──

    private function coupon(Product $for, string $value, int $type = 1, ?string $currency = null): string
    {
        $code = 'T'.strtoupper(uniqid());
        $type = PromotionType::find($type) ? $type : PromotionType::firstOrCreate(['name' => 'Percentage'])->id;
        $promo = Promotion::create([
            'code' => $code, 'type' => $type, 'uses' => 100, 'value' => $value, 'currency' => $currency,
            'start' => now()->subDay()->toDateTimeString(), 'expiry' => now()->addDay()->toDateTimeString(),
        ]);
        PromoProductRelation::create(['promotion_id' => $promo->id, 'product_id' => $for->id]);

        return $code;
    }

    private function cartDiscount(): float
    {
        return (float) Cart::where('user_id', $this->user->id)->value('coupon_discount');
    }

    public function test_a_coupon_for_another_product_is_refused(): void
    {
        [$a] = $this->product(price: 100);
        [$b, $bPlan] = $this->product(price: 200);
        $this->service->addItem($this->request(), ['product_id' => $b->id, 'plan_id' => $bPlan->id]);

        try {
            $this->service->applyCoupon($this->request(), $this->coupon(for: $a, value: '10%'));
            $this->fail('Expected the coupon to be refused.');
        } catch (\Exception $exception) {
            $this->assertSame(__('message.invalid_coupon_code'), $exception->getMessage());
        }

        $this->assertNull(Cart::where('user_id', $this->user->id)->value('coupon_code'));
    }

    public function test_a_coupon_discounts_only_its_own_product_in_a_mixed_cart(): void
    {
        [$a, $aPlan] = $this->product(price: 100);
        [$b, $bPlan] = $this->product(price: 200);
        $this->service->addItem($this->request(), ['product_id' => $a->id, 'plan_id' => $aPlan->id]);
        $this->service->addItem($this->request(), ['product_id' => $b->id, 'plan_id' => $bPlan->id]);
        Cart::where('user_id', $this->user->id)->update(['currency' => 'USD']);

        $this->service->applyCoupon($this->request(), $this->coupon(for: $a, value: '10%'));

        $this->assertEqualsWithDelta(10.0, $this->cartDiscount(), 0.001); // 10% of A's 100 — not of the 300 cart
    }

    public function test_a_fixed_coupon_is_capped_at_its_products_price_and_dropped_when_that_product_leaves(): void
    {
        [$a, $aPlan] = $this->product(price: 100);
        [$b, $bPlan] = $this->product(price: 200);
        $this->service->addItem($this->request(), ['product_id' => $a->id, 'plan_id' => $aPlan->id]);
        $this->service->addItem($this->request(), ['product_id' => $b->id, 'plan_id' => $bPlan->id]);
        Cart::where('user_id', $this->user->id)->update(['currency' => 'USD']);

        $this->service->applyCoupon($this->request(), $this->coupon(for: $a, value: '486', type: 2));
        $this->assertEqualsWithDelta(100.0, $this->cartDiscount(), 0.001); // not 200 off the whole cart

        $aLine = CartItem::whereHas('cart', fn (Builder $q) => $q->where('user_id', $this->user->id))->where('product_id', $a->id)->firstOrFail();
        $this->service->removeItem($this->request(), $aLine->id);

        $this->assertNull(Cart::where('user_id', $this->user->id)->value('coupon_code'));
        $this->assertSame(0.0, $this->cartDiscount());
    }

    public function test_a_fixed_coupon_works_only_in_its_own_currency(): void
    {
        // "500 off" in INR must not take 500 USD off a USD cart; a percentage works in any currency.
        [$a, $aPlan] = $this->product(price: 100);
        $this->service->addItem($this->request(), ['product_id' => $a->id, 'plan_id' => $aPlan->id]);
        Cart::where('user_id', $this->user->id)->update(['currency' => 'USD']);

        try {
            $this->service->applyCoupon($this->request(), $this->coupon(for: $a, value: '50', type: 2, currency: 'INR'));
            $this->fail('An INR coupon was applied to a USD cart.');
        } catch (\Exception $exception) {
            $this->assertSame(__('message.coupon_wrong_currency', ['currency' => 'USD']), $exception->getMessage());
        }

        $this->service->applyCoupon($this->request(), $this->coupon(for: $a, value: '50', type: 2, currency: 'USD'));
        $this->assertEqualsWithDelta(50.0, $this->cartDiscount(), 0.001);

        $this->service->applyCoupon($this->request(), $this->coupon(for: $a, value: '10%'));
        $this->assertEqualsWithDelta(10.0, $this->cartDiscount(), 0.001);
    }

    // ── Batch 3 #4: tax is charged on the price after the coupon ──

    private function flatTax(float $percent): void
    {
        $tax = Mockery::mock(TaxService::class);
        $tax->shouldReceive('calculate')->andReturnUsing(fn (float $amount): array => [
            'applicable' => true, 'prices_include_tax' => false, 'rates' => [], 'percent' => $percent, 'name' => 'GST',
            'total' => round($amount * $percent / 100, 2),
            'lines' => [['tax_rate_id' => 1, 'label' => 'GST', 'rate' => $percent, 'compound' => false, 'amount' => round($amount * $percent / 100, 2)]],
        ]);
        $this->app->instance(TaxService::class, $tax);
        TaxOption::whereKey(1)->update(['inclusive' => 0]);
    }

    private function cartSummary(): array
    {
        Cart::where('user_id', $this->user->id)->update(['currency' => 'USD']);
        $cart = $this->service->resolveCart($this->request());

        return new \ReflectionMethod($this->service, 'summary')->invoke($this->service, $cart, $this->user);
    }

    public function test_tax_is_charged_on_the_discounted_price(): void
    {
        // 100 with 50% off at 18%: 50 + 9 = 59 — taxing before the discount gave 100 − 50 + 18 = 68.
        [$a, $aPlan] = $this->product(price: 100);
        $this->service->addItem($this->request(), ['product_id' => $a->id, 'plan_id' => $aPlan->id]);
        Cart::where('user_id', $this->user->id)->update(['currency' => 'USD']);
        $this->service->applyCoupon($this->request(), $this->coupon(for: $a, value: '50%'));
        $this->flatTax(18);

        $summary = $this->cartSummary();

        $this->assertEqualsWithDelta(9.0, $summary['tax_total'], 0.001);
        $this->assertEqualsWithDelta(59.0, $summary['grand_total'], 0.001);
        $this->assertEqualsWithDelta(100.0, $summary['items'][0]['subtotal'], 0.001); // line price itself unchanged
    }

    public function test_a_coupon_for_another_product_is_rejected(): void
    {
        // QA case: coupon made for HD Enterprise, cart holds only HD Enterprise Pro.
        [$a] = $this->product(price: 100);
        [$b, $bPlan] = $this->product(price: 100);
        $this->service->addItem($this->request(), ['product_id' => $b->id, 'plan_id' => $bPlan->id]);

        try {
            $this->service->applyCoupon($this->request(), $this->coupon(for: $a, value: '10', type: 2));
            $this->fail('A coupon for another product was applied.');
        } catch (\Exception $exception) {
            $this->assertSame(__('message.invalid_coupon_code'), $exception->getMessage());
        }

        $this->assertSame(0.0, $this->cartDiscount());
    }

    public function test_a_used_up_coupon_for_another_product_says_invalid_not_used_up(): void
    {
        [$a] = $this->product(price: 100);
        [$b, $bPlan] = $this->product(price: 100);
        $this->service->addItem($this->request(), ['product_id' => $b->id, 'plan_id' => $bPlan->id]);
        $code = $this->coupon(for: $a, value: '10', type: 2);
        Promotion::where('code', $code)->update(['uses' => 0]);

        $this->expectExceptionMessageIsOrContains(__('message.invalid_coupon_code'));

        $this->service->applyCoupon($this->request(), $code);
    }

    public function test_a_coupon_bigger_than_the_price_leaves_no_tax_and_nothing_to_pay(): void
    {
        // QA case: 48.60 with a fixed 70 coupon at 3% — the coupon is capped at 48.60, so 0 is taxed.
        [$a, $aPlan] = $this->product(price: 48.60);
        $this->service->addItem($this->request(), ['product_id' => $a->id, 'plan_id' => $aPlan->id]);
        Cart::where('user_id', $this->user->id)->update(['currency' => 'USD']);
        $this->service->applyCoupon($this->request(), $this->coupon(for: $a, value: '70', type: 2));
        $this->flatTax(3);

        $summary = $this->cartSummary();

        $this->assertEqualsWithDelta(48.60, $summary['discount'], 0.001);
        $this->assertEqualsWithDelta(0.0, $summary['tax_total'], 0.001);
        $this->assertEqualsWithDelta(0.0, $summary['grand_total'], 0.001);
    }

    public function test_only_the_coupons_product_gets_the_lower_tax(): void
    {
        // A: 100 with 50% off → taxed on 50; B: 200, no coupon → taxed on 200. Tax = 9 + 36.
        [$a, $aPlan] = $this->product(price: 100);
        [$b, $bPlan] = $this->product(price: 200);
        $this->service->addItem($this->request(), ['product_id' => $a->id, 'plan_id' => $aPlan->id]);
        $this->service->addItem($this->request(), ['product_id' => $b->id, 'plan_id' => $bPlan->id]);
        Cart::where('user_id', $this->user->id)->update(['currency' => 'USD']);
        $this->service->applyCoupon($this->request(), $this->coupon(for: $a, value: '50%'));
        $this->flatTax(18);

        $summary = $this->cartSummary();

        $this->assertEqualsWithDelta(45.0, $summary['tax_total'], 0.001);
        $this->assertEqualsWithDelta(300 - 50 + 45, $summary['grand_total'], 0.001);
    }

    public function test_without_a_coupon_tax_is_unchanged(): void
    {
        [$a, $aPlan] = $this->product(price: 100);
        $this->service->addItem($this->request(), ['product_id' => $a->id, 'plan_id' => $aPlan->id]);
        $this->flatTax(18);

        $summary = $this->cartSummary();

        $this->assertEqualsWithDelta(18.0, $summary['tax_total'], 0.001);
        $this->assertEqualsWithDelta(118.0, $summary['grand_total'], 0.001);
    }

    // ── Batch 3 #14: quantity is locked at 1 unless the product allows changing it ──

    public function test_quantity_cannot_be_changed_on_a_product_that_forbids_it(): void
    {
        [$product, $plan] = $this->product(price: 100);
        $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id, 'quantity' => 5]);
        $this->assertSame(1, $this->line()->quantity); // sent quantity ignored

        $this->service->updateItem($this->request(), $this->line()->id, ['quantity' => 4]);
        $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id]); // added again

        $line = $this->line();
        $this->assertSame(1, $line->quantity);
        $this->assertEqualsWithDelta(100.0, $line->lineTotal(), 0.001); // one licence, charged once
    }

    public function test_quantity_still_changes_on_a_product_that_allows_it(): void
    {
        [$product, $plan] = $this->product(price: 100);
        \DB::table('products')->where('id', $product->id)->update(['can_modify_quantity' => 1]);
        $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id]);
        $this->service->updateItem($this->request(), $this->line()->id, ['quantity' => 3]);
        $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id]);

        $this->assertSame(4, $this->line()->quantity);
    }

    public function test_the_guest_cart_locks_quantity_too(): void
    {
        [$product, $plan] = $this->product(price: 100);
        $guest = new GuestCart;
        $guest->clear();

        $this->service->addItem($this->request(guest: true), ['product_id' => $product->id, 'plan_id' => $plan->id, 'quantity' => 5]);
        $this->service->addItem($this->request(guest: true), ['product_id' => $product->id, 'plan_id' => $plan->id]);

        $this->assertSame(1, $guest->all()[0]['quantity']);
    }

    // ── Batch 3 #5: the cart's own pending invoice doesn't use up its coupon ──

    private function singleUseCouponInCart(): array
    {
        [$a, $aPlan] = $this->product(price: 100);
        $this->service->addItem($this->request(), ['product_id' => $a->id, 'plan_id' => $aPlan->id]);
        $code = $this->coupon(for: $a, value: '10%');
        Promotion::where('code', $code)->update(['uses' => 1]);

        return [$code, Cart::where('user_id', $this->user->id)->firstOrFail()];
    }

    public function test_a_single_use_coupon_survives_the_customers_own_re_checkout(): void
    {
        [$code, $cart] = $this->singleUseCouponInCart();
        // Placed the order once: their own pending invoice now carries the code.
        $own = Invoice::factory()->create(['user_id' => $this->user->id, 'status' => 'pending', 'coupon_code' => $code]);
        $cart->update(['invoice_id' => $own->id]);

        $this->service->applyCoupon($this->request(), $code);

        $this->assertSame($code, $cart->fresh()->coupon_code);
    }

    public function test_someone_elses_use_still_counts(): void
    {
        [$code] = $this->singleUseCouponInCart();
        Invoice::factory()->create(['status' => 'success', 'coupon_code' => $code]);

        $this->expectExceptionMessageIsOrContains(__('message.usage-of-code-completed'));
        $this->service->applyCoupon($this->request(), $code);
    }
}
