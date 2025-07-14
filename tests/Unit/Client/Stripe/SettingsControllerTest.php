<?php

namespace Tests\Unit\Client\Stripe;

use App\Http\Controllers\RazorpayController;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Plugins\Stripe\Controllers\SettingsController;
use App\User;
use Cartalyst\Stripe\Laravel\Facades\Stripe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\DBTestCase;

class SettingsControllerTest extends DBTestCase
{
    // Helper method to set up the mock for the Stripe client
    protected function setupStripeClientMock($expectedArguments, $status)
    {
        $stripeClientConstructorMock = Mockery::mock('\Stripe\StripeClient');
        $stripeClientConstructorMock->shouldReceive('paymentIntents->confirm')
            ->with('payment_intent_id', $expectedArguments)
            ->andReturn(['status' => $status]);
        \DB::table('api_keys')->where('id', 1)->update(['stripe_secret' => 'sk_test_FIPEe0BihQ4Rn2exN1BhOotg']);

        return $stripeClientConstructorMock;
    }

    // Helper method to set up the mock for the request
    protected function setupRequestMock($requestData)
    {
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('all')->andReturn($requestData);
        $requestMock->shouldReceive('isPrecognitive')->andReturn(null);
        $requestMock->shouldReceive('validate')->andReturn($requestData);
        foreach ($requestData as $key => $value) {
            $requestMock->shouldReceive('get')->with($key)->andReturn($value);
        }

        return $requestMock;
    }

    protected function stripeTokenGenerate($cardNumber = '4242424242424242')
    {
        $stripe = Stripe::make('sk_test_FIPEe0BihQ4Rn2exN1BhOotg');
        $stripeToken = $stripe->tokens()->create([
            'card' => [
                'number' => $cardNumber,
                'exp_month' => 12,
                'exp_year' => 25,
                'cvc' => '123',
            ],
        ]);

        return $stripeToken;
    }

    // Helper method to set up the Auth user
    protected function SetAuthUser()
    {
        $user = \Auth::shouldReceive('user')->andReturn((object) [
            'first_name' => 'sowmi',
            'last_name' => 's',
            'email' => 'sowmi@gmail.com',
            'line1' => '5/204',
            'postal_code' => '621651',
            'city' => 'Bangalore',
            'state' => 'Karnataka',
            'country' => 'India',
            'address' => 'Bangalore',
            'zip' => '590017',
            'town' => 'koramangala',
        ]);

        return $user;
    }

    // Test case for handling 3DS authentication
    public function test_handlePayment_3DS_authentication()
    {
        $stripeToken = $this->stripeTokenGenerate('4000003560000008');
        $requestData = ['stripeToken' => $stripeToken['id']];
        $expectedArguments = ['payment_method' => 'pm_card_visa', 'return_url' => 'https://example.com/return-url'];
        $status = 'requires_action';
        $stripeClientConstructorMock = $this->setupStripeClientMock($expectedArguments, $status);
        $requestMock = $this->setupRequestMock($requestData);
        $this->SetAuthUser();
        $controller = new SettingsController($stripeClientConstructorMock);
        $response = $controller->handlePayment($requestMock, 50, 'INR', 'https://example.com/return-url', null);
        $this->assertEquals('requires_action', $response['status']);
        $this->assertEquals('https://example.com/return-url', $response['next_action']['redirect_to_url']['return_url']);
    }

    // Test case for handling Non 3DS card
    public function test_handlePayment_return_non_3ds_values()
    {
        $stripeToken = $this->stripeTokenGenerate();
        $requestData = ['stripeToken' => $stripeToken['id']];
        $expectedArguments = ['payment_method' => 'pm_card_visa', 'return_url' => 'https://example.com/return-url'];
        $status = 'require_action';
        $stripeClientConstructorMock = $this->setupStripeClientMock($expectedArguments, $status);
        $requestMock = $this->setupRequestMock($requestData);
        $this->SetAuthUser();
        $controller = new SettingsController($stripeClientConstructorMock);
        $response = $controller->handlePayment($requestMock, 50, 'INR', 'https://example.com/return-url', null);
        $this->assertEquals('succeeded', $response['status']);
    }

