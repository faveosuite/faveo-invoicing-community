<?php

namespace Tests\Unit\Client\Cart;

use App\Http\Controllers\Front\BaseCartController;
use App\Http\Controllers\Front\ClientController;
use App\Model\Product\ProductGroup;
use App\Http\Controllers\Order\OrderSearchController;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use App\Model\License\LicensePermission;
use App\Model\License\LicenseType;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\User;
use Mockery;
use Tests\DBTestCase;
use Spatie\Html\Html;
class BaseCartControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classObject = new BaseCartController();
        $this->classObject1=new ClientController();
        $this->request = app(Request::class);
//        $this->html1 = new Html($this->request);
        $this->html = Mockery::mock(Html::class, [$this->request])->makePartial();
        $this->html->shouldReceive('token')->andReturn('mocked-token');
        $this->app->instance(Html::class, $this->html);
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
            'id' => $plan->id,
            'name' => $product->name,
            'price' => 1000,
            'quantity' => 1,
            'attributes' => ['currency' => $currency, 'symbol' => $currency, 'agents' => 10],
        ]);
        $response = $this->call('POST', 'update-qty', [
            'productid' => $product->id, 'planid' => $plan->id,
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
            'id' => $plan->id,
            'name' => $product->name,
            'price' => 1000,
            'quantity' => 2,
            'attributes' => ['currency' => $currency, 'symbol' => $currency, 'agents' => 10],
        ]);
        $response = $this->call('POST', 'reduce-product-qty', [
            'productid' => $product->id,
            'planid' => $plan->id,
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


    /** @group order  */
    public function test_successful_when_license_mocked()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_name' => 'Helpdesk Advance',
            'regular_price' => 10000,
            'quantity' => 1,
            'tax_name' => 'CGST+SGST',
            'tax_percentage' => 18,
            'subtotal' => 11800,
            'domain' => 'faveo.com',
            'plan_id' => 1,
        ]);
        $order = Order::factory()->create(['invoice_id' => $invoice->id,
            'invoice_item_id' => $invoiceItem->id, 'client' => $user->id, 'product' => $product->id]);
        $subscription = Subscription::create(['user_id' => $user->id, 'order_id' => $order->id, 'product_id' => $product->id, 'version' => 'v3.0.0', 'is_subscribed' => '1', 'autoRenew_status' => '1']);
        $serialKey = 'eertrertyuhgbvfdrgtyujhnbvfdrethgbf';
        $productId = 1;
        $mock = Mockery::mock(\App\Http\Controllers\License\LicenseController::class);
        $mock->shouldReceive('searchInstallationPath')
            ->withAnyArgs()
            ->once()
            ->andReturn(['path' => '/mocked']);

        $this->app->instance(\App\Http\Controllers\License\LicenseController::class, $mock);

        $response=$this->getPrivateMethod($this->classObject1,'getOrder',[$order->id]);
        $this->assertEquals('themes.default1.front.clients.show-order', $response->getName());
    }



    /** @group store */
    public function test_store_has_groups()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group = ProductGroup::create(['name' => 'consumer-products', 'hidden' => 0, 'pricing_templates_id' => 1]);
        $product = Product::factory()->create(['group' => $group->id]);
        $plan = Plan::factory()->create(['product' => $product->id]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id]);
        $response = $this->withSession(['store'=>1])->get( 'group/'.$group->pricing_templates_id.'/'.$group->id.'/');
        $data=$response->original->gatherData();
        $this->assertStringContainsString('<input type="submit" value="Order Now" class="btn btn-dark btn-modern buttonsale">', $data['templates']);
        $response->assertStatus(200);
        $response->assertViewHas('templates');
        $response->assertViewIs('themes.default1.common.template.shoppingcart');
    }

    public function test_generating_string(){
        $response=$this->getPrivateMethod($this->classObject1,'generateMerchantRandomString');
        dd($response);
    }
}
