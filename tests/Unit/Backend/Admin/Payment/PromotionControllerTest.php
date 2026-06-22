<?php

namespace Tests\Unit\Backend\Admin\Payment;

use App\Http\Controllers\Payment\PromotionController;
use App\Model\Order\Invoice;
use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Payment\Promotion;
use App\Model\Product\Product;
use Cart;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Session;
use Tests\DBTestCase;

class PromotionControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classObject = new PromotionController;
        Currency::where('code', 'INR')->update(['status' => 1]);
    }

    #[Group('promotion')]
    public function test_get_promotion_details_when_random_code_passed_throws_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid Coupon code');
        $this->classObject->getPromotionDetails('RANDOMCODE');
    }

    #[Group('promotion')]
    public function test_get_promotion_details_when_code_has_expired_throws_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Usage of Code Expired');
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 1, 'value' => 10, 'uses' => 10, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2020']);
        $this->classObject->getPromotionDetails('FAVEOCOUPON');
    }

    #[Group('promotion')]
    public function test_get_promotion_details_when_product_is_not_linked_to_code_throws_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('There is  no product related to this code');
        $this->withoutMiddleware();
        Product::factory()->create();
        Promotion::create(['code' => 'FAVEOCOUPON',
            'type' => 1,
            'uses' => '100',
            'value' => '100',
            'start' => '2017-06-30 00:00:00',
            'expiry' => '2017-07-30 00:00:00',

        ]);
        $this->classObject->getPromotionDetails('FAVEOCOUPON');
    }

    #[Group('promotion')]
    public function test_get_promotion_details_when_usage_count_has_expired_throws_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Usage of code Completed');
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        Invoice::factory()->count(3)->create(['user_id' => $this->user->id, 'coupon_code' => 'FAVEOCOUPON']);

        $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 1, 'value' => 10, 'uses' => 2, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        $this->classObject->getPromotionDetails('FAVEOCOUPON');
    }

    #[Group('promotion')]
    public function test_get_promotion_details_when_valid_code_passed_returns_success(): void
    {
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 1, 'value' => 10, 'uses' => 10, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        $promotion = $this->classObject->getPromotionDetails('FAVEOCOUPON');
        $this->assertStringContainsSubstring($promotion->code, 'FAVEOCOUPON');
    }

    #[Group('promotion')]
    public function test_find_cost_after_discount_when_code_type_is_in_percents_returns_discounted_price(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);

        PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);
        Session::put('plan', $plan->id);
        $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 1, 'value' => 10, 'uses' => 10, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        $promotion = Promotion::orderBy('id', 'desc')->first();
        $promotion = $this->classObject->findCostAfterDiscount($promotion->id, $product->id, $this->user->id);
        $this->assertEquals($promotion, 900); // 10% dicount on 1000
    }

    #[Group('promotion')]
    public function test_find_cost_after_discount_when_code_type_is_fixed_amount_returns_discounted_price(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);

        PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);
        Session::put('plan', $plan->id);
        $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 2, 'value' => 10, 'uses' => 10, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        $promotion = Promotion::orderBy('id', 'desc')->first();
        $promotion = $this->classObject->findCostAfterDiscount($promotion->id, $product->id, $this->user->id);
        $this->assertEquals($promotion, 990); // Rs 10 dicount on 1000
    }

    #[Group('promotion')]
    public function test_check_code_when_fixed_amt_coupon_code_entered_with_cart_conditions_returns_updated_cart_price(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);

        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);
        Session::put('plan', $plan->id);
        $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 2, 'value' => 10, 'uses' => 10, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        Promotion::orderBy('id', 'desc')->first();
        Cart::add([
            'id' => $plan->id,
            'name' => $product->name,
            'price' => $planPrice->add_price,
            'quantity' => 1,
            'attributes' => [],
            'associatedModel' => $product,
        ]);
        $this->classObject->checkCode('FAVEOCOUPON');
        foreach (Cart::getContent() as $cart) {
            $this->assertEquals($cart->getPriceSum(), 990); // Rs 10 dicount on Cart subtotal
        }
    }

    #[Group('promotion')]
    public function test_check_code_when_percent_coupon_code_entered_with_cart_conditions_returns_updated_cart_price(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);

        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);
        Session::put('plan', $plan->id);
        $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 1, 'value' => 10, 'uses' => 10, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        Promotion::orderBy('id', 'desc')->first();
        Cart::add([
            'id' => $plan->id,
            'name' => $product->name,
            'price' => $planPrice->add_price,
            'quantity' => 1,
            'attributes' => [],
            'associatedModel' => $product,
        ]);
        $this->classObject->checkCode('FAVEOCOUPON');
        foreach (Cart::getContent() as $cart) {
            $this->assertEquals($cart->getPriceSum(), 900); // 10% dicount on Cart subtotal
        }
    }

    #[Group('promotion')]
    public function test_check_code_when_coupon_code_is_enetered_for_non_discounted_product_throws_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid Coupon code');
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create(['name' => 'Test Product']);
        $plan1 = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product1->id, 'days' => 366]);
        Plan::create(['name' => 'SD Plan 1 year', 'product' => $product2->id, 'days' => 366]);

        $planPrice = PlanPrice::create(['plan_id' => $plan1->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);

        $this->call('POST', 'promotions', ['code' => 'FAVEO', 'type' => 1, 'value' => 10, 'uses' => 10, 'applied' => $product1->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        Promotion::orderBy('id', 'desc')->first();
        Cart::add([
            'id' => $plan1->id,
            'name' => $product2->name,
            'price' => $planPrice->add_price,
            'quantity' => 1,
            'attributes' => [],
        ]);
        $this->classObject->checkCode('FAVEOCOUPON');
    }

    #[Group('promotion')]
    public function test_check_code_when_coupon_code_is_enetered_twice_in_same_session_throws_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Coupon code has already been applied');
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);

        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);
        Session::put('plan', $plan->id);
        $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 1, 'value' => 10, 'uses' => 10, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        $promotion = Promotion::orderBy('id', 'desc')->first();
        Cart::add([
            'id' => $plan->id,
            'name' => $product->name,
            'price' => $planPrice->add_price,
            'quantity' => 1,
            'attributes' => [],
            'associatedModel' => $product,

        ]);
        for ($i = 0; $i <= 1; $i++) {
            $promotion = $this->classObject->checkCode('FAVEOCOUPON');
        }
    }

    #[Group('promotion')]
    public function test_store_save_new_promotion_code_returns_success_message(): void
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $promotion = $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 1, 'value' => 10, 'uses' => 2, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        $promotion->assertSessionHas('success');
    }
}
