<?php

namespace Tests\Unit\Backend\Http\Controllers\Subscription;

use App\ApiKey;
use App\Http\Controllers\ConcretePostSubscriptionHandleController;
use App\Http\Controllers\License\LicensePermissionsController;
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
use Artisan;
use DB;
use Illuminate\Support\Facades\Date;
use Mail;
use Tests\DBTestCase;

class SubscriptionControllerTest extends DBTestCase
{
    protected function instantiateDependencies(): ConcretePostSubscriptionHandleController
    {
        // Instantiate dependencies
        $invoiceModel = new Invoice;
        $orderModel = new Order;
        $statusSettingModel = new StatusSetting;
        $plan = new Plan;
        $subscription = new Subscription;
        $payment = new Payment;

        $dependencies = [
            'invoiceModel' => $invoiceModel,
            'orderModel' => $orderModel,
            'statusSettingModel' => $statusSettingModel,
            'plan' => $plan,
            'subscription' => $subscription,
            'payment' => $payment,
        ];

        return new ConcretePostSubscriptionHandleController(
            $dependencies['invoiceModel'],
            $dependencies['orderModel'],
            $dependencies['statusSettingModel'],
            $dependencies['plan'],
            $dependencies['subscription'],
            $dependencies['payment']
        );
    }

    // return empty when zero expired subscription
    public function test_auto_renewal_return_null_when_empty_expired_subscription(): void
    {
        ApiKey::create(['stripe_secret' => 'sk_test_FIPEe0BihQ4Rn2exN1BhOotg', 'rzp_key' => 'rzp_test_fNDuvutBRXJLkQ', 'rzp_secret' => 'ObVJAj8L2e7V9RLOQkcdLtSw']);
        $date = '2025-03-02 18:15:02';
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $user = User::factory()->create(['id' => mt_rand(1, 999), 'role' => 'user', 'country' => 'IN']);

        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => 'Helpdesk Advance', 'number' => mt_rand(100000, 999999), ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Helpdesk 1 year', 'product' => $product->id, 'days' => 365]);
        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id, 'version' => 'v6.0.0', 'update_ends_at' => $date]);
        $controller = $this->instantiateDependencies();
        $response = new SubscriptionController($controller)->getOnDayExpiryInfoSubs();
        $this->assertEmpty($response);
    }

    // return onday expired data in autorenewal
    public function test_auto_renewal_return_onday_expired_subscription(): void
    {
        ApiKey::create(['stripe_secret' => 'sk_test_FIPEe0BihQ4Rn2exN1BhOotg', 'rzp_key' => 'rzp_test_fNDuvutBRXJLkQ', 'rzp_secret' => 'ObVJAj8L2e7V9RLOQkcdLtSw']);
        $date = date('Y-m-d H:m:i');
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $user = User::factory()->create(['id' => mt_rand(1, 999), 'role' => 'user', 'country' => 'IN']);

        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => 'Helpdesk Advance', 'number' => mt_rand(100000, 999999), ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Helpdesk 1 year', 'product' => $product->id, 'days' => 365]);
        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id, 'version' => 'v6.0.0', 'update_ends_at' => $date]);
        $controller = $this->instantiateDependencies();
        $response = new SubscriptionController($controller)->getOnDayExpiryInfoSubs();
        $this->assertEmpty($response);
    }

    public function test_calculate_unit_cost_with_twodecimal_currency(): void
    {
        $currency = 'INR';
        $cost = '100';
        $controller = $this->instantiateDependencies();
        $response = $controller->calculateUnitCost($currency, $cost);
        $this->assertEquals(10000, $response);
    }

    public function test_calculate_unit_cost_with_threedecimal_currency(): void
    {
        $currency = 'BHD';
        $cost = '100';
        $controller = $this->instantiateDependencies();
        $response = $controller->calculateUnitCost($currency, $cost);
        $this->assertEquals(100000, $response);
    }

    public function test_calculate_unit_cost_with_zerodecimal_currency(): void
    {
        $currency = 'JPY';
        $cost = '100';
        $controller = $this->instantiateDependencies();
        $response = $controller->calculateUnitCost($currency, $cost);
        $this->assertEquals(100.0, $response);
    }

}
