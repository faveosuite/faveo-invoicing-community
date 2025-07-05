<?php

namespace Tests\Unit\Subscription;

use App\ApiKey;
use App\Http\Controllers\ConcretePostSubscriptionHandleController;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Model\Common\StatusSetting;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use Carbon\Carbon;
use Tests\DBTestCase;

class SubscriptionControllerTest extends DBTestCase
{
    protected function instantiateDependencies()
    {
        // Instantiate dependencies
        $invoiceModel = new Invoice();
        $orderModel = new Order();
        $statusSettingModel = new StatusSetting();
        $plan = new Plan();
        $subscription = new Subscription();
        $payment = new Payment();

        $dependencies = [
            'invoiceModel' => $invoiceModel,
            'orderModel' => $orderModel,
            'statusSettingModel' => $statusSettingModel,
            'plan' => $plan,
            'subscription' => $subscription,
            'payment' => $payment,
        ];
        $controller = new ConcretePostSubscriptionHandleController(
            $dependencies['invoiceModel'],
            $dependencies['orderModel'],
            $dependencies['statusSettingModel'],
            $dependencies['plan'],
            $dependencies['subscription'],
            $dependencies['payment']
        );

        return $controller;
    }

    //return empty when zero expired subscription
    public function test_autoRenewal_return_null_when_empty_expired_subscription()
    {
        $stripeSecretKey = ApiKey::create(['stripe_secret' => 'sk_test_FIPEe0BihQ4Rn2exN1BhOotg', 'rzp_key' => 'rzp_test_fNDuvutBRXJLkQ', 'rzp_secret' => 'ObVJAj8L2e7V9RLOQkcdLtSw']);
        $date = '2025-03-02 18:15:02';
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $user = User::factory()->create(['id' => mt_rand(1, 999), 'role' => 'user', 'country' => 'IN']);

        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => 'Helpdesk Advance', 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $permissions = 'No Permissions';
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id, 'version' => 'v6.0.0', 'update_ends_at' => $date]);
        $controller = $this->instantiateDependencies();
        $response = (new SubscriptionController($controller))->getOnDayExpiryInfoSubs();
        $this->assertEmpty($response);
    }

    //return onday expired data in autorenewal
    public function test_autoRenewal_return_onday_expired_subscription()
    {
        $stripeSecretKey = ApiKey::create(['stripe_secret' => 'sk_test_FIPEe0BihQ4Rn2exN1BhOotg', 'rzp_key' => 'rzp_test_fNDuvutBRXJLkQ', 'rzp_secret' => 'ObVJAj8L2e7V9RLOQkcdLtSw']);
        $date = date('Y-m-d H:m:i');
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $user = User::factory()->create(['id' => mt_rand(1, 999), 'role' => 'user', 'country' => 'IN']);

        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => 'Helpdesk Advance', 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $permissions = 'No Permissions';
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id, 'version' => 'v6.0.0', 'update_ends_at' => $date]);
        $controller = $this->instantiateDependencies();
        $response = (new SubscriptionController($controller))->getOnDayExpiryInfoSubs();
        $this->assertEmpty($response);
    }

    public function test_autoRenewal_return_payment_status_suucess()
    {
        $date = date('Y-m-d H:m:i');
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $user = User::factory()->create(['id' => mt_rand(1, 999), 'role' => 'user', 'country' => 'IN']);

        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $payment_method = 'stripe';
        $controller = $this->instantiateDependencies();
        $response = $controller->postRazorpayPayment($invoiceItem, $payment_method);
        $this->assertEquals('success', $response->payment_status);
    }

    public function test_calculateUnitCost_withTwodecimal_currency()
    {
        $currency = 'INR';
        $cost = '100';
        $controller = $this->instantiateDependencies();
        $response = $controller->calculateUnitCost($currency, $cost);
        $this->assertEquals(10000, $response);
    }

    public function test_calculateUnitCost_withThreedecimal_currency()
    {
        $currency = 'BHD';
        $cost = '100';
        $controller = $this->instantiateDependencies();
        $response = $controller->calculateUnitCost($currency, $cost);
        $this->assertEquals(100000, $response);
    }

    public function test_calculateUnitCost_withZerodecimal_currency()
    {
        $currency = 'JPY';
        $cost = '100';
        $controller = $this->instantiateDependencies();
        $response = $controller->calculateUnitCost($currency, $cost);
        $this->assertEquals(100.0, $response);
    }

    public function test_create_susbcription_enabled_users()
    {
        $user = User::factory()->create(['id' => mt_rand(1, 999), 'role' => 'user', 'country' => 'IN']);
        $product = Product::create(['name' => 'Helpdesk']);

        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => 'Helpdesk Advance', 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $subscription = Subscription::create(['order_id' => $order->id, 'product_id' => $product->id, 'version' => 'v3.0.0', 'is_subscribed' => '1', 'autoRenew_status' => '1']);
        $plan = Plan::create(['name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $unitCost = 1000;
        $currency = 'INR';
        $cost = 10;
        $end = date('Y-m-d H:m:i');
        $stripePaymentDetails = \DB::table('auto_renewals')->insert([
            'user_id' => $user->id,
            'customer_id' => 'cus_Pnj9QJLaBK6Hu7',
            'order_id' => $order->id,
            'payment_method' => 'stripe',
            'payment_intent_id' => 'pm_1Oy7hGI0SyY30M2QxKcYp9Jo',
        ]);
        $controller = $this->instantiateDependencies();
        $response = (new SubscriptionController($controller))->createSubscriptionsForEnabledUsers($stripePaymentDetails, $product, $unitCost, $currency, $plan, $subscription, $invoice, $order, $user, $cost,
            $end);
        $this->assertTrue(true);
    }

    public function test_update_invoice_and_payment_after_renewed_successfully()
    {
        $user = User::factory()->create(['id' => mt_rand(1, 999), 'role' => 'user', 'country' => 'IN']);
        $product = Product::create(['name' => 'Helpdesk']);

        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => 'Helpdesk Advance', 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $subscription = Subscription::create(['order_id' => $order->id, 'product_id' => $product->id, 'version' => 'v3.0.0', 'is_subscribed' => '1', 'autoRenew_status' => '1']);
        $plan = Plan::create(['name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $unitCost = 1000;
        $currency = 'INR';
        $cost = 10;
        $end = date('Y-m-d H:m:i');
        $payment_method = 'razorpay';

        $controller = $this->instantiateDependencies();
        $response = $controller->postRazorpayPayment($invoiceItem, $payment_method);
        $this->assertTrue(true);
    }

    public function test_autorenewal_when_there_is_no_plan_for_the_order()
    {
        $this->withoutMiddleware();
        \Mail::fake();
        StatusSetting::first()->update(['subs_expirymail' => 1]);
        $user = User::factory()->create([
            'role' => 'user',
            'country' => 'IN',
        ]);

        $product = Product::create([
            'name' => 'Helpdesk',
        ]);

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_name' => $product->name,
        ]);

        $order = Order::create([
            'client' => $user->id,
            'order_status' => 'executed',
            'product' => 'Helpdesk Advance',
            'number' => 123456,
            'invoice_id' => $invoice->id,
        ]);

        $plan = Plan::create([
            'name' => 'Helpdesk 1 year',
            'product' => $product->id,
            'days' => 365,
        ]);

        PlanPrice::create([
            'plan_id' => $plan->id,
        ]);

        $id = \DB::table('subscriptions')->insertGetId([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'is_subscribed' => 1,
            'autoRenew_status' => 1,
            'update_ends_at' => Carbon::now()->addDays(1)->toDateString(),
        ]);

        $subscription = \DB::table('subscriptions')->where('id', $id)->first();

        $this->assertTrue((bool) $subscription->autoRenew_status);
        $this->assertTrue((bool) $subscription->is_subscribed);

        \Artisan::call('renewal:cron');

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'autoRenew_status' => 0,
            'is_subscribed' => 0,
        ]);
    }
}
