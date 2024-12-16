<?php

namespace Tests\Unit\Client\Cart;

use App\Http\Controllers\Front\CartController;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class CartControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classObject = new CartController();
    }

    #[Group('cart')]
    public function test_addProduct_addNewProductToCart_returnArrayOfProductDetails()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create(['name' => 'Helpdesk Advance']);
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);
        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);

        $response = $this->classObject->addProduct($product->id);
        $this->assertStringContainsSubstring($response['name'], 'Helpdesk Advance');
    }

    #[Group('cart')]
    public function test_planCost_getCostForProductPlan_returnCost()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);
        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);

        $response = $this->classObject->planCost($product->id, $this->user->id, $plan->id);
        $this->assertEquals($response, 1000);
    }

    #[Group('cart')]
    public function test_planCost_whenPlanIdNotRelatedToProductPassed_throwsException()
    {
        $this->expectException(\Exception::class);
        $errors = session('errors');
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);
        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);

        $response = $this->classObject->planCost($product->id, $this->user->id, 1);
    }

    #[Group('cart')]
    public function test_planCost_whenPlanIdNotPassed_returnsProductCost()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);
        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);

        $response = $this->classObject->planCost($product->id, $this->user->id);
        $this->assertEquals($response, 1000);
    }

    #[Group('cart')]
    public function test_planCost_whenPlanIdForOtherProductPassed_throwsException()
    {
        $this->expectException(\Exception::class);
        $errors = session('errors');
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create(['name' => 'Test Product']);
        $plan1 = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product1->id, 'days' => 366]);
        $plan2 = Plan::create(['name' => 'SD Plan 1 year', 'product' => $product2->id, 'days' => 366]);

        $planPrice1 = PlanPrice::create(['plan_id' => $plan1->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);

        $planPrice2 = PlanPrice::create(['plan_id' => $plan2->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);

        $response = $this->classObject->planCost($product1->id, $this->user->id, $plan2->id);
    }

    #[Group('cart')]
    public function test_cartRemove_removeAnItemFromCart_returnEmptyCart()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();

        $taxCondition = new \Darryldecode\Cart\CartCondition([
            'name' => 'GST', 'type' => 'tax',
            'value' => 5,
        ]);
        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => 1000,
            'quantity' => 1,
            'attributes' => [],
            'conditions' => $taxCondition,
        ]);

        $response = $this->call('POST', 'cart/remove', [
            'id' => $product->id,
        ]);
        $this->assertEquals($response->getContent(), 'success');
        $this->assertCount(0, \Cart::getContent());
    }

    #[Group('cart')]
    public function test_cartRemove_clearsCart_returnEmptyCart()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create(['name' => 'Test Product']);

        $taxCondition1 = new \Darryldecode\Cart\CartCondition([
            'name' => 'GST', 'type' => 'tax',
            'value' => 5,
        ]);
        $taxCondition2 = new \Darryldecode\Cart\CartCondition([
            'name' => 'VAT', 'type' => 'tax',
            'value' => 10,
        ]);
        \Cart::add([
            ['id' => $product1->id,
                'name' => $product1->name,
                'price' => 1000,
                'quantity' => 1,
                'attributes' => [],
                'conditions' => $taxCondition1,
            ],
            ['id' => $product2->id,
                'name' => $product2->name,
                'price' => 1000,
                'quantity' => 1,
                'attributes' => [],
                'conditions' => $taxCondition2,
            ],
        ]);
        $response = $this->call('POST', 'cart/clear');
        $this->assertCount(0, \Cart::getContent());
    }
}
