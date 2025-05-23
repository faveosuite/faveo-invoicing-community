<?php

namespace Tests\Unit\Client\Cart;

use App\Http\Controllers\Front\BaseCartController;
use App\Http\Controllers\Front\CheckoutController;
use App\Model\Common\Setting;
use App\Model\License\LicensePermission;
use App\Model\License\LicenseType;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Payment\TaxOption;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
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
        $plan1 = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);
        $planPrice = PlanPrice::create(['plan_id' => $plan1->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);

        $currency = 'INR';
        \Cart::add([
            'id' => $plan1->id,
            'name' => $product->name,
            'price' => 1000,
            'quantity' => 1,
            'attributes' => ['currency' => $currency, 'symbol' => $currency, 'agents' => 10],
        ]);
        $response = $this->getPrivateMethod($this->classObject, 'getCartValues', [$plan1->id, true]);
        $this->assertEquals($response['agtqty'], 9); //Reduced to half
        $this->assertEquals($response['price'], 900); //Reduced to half
    }

    #[Group('quantity')]
    public function test_getCartValues_calculateAgentQtyPriceOfCartWhenIncreasinAgtAllowed_returnArrayToBeAdded()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
        $plan1 = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);
        $planPrice = PlanPrice::create(['plan_id' => $plan1->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);

        $currency = 'INR';
        \Cart::add([
            'id' => $plan1->id,
            'name' => $product->name,
            'price' => 1000,
            'quantity' => 1,
            'attributes' => ['currency' => $currency, 'symbol' => $currency, 'agents' => 10],
        ]);
        $response = $this->getPrivateMethod($this->classObject, 'getCartValues', [$plan1->id]);
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
        $plan1 = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);
        $planPrice = PlanPrice::create(['plan_id' => $plan1->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);

        $currency = 'INR';
        \Cart::add([
            'id' => $plan1->id,
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

    public function test_cart_has_same_product_with_different_plans()
    {
        $this->getLoggedInUser();
        $this->withoutMiddleware();
        $product = Product::factory()->create();
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

        $product1 = Product::factory()->create();
        $plan1 = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);
        $planPrice = PlanPrice::create(['plan_id' => $plan1->id, 'currency' => 'INR', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);
        $currency1 = 'INR';
        \Cart::add([
            'id' => $plan1->id,
            'name' => $product1->name,
            'price' => 1000,
            'quantity' => 2,
            'attributes' => ['currency' => $currency1, 'symbol' => $currency1, 'agents' => 10],
        ]);

        foreach (\Cart::getContent() as $cart) {
            $this->assertEquals($cart->quantity, 2);
        }
    }

    public function test_when_we_session_not_set_payment_gateway_not_selected()
    {
        $user = User::factory()->create(['billing_pay_balance' => 0]);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::factory()->create();
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

        $response = $this->withSession(['nothingLeft' => 1, 'discount' => 300])->call('post', 'checkout-and-pay', ['cost' => 0, 'payment_gateway' => '']);
        $response->assertSessionHasErrors('payment_gateway');
    }

    public function test_post_checkout_cart()
    {
        $user = User::factory()->create(['billing_pay_balance' => 0]);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $licensetype = LicenseType::create(['name' => 'DevelopmentLicense']);
        $licensepermissiontype = LicensePermission::create(['Can be Downloaded']);
        LicensePermission::create(['Generate License Expiry Date']);
        LicensePermission::create(['Generate Updates Expiry Date']);
        LicensePermission::create(['Allow Downloads Before Updates Expire']);
        $permissionid = [
            0 => '1',
            1 => '2',
            2 => '3',
            3 => '4',
            6 => '6',
        ];
        $licensetype->permissions()->attach($permissionid);

        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct', 'type' => $licensetype->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id]);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '']);
        $currency = 'INR';
        $taxes = TaxOption::create(['tax_enable' => 0, 'inclusive' => 0, 'shop_inclusive' => 0, 'cart_inclusive' => 0, 'rounding' => 1]);
        $payment = Payment::create(['user_id' => $user->id, 'payment_method' => 'Credit Balance', 'payment_status' => 'success', 'amt_to_credit' => 10000]);
        Setting::create(['sending_status' => 0, 'mailchimp_status' => 0]);
        \Cart::add([
            'id' => 55,
            'name' => $product->name,
            'price' => 1000,
            'quantity' => 2,
            'attributes' => ['currency' => $currency, 'symbol' => $currency, 'agents' => 10],
            'associatedModel' => $product,
        ]);
        $checkoutController = new CheckoutController();
        $checkoutController->getAttributes(\Cart::getContent());
        $response = $this->withSession(['nothingLeft' => 0, 'discount' => 300, 'priceRemaining' => 1, 'priceToBePaid' => 0])->call('post', 'checkout-and-pay', ['cost' => 0, 'payment_gateway' => '', 'invoice_id' => 0]);
        $response->assertStatus(302);
        $response->assertRedirect('checkout');
        $amount = Payment::where('user_id', \Auth::user()->id)->where('payment_status', 'success')->where('payment_method', 'Credit Balance')->value('amt_to_credit');
        $this->assertEquals(10300, $amount);
    }
}