    // Test case for handling incorrect stripe token
    public function test_handlePayment_return_exception_incorrect_values()
    {
        try {
            $requestData = ['stripeToken' => '12345678904567890'];
            $expectedArguments = ['payment_method' => 'pm_card_visa', 'return_url' => 'https://example.com/return-url'];
            $status = 'require_action';
            $stripeClientConstructorMock = $this->setupStripeClientMock($expectedArguments, $status);
            $requestMock = $this->setupRequestMock($requestData);
            $this->SetAuthUser();
            $controller = new SettingsController($stripeClientConstructorMock);
            $response = $controller->handlePayment($requestMock, 50, 'INR', 'https://example.com/return-url', null);
        } catch (\Exception $exception) {
            $this->assertEquals('Invalid token id: 12345678904567890', $exception->getMessage());
        }
    }

    // Test case for handling autopay for 3ds with incomplete status
    public function test_handle_autoPayment_non_3ds_card()
    {
        $stripePaymentDetails = (object) ['payment_intent_id' => 'pm_1OyUW0I0SyY30M2QqJqeC5hx'];

        $productDetails = (object) ['name' => 'Sample Product'];
        $unitCost = 50;
        $currency = 'INR';
        $plan = (object) ['days' => 30];
        $expectedArguments = ['id' => 'sub_1OyXYHI0SyY30M2QDkWSfCb2',
            'object' => 'subscription', ];
        $status = 'incomplete';
        $stripeClientConstructorMock = $this->setupStripeClientMock($expectedArguments, $status);
        $this->SetAuthUser();
        $controller = new SettingsController($stripeClientConstructorMock);
        $response = $controller->handleStripeAutoPay($stripePaymentDetails, $productDetails, $unitCost, $currency, $plan);
        $this->assertEquals($status, $response->status);
    }

//     Test case for handling autopay for non 3ds with active status
    public function test_handle_autoPayment_3ds_card()
    {
        $stripePaymentDetails = (object) ['payment_intent_id' => 'pm_1OyTcJI0SyY30M2QznXTOvZH'];

        $productDetails = (object) ['name' => 'Sample Product'];
        $unitCost = 50;
        $currency = 'INR';
        $plan = (object) ['days' => 30];
        $expectedArguments = ['id' => 'sub_1OyXYHI0SyY30M2QDkWSfCb2',
            'object' => 'subscription', ];
        $status = 'active';
        $stripeClientConstructorMock = $this->setupStripeClientMock($expectedArguments, $status);
        $this->SetAuthUser();
        $controller = new SettingsController($stripeClientConstructorMock);
        $response = $controller->handleStripeAutoPay($stripePaymentDetails, $productDetails, $unitCost, $currency, $plan);
        $this->assertEquals($status, $response->status);
    }

    //Testcase for handle razorpay api for subscription
    public function test_handleRzpAutoPay_correctly()
    {
        $user = User::factory()->create(['id' => mt_rand(1, 999), 'role' => 'user', 'country' => 'IN']);
        $product = Product::create(['name' => 'Helpdesk']);

        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => 'Helpdesk Advance', 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $subscription = Subscription::create(['order_id' => $order->id, 'product_id' => $product->id, 'version' => 'v3.0.0', 'is_subscribed' => '1', 'autoRenew_status' => '1']);
        $plan = Plan::create(['name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        \DB::table('api_keys')->where('id', 1)->update(['rzp_key' => 'rzp_test_0UWbi4WpjuMCoC', 'rzp_secret' => 'jZbOckxf4RhwaUAgxzegwQqV']);
        // Prepare mock data
        $days = 30;
        $product_name = 'Example Product';
        $cost = 1000;
        $currency = 'INR';

        $endDate = date('Y-m-d H:m:i');
        Http::fake([
            'api.razorpay.com/*' => Http::response([
                'id' => 'sub_NqwkfKaNkiuHXG',
                'entity' => 'subscription',
                'plan_id' => 'plan_NqwkeMP7pGGucR',
                'status' => 'created',
            ], 200),
        ]);
        $controller = new RazorpayController();
        $result = $controller->handleRzpAutoPay($cost, $days, $product_name, $invoice, $currency, $subscription, $user, $order, $endDate, $product);
        $this->assertEquals('created', $result['status']);
    }
}
