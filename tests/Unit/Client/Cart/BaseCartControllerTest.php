<?php

namespace Tests\Unit\Client\Cart;

use App\Http\Controllers\Front\BaseCartController;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\DBTestCase;

class BaseCartControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classObject = new BaseCartController();
    }

    #[Group('quantity')]
    public function test_getCartValues_calculatesAgentQtyPriceOfCartWhenReducingAgtAllowed_returnArrayToBeAdded()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $currency = 'INR';
        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => 1000,
            'quantity' => 1,
            'attributes' => ['currency' => $currency, 'symbol' => $currency, 'agents' => 10],
        ]);
        $response = $this->getPrivateMethod($this->classObject, 'getCartValues', [$product->id, true]);
        $this->assertEquals($response['agtqty'], 9); //Reduced to half
        $this->assertEquals($response['price'], 900); //Reduced to half
    }

    #[Group('quantity')]
    public function test_getCartValues_calculateAgentQtyPriceOfCartWhenIncreasinAgtAllowed_returnArrayToBeAdded()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $currency = 'INR';
        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => 1000,
            'quantity' => 1,
            'attributes' => ['currency' => $currency, 'symbol' => $currency, 'agents' => 10],
        ]);
        $response = $this->getPrivateMethod($this->classObject, 'getCartValues', [$product->id]);
        $this->assertEquals($response['agtqty'], 11); //Doubled
        $this->assertEquals($response['price'], 1100); //Doubled
    }

    #[Group('quantity')]
    public function test_getCartValues_calculateAgentQtyPriceOfCartWhenInvalidProductPassed_throwsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Product not present in cart.');
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create(['name' => 'SD Enterprise']);
        $currency = 'INR';
        \Cart::add([
            'id' => $product1->id,
            'name' => $product1->name,
            'price' => 1000,
            'quantity' => 1,
            'attributes' => ['currency' => $currency, 'symbol' => $currency, 'agents' => 10],
        ]);
        $response = $this->getPrivateMethod($this->classObject, 'getCartValues', [$product2->id]);
    }

    #[Group('quantity')]
    //re think this case
//    public function test_updateAgentQty_updatesCart_returnsUpdatedCart()
//    {
//        $this->getLoggedInUser();
//        $this->withoutMiddleware();
//        $product = Product::factory()->create(['can_modify_agent' => 1]);
//        $currency = 'INR';
//        \Cart::add([
//            'id' => $product->id,
//            'name' => $product->name,
//            'price' => 1000,
//            'quantity' => 1,
//            'attributes' => ['currency' => $currency, 'symbol' => $currency, 'agents' => 10],
//        ]);
//        $response = $this->call('POST', 'update-agent-qty', [
//            'productid' => $product->id,
//        ]);
//        foreach (\Cart::getContent() as $cart) {
//            $this->assertEquals($cart->price, 1900);
//            $this->assertEquals($cart->attributes->agents, 19);
//        }
//    }

    #[Group('quantity')]
    public function test_updateAgentQty_updatesCartWhenModifyingAgentNotAllowed_returnsSameCartValues()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $currency = 'INR';
        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => 1000,
            'quantity' => 1,
            'attributes' => ['currency' => $currency, 'symbol' => $currency, 'agents' => 10],
        ]);
        $response = $this->call('POST', 'update-agent-qty', [
            'productid' => $product->id,
        ]);
        foreach (\Cart::getContent() as $cart) {
            $this->assertEquals($cart->price, 1000);
            $this->assertEquals($cart->attributes->agents, 10);
        }
    }

    #[Group('quantity')]
    public function test_updateProductQty_updatesCartWhenModifyingQtyAllowed_returnsUpdatedCart()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create(['can_modify_quantity' => 1]);
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);
        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);
        $currency = 'INR';
        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => 1000,
            'quantity' => 1,
            'attributes' => ['currency' => $currency, 'symbol' => $currency, 'agents' => 10],
        ]);
        $response = $this->call('POST', 'update-qty', [
            'productid' => $product->id,
        ]);
        foreach (\Cart::getContent() as $cart) {
            $this->assertEquals($cart->quantity, 2);
        }
    }

    #[Group('quantity')]
    public function test_updateProductQty_updatesCartWhenModifyingQtyNotAllowed_throwsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot Modify Quantity');
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);
        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);
        $currency = 'INR';
        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => 1000,
            'quantity' => 1,
            'attributes' => ['currency' => $currency, 'symbol' => $currency, 'agents' => 10],
        ]);
        $this->classObject->updateProductQty(new Request(['productid' => $product->id]));
    }

    #[Group('quantity')]
    public function test_reduceProductQty_reduceCartQtyWhenModifyingQtyAllowed_returnsUpdatedCart()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create(['can_modify_quantity' => 1]);
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);
        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);
        $currency = 'INR';
        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => 1000,
            'quantity' => 2,
            'attributes' => ['currency' => $currency, 'symbol' => $currency, 'agents' => 10],
        ]);
        $response = $this->call('POST', 'reduce-product-qty', [
            'productid' => $product->id,
        ]);
        foreach (\Cart::getContent() as $cart) {
            $this->assertEquals($cart->quantity, 1);
        }
    }

    #[Group('quantity')]
    public function test_reduceProductQty_updatesCartWhenModifyingQtyNotAllowed_throwsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot Modify Quantity');
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);
        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);
        $currency = 'INR';
        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => 1000,
            'quantity' => 2,
            'attributes' => ['currency' => $currency, 'symbol' => $currency, 'agents' => 10],
        ]);
        $this->classObject->reduceProductQty(new Request(['productid' => $product->id]));
    }
}
