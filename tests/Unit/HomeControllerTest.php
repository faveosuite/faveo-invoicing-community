<?php

namespace Tests\Unit;

use App\Http\Controllers\Order\RenewController;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\User;
use Tests\DBTestCase;

class HomeControllerTest extends DBTestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_validation_when_given_url_empty()
    {
        $response = $this->post('/renewurl', [
            'domain' => '',
        ]);
        $errors = session('errors');
        $response->assertStatus(302);
    }

    public function test_is_new_version_available_response_includes_versions()
    {
        $this->withoutMiddleware();
        $product = Product::factory()->create(['name' => 'Test Helpdesk']);
        ProductUpload::create([
            'product_id' => $product->id,
            'version' => 'v6.0.0',
            'title' => $product->name,
            'release_type' => 'official',
            'is_private' => 0,
        ]);

        $response = $this->call('get', 'new-version-available', [
            'title' => $product->name,
            'id' => $product->id,
            'version' => 'v5.0.0',
        ]);

        $response->assertStatus(200);
        $content = $response->json();
        $this->assertEquals('true', $content['status']);
        $this->assertEquals('new-version-available', $content['message']);
        $this->assertArrayHasKey('versions', $content);
        $this->assertContains('v6.0.0', $content['versions']);
    }

    public function test_no_new_version_available_response_includes_versions()
    {
        $this->withoutMiddleware();
        $product = Product::factory()->create(['name' => 'Test Helpdesk Pro']);
        ProductUpload::create([
            'product_id' => $product->id,
            'version' => 'v5.0.0',
            'title' => $product->name,
            'release_type' => 'official',
            'is_private' => 0,
        ]);

        $response = $this->call('get', 'new-version-available', [
            'title' => $product->name,
            'id' => $product->id,
            'version' => 'v6.0.0',
        ]);

        $response->assertStatus(200);
        $content = $response->json();
        $this->assertEquals('', $content['status']);
        $this->assertEquals('no-new-version-available', $content['message']);
        $this->assertArrayHasKey('versions', $content);
        $this->assertContains('v5.0.0', $content['versions']);
    }

    public function test_detailed_billing_info_returns_extended_fields()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create([
            'email' => 'billing@example.com',
            'company' => 'Acme Corp',
        ]);
        $product = Product::factory()->create();
        $plan = Plan::factory()->create(['product' => $product->id]);
        $order = Order::factory()->create([
            'number' => mt_rand(100000, 999999),
            'client' => $user->id,
            'product' => $product->id,
        ]);
        Subscription::create([
            'order_id' => $order->id,
            'plan_id' => $plan->id,
            'product_id' => $product->id,
            'update_ends_at' => '2026-12-31 00:00:00',
            'ends_at' => '2026-11-30 00:00:00',
            'support_ends_at' => '2026-10-31 00:00:00',
        ]);

        $response = $this->call('get', 'api/billingInfo', ['order' => $order->number]);

        $response->assertStatus(200);
        $content = $response->json();
        $this->assertEquals('billing@example.com', $content['billing_client_email']);
        $this->assertEquals('Acme Corp', $content['company']);
        $this->assertEquals($plan->name, $content['plan_name']);
        $this->assertArrayHasKey('start_date', $content);
        $this->assertArrayHasKey('next_billing_date', $content);
        $this->assertStringContainsString('my-order/'.$order->id, $content['order_url']);
    }

    public function test_renewurl_return_orderid_()
    {
        // Create test data
        $orderid = '12345';
        $product = Product::factory()->create([
            'id' => '1',
            'name' => 'Test Product',
        ]);

        $plan = Plan::factory()->create([
            'product' => $product->id,
            'days' => 30,
        ]);

        $planPrice = PlanPrice::factory()->create([
            'plan_id' => $plan->id,
            'currency' => 'USD',
            'renew_price' => 9.99,
        ]);
        $user = User::factory()->create([
            'first_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $subscription = Subscription::factory()->create([
            'order_id' => $orderid,
            'user_id' => $user,
            'product_id' => $product->id,
        ]);

        $renewController = new RenewController();
        $response = $renewController->generateInvoice($product, $user, $orderid, $plan->id, $planPrice->renew_price, $code = '', '4', 'INR');
        $url = url("autopaynow/$response->invoice_id");

        $expectedUrl = request()->getSchemeAndHttpHost().'/autopaynow/'.$response->invoice_id;

        $this->assertEquals($expectedUrl, $url);
    }
}
