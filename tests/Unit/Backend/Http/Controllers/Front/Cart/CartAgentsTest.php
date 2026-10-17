<?php

namespace Tests\Unit\Backend\Http\Controllers\Front\Cart;

use App\Http\Controllers\Front\Cart\CartService;
use App\Http\Controllers\Front\Cart\GuestCart;
use App\Http\Controllers\Order\ExtendedOrderController;
use App\Model\Cart\Cart;
use App\Model\Cart\CartItem;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Services\Payment\InvoicePaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Mockery;
use Tests\DBTestCase;

/**
 * A plan's price covers the plan's own agent count (plan_prices.no_of_agents); a cart line
 * gets that count, and only agent-adjustable products may change it, pro rata — the rule
 * the development branch's cart and today's renewals use. B54: Freelancer (a 2-agent plan)
 * was bought with 1 agent because the store hard-coded agents: 1.
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
        $request->setLaravelSession(app('session.store'));
        if (! $guest) {
            $request->setUserResolver(fn () => $this->user);
        }

        return $request;
    }

    /** @return array{0: Product, 1: Plan} */
    private function product(bool $canModifyAgents, ?string $planAgents, float $price = 100): array
    {
        $product = Product::factory()->create(['can_modify_agent' => (int) $canModifyAgents]);
        $plan = Plan::factory()->create(['product' => $product->id]);
        foreach (['USD', 'INR'] as $currency) {
            PlanPrice::factory()->create([
                'plan_id' => $plan->id, 'currency' => $currency, 'add_price' => $price,
                'offer_price' => 0, 'no_of_agents' => $planAgents,
            ]);
        }

        return [$product, $plan];
    }

    private function line(): CartItem
    {
        $cart = $this->service->resolveCart($this->request());
        $cart->update(['currency' => 'USD']);

        return $this->service->resolveCart($this->request())->items->sole();
    }

    public function test_fixed_agent_plan_gets_the_plans_count_at_the_plan_price(): void
    {
        [$product, $plan] = $this->product(canModifyAgents: false, planAgents: '2');

        $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id]);

        $line = $this->line();
        $this->assertSame(2, $line->agents);
        $this->assertEqualsWithDelta(100.0, $line->lineTotal(), 0.001);
    }

    public function test_a_sent_agent_count_is_ignored_for_a_fixed_agent_plan(): void
    {
        [$product, $plan] = $this->product(canModifyAgents: false, planAgents: '2');

        // What the store used to send: agents: 1.
        $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id, 'agents' => 1]);

        $this->assertSame(2, $this->line()->agents);
    }

    public function test_unlimited_plan_gets_zero_agents_which_licenses_as_unlimited(): void
    {
        foreach (['0', null] as $planAgents) {
            Cart::where('user_id', $this->user->id)->delete();
            [$product, $plan] = $this->product(canModifyAgents: false, planAgents: $planAgents);

            $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id]);

            $line = $this->line();
            $this->assertSame(0, $line->agents);
            $this->assertEqualsWithDelta(100.0, $line->lineTotal(), 0.001);
        }

        // Licence serial's last four digits are the agent count; 0000 reads as Unlimited.
        $this->assertStringEndsWith('0000', (new ExtendedOrderController)->generateSerialKey($product->id, 0));
    }

    public function test_adjustable_plan_starts_at_the_plans_count_and_scales_pro_rata(): void
    {
        [$product, $plan] = $this->product(canModifyAgents: true, planAgents: '5', price: 100);

        $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id]);
        $line = $this->line();
        $this->assertSame(5, $line->agents);
        $this->assertEqualsWithDelta(100.0, $line->lineTotal(), 0.001);

        $this->service->updateItem($this->request(), $line->id, ['agents' => 7]);
        $this->assertEqualsWithDelta(140.0, $this->line()->lineTotal(), 0.001); // 100 / 5 × 7

        $this->service->updateItem($this->request(), $line->id, ['agents' => 1]);
        $this->assertEqualsWithDelta(20.0, $this->line()->lineTotal(), 0.001);
    }

    public function test_adjustable_plan_honours_an_agent_count_sent_on_add(): void
    {
        [$product, $plan] = $this->product(canModifyAgents: true, planAgents: '5', price: 100);

        $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id, 'agents' => 10]);

        $line = $this->line();
        $this->assertSame(10, $line->agents);
        $this->assertEqualsWithDelta(200.0, $line->lineTotal(), 0.001);
    }

    public function test_agents_cannot_be_changed_on_a_fixed_agent_plan(): void
    {
        [$product, $plan] = $this->product(canModifyAgents: false, planAgents: '2');
        $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id]);
        $line = $this->line();

        $this->service->updateItem($this->request(), $line->id, ['agents' => 1, 'quantity' => 3]);

        $line = $this->line();
        $this->assertSame(2, $line->agents);   // refused
        $this->assertSame(3, $line->quantity); // the rest of the update still applies
        $this->assertEqualsWithDelta(300.0, $line->lineTotal(), 0.001);
    }

    public function test_guest_cart_gets_the_same_agents_and_a_legacy_guest_row_is_corrected_on_merge(): void
    {
        [$product, $plan] = $this->product(canModifyAgents: false, planAgents: '2');
        $guest = new GuestCart;
        $guest->clear();

        $this->service->addItem($this->request(guest: true), ['product_id' => $product->id, 'plan_id' => $plan->id, 'agents' => 1]);
        $this->assertSame(2, $guest->all()[0]['agents']);

        // A guest row saved before this fix carries the old hard-coded 1.
        $guest->clear();
        $guest->add(['product_id' => $product->id, 'plan_id' => $plan->id, 'agents' => 1], 'USD');
        $this->service->mergeGuestCart($this->user);

        $this->assertSame(2, $this->line()->agents);
    }

    public function test_invoice_lines_carry_the_plans_agents_and_price(): void
    {
        [$product, $plan] = $this->product(canModifyAgents: true, planAgents: '4', price: 80);
        $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id, 'agents' => 6]);
        $cart = $this->service->resolveCart($this->request());
        $cart->update(['currency' => 'USD']);
        $cart = $this->service->resolveCart($this->request());

        $summary = (new \ReflectionMethod($this->service, 'summary'))->invoke($this->service, $cart, $this->user);
        $item = $summary['items'][0];

        $this->assertSame(6, $item['agents']);                              // → licence serial …0006
        $this->assertEqualsWithDelta(120.0, $item['regular_price'], 0.001); // 80 / 4 × 6
        $this->assertEqualsWithDelta(120.0, $item['subtotal'], 0.001);
        $this->assertEqualsWithDelta(120.0, $summary['subtotal'], 0.001);   // cart total agrees
    }

    // ── C2: the plan must be this product's own, and priced in the cart's currency ──

    private function assertRefused(callable $add): void
    {
        try {
            $add();
            $this->fail('Expected the cart to refuse this line.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
        $this->assertCount(0, $this->service->resolveCart($this->request())->items);
    }

    public function test_another_products_plan_is_refused(): void
    {
        [$paid] = $this->product(canModifyAgents: false, planAgents: '2', price: 500);
        [, $freePlan] = $this->product(canModifyAgents: false, planAgents: '2', price: 0);

        // The exploit: a paid product on someone else's free plan.
        $this->assertRefused(fn () => $this->service->addItem($this->request(), ['product_id' => $paid->id, 'plan_id' => $freePlan->id]));
    }

    public function test_a_plan_that_does_not_exist_is_refused(): void
    {
        [$product] = $this->product(canModifyAgents: false, planAgents: '2');

        $this->assertRefused(fn () => $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => 99999999]));
    }

    public function test_a_plan_with_no_price_in_the_carts_currency_is_refused(): void
    {
        [$product, $plan] = $this->product(canModifyAgents: false, planAgents: '2');
        $cartCurrency = $this->service->resolveCart($this->request())->currency;
        PlanPrice::where('plan_id', $plan->id)->where('currency', $cartCurrency)->delete();

        $this->assertRefused(fn () => $this->service->addItem($this->request(), ['product_id' => $product->id, 'plan_id' => $plan->id]));
    }

    public function test_a_guest_line_the_cart_now_refuses_is_dropped_on_login_not_thrown(): void
    {
        [$paid] = $this->product(canModifyAgents: false, planAgents: '2', price: 500);
        [, $freePlan] = $this->product(canModifyAgents: false, planAgents: '2', price: 0);
        [$good, $goodPlan] = $this->product(canModifyAgents: false, planAgents: '2');
        $guest = new GuestCart;
        $guest->clear();
        $guest->add(['product_id' => $paid->id, 'plan_id' => $freePlan->id], 'USD'); // saved before the fix
        $guest->add(['product_id' => $good->id, 'plan_id' => $goodPlan->id], 'USD');

        $this->service->mergeGuestCart($this->user); // must not throw — it runs inside login

        $items = $this->service->resolveCart($this->request())->items;
        $this->assertSame([$good->id], $items->pluck('product_id')->all());
    }

    // ── H4: a coupon discounts only the products it is linked to ──

    private function coupon(Product $for, string $value, int $type = 1): string
    {
        $code = 'T'.strtoupper(uniqid());
        $type = \App\Model\Payment\PromotionType::find($type) ? $type : \App\Model\Payment\PromotionType::firstOrCreate(['name' => 'Percentage'])->id;
        $promo = \App\Model\Payment\Promotion::create([
            'code' => $code, 'type' => $type, 'uses' => 100, 'value' => $value,
            'start' => now()->subDay()->toDateTimeString(), 'expiry' => now()->addDay()->toDateTimeString(),
        ]);
        \App\Model\Payment\PromoProductRelation::create(['promotion_id' => $promo->id, 'product_id' => $for->id]);

        return $code;
    }

    private function cartDiscount(): float
    {
        return (float) Cart::where('user_id', $this->user->id)->value('coupon_discount');
    }

    public function test_a_coupon_for_another_product_is_refused(): void
    {
        [$a] = $this->product(canModifyAgents: false, planAgents: '1', price: 100);
        [$b, $bPlan] = $this->product(canModifyAgents: false, planAgents: '1', price: 200);
        $this->service->addItem($this->request(), ['product_id' => $b->id, 'plan_id' => $bPlan->id]);

        try {
            $this->service->applyCoupon($this->request(), $this->coupon(for: $a, value: '10%'));
            $this->fail('Expected the coupon to be refused.');
        } catch (\Exception $e) {
            $this->assertSame(__('message.invalid_coupon_code'), $e->getMessage());
        }
        $this->assertNull(Cart::where('user_id', $this->user->id)->value('coupon_code'));
    }

    public function test_a_coupon_discounts_only_its_own_product_in_a_mixed_cart(): void
    {
        [$a, $aPlan] = $this->product(canModifyAgents: false, planAgents: '1', price: 100);
        [$b, $bPlan] = $this->product(canModifyAgents: false, planAgents: '1', price: 200);
        $this->service->addItem($this->request(), ['product_id' => $a->id, 'plan_id' => $aPlan->id]);
        $this->service->addItem($this->request(), ['product_id' => $b->id, 'plan_id' => $bPlan->id]);
        Cart::where('user_id', $this->user->id)->update(['currency' => 'USD']);

        $this->service->applyCoupon($this->request(), $this->coupon(for: $a, value: '10%'));

        $this->assertEqualsWithDelta(10.0, $this->cartDiscount(), 0.001); // 10% of A's 100 — not of the 300 cart
    }

    public function test_a_fixed_coupon_is_capped_at_its_products_price_and_dropped_when_that_product_leaves(): void
    {
        [$a, $aPlan] = $this->product(canModifyAgents: false, planAgents: '1', price: 100);
        [$b, $bPlan] = $this->product(canModifyAgents: false, planAgents: '1', price: 200);
        $this->service->addItem($this->request(), ['product_id' => $a->id, 'plan_id' => $aPlan->id]);
        $this->service->addItem($this->request(), ['product_id' => $b->id, 'plan_id' => $bPlan->id]);
        Cart::where('user_id', $this->user->id)->update(['currency' => 'USD']);

        $this->service->applyCoupon($this->request(), $this->coupon(for: $a, value: '486', type: 2));
        $this->assertEqualsWithDelta(100.0, $this->cartDiscount(), 0.001); // not 200 off the whole cart

        $aLine = CartItem::whereHas('cart', fn ($q) => $q->where('user_id', $this->user->id))->where('product_id', $a->id)->firstOrFail();
        $this->service->removeItem($this->request(), $aLine->id);

        $this->assertNull(Cart::where('user_id', $this->user->id)->value('coupon_code'));
        $this->assertSame(0.0, $this->cartDiscount());
    }
}
