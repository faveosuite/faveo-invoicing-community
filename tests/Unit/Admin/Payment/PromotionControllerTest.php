<?php

namespace Tests\Unit\Admin\Payment;

use App\Http\Controllers\Payment\PromotionController;
use App\Model\Order\Invoice;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Payment\Promotion;
use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class PromotionControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classObject = new PromotionController();
    }

    #[Group('promotion')]
    public function test_getPromotionDetails_whenRandomCodePassed_throwsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid Coupon code');
        $promotion = $this->classObject->getPromotionDetails('RANDOMCODE');
    }

    #[Group('promotion')]
    public function test_getPromotionDetails_whenCodeHasExpired_throwsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Usage of Code Expired');
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 1, 'value' => 10, 'uses' => 10, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2020']);
        $promotion = $this->classObject->getPromotionDetails('FAVEOCOUPON');
    }

    #[Group('promotion')]
    public function test_getPromotionDetails_whenProductIsNotLinkedToCode_throwsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('There is  no product related to this code');
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $promotion = Promotion::create(['code' => 'FAVEOCOUPON',
            'type' => 1,
            'uses' => '100',
            'value' => '100',
            'start' => '2017-06-30 00:00:00',
            'expiry' => '2017-07-30 00:00:00',

        ]);
        $promotion = $this->classObject->getPromotionDetails('FAVEOCOUPON');
    }

    #[Group('promotion')]
    public function test_getPromotionDetails_whenUsageCountHasExpired_throwsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Usage of code Completed');
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $invoice = Invoice::factory()->count(3)->create(['user_id' => $this->user->id, 'coupon_code' => 'FAVEOCOUPON']);

        $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 1, 'value' => 10, 'uses' => 2, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        $promotion = $this->classObject->getPromotionDetails('FAVEOCOUPON');
    }

    #[Group('promotion')]
    public function test_getPromotionDetails_whenValidCodePassed_returnsSuccess()
    {
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 1, 'value' => 10, 'uses' => 10, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        $promotion = $this->classObject->getPromotionDetails('FAVEOCOUPON');
        $this->assertStringContainsSubstring($promotion->code, 'FAVEOCOUPON');
    }

    #[Group('promotion')]
    public function test_findCostAfterDiscount_whenCodeTypeIsInPercents_returnsDiscountedPrice()
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);

        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);

        $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 1, 'value' => 10, 'uses' => 10, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        $promotion = Promotion::orderBy('id', 'desc')->first();
        $promotion = $this->classObject->findCostAfterDiscount($promotion->id, $product->id, $this->user->id);
        $this->assertEquals($promotion, 900); //10% dicount on 1000
    }

    #[Group('promotion')]
    public function test_findCostAfterDiscount_whenCodeTypeIsFixedAmount_returnsDiscountedPrice()
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);

        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);

        $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 2, 'value' => 10, 'uses' => 10, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        $promotion = Promotion::orderBy('id', 'desc')->first();
        $promotion = $this->classObject->findCostAfterDiscount($promotion->id, $product->id, $this->user->id);
        $this->assertEquals($promotion, 990); //Rs 10 dicount on 1000
    }

    #[Group('promotion')]
    public function test_checkCode_whenFixedAmtCouponCodeEnteredWithCartConditions_returnsUpdatedCartPrice()
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);

        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);

        $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 2, 'value' => 10, 'uses' => 10, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        $promotion = Promotion::orderBy('id', 'desc')->first();
        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $planPrice->add_price,
            'quantity' => 1,
            'attributes' => [],
        ]);
        $promotion = $this->classObject->checkCode('FAVEOCOUPON');
        foreach (\Cart::getContent() as $cart) {
            $this->assertEquals($cart->getPriceSum(), 990); //Rs 10 dicount on Cart subtotal
        }
    }

    #[Group('promotion')]
    public function test_checkCode_whenPercentCouponCodeEnteredWithCartConditions_returnsUpdatedCartPrice()
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);

        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);

        $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 1, 'value' => 10, 'uses' => 10, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        $promotion = Promotion::orderBy('id', 'desc')->first();
        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $planPrice->add_price,
            'quantity' => 1,
            'attributes' => [],
        ]);
        $promotion = $this->classObject->checkCode('FAVEOCOUPON');
        foreach (\Cart::getContent() as $cart) {
            $this->assertEquals($cart->getPriceSum(), 900); // 10% dicount on Cart subtotal
        }
    }

    #[Group('promotion')]
    public function test_checkCode_whenCouponCodeIsEneteredForNonDiscountedProduct_throwsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid Coupon code');
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create(['name' => 'Test Product']);
        $plan1 = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product1->id, 'days' => 366]);
        $plan2 = Plan::create(['name' => 'SD Plan 1 year', 'product' => $product2->id, 'days' => 366]);

        $planPrice = PlanPrice::create(['plan_id' => $plan1->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);

        $this->call('POST', 'promotions', ['code' => 'FAVEO', 'type' => 1, 'value' => 10, 'uses' => 10, 'applied' => $product1->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        $promotion = Promotion::orderBy('id', 'desc')->first();
        \Cart::add([
            'id' => $product2->id,
            'name' => $product2->name,
            'price' => $planPrice->add_price,
            'quantity' => 1,
            'attributes' => [],
        ]);
        $promotion = $this->classObject->checkCode('FAVEOCOUPON');
    }

    #[Group('promotion')]
    public function test_checkCode_whenCouponCodeIsEneteredTwiceInSameSession_throwsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Coupon code has already been applied');
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);

        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);

        $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 1, 'value' => 10, 'uses' => 10, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        $promotion = Promotion::orderBy('id', 'desc')->first();
        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $planPrice->add_price,
            'quantity' => 1,
            'attributes' => [],
        ]);
        for ($i = 0; $i <= 1; $i++) {
            $promotion = $this->classObject->checkCode('FAVEOCOUPON');
        }
    }

    #[Group('promotion')]
    public function test_store_saveNewPromotionCode_returnsSuccessMessage()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $promotion = $this->call('POST', 'promotions', ['code' => 'FAVEOCOUPON', 'type' => 1, 'value' => 10, 'uses' => 2, 'applied' => $product->id, 'start' => '08/01/2020', 'expiry' => '08/15/2050']);
        $promotion->assertSessionHas('success');
    }
}
