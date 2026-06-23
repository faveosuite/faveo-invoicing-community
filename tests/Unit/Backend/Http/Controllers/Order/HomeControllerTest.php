<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Order;

use App\Http\Controllers\Order\RenewController;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use Tests\DBTestCase;

class HomeControllerTest extends DBTestCase
{
    /**
     * A basic unit test example.
     */
    public function test_validation_when_given_url_empty(): void
    {
        $response = $this->post('/renewurl', [
            'domain' => '',
        ]);
        session('errors');
        $response->assertStatus(302);
    }

    public function test_renewurl_return_orderid_(): void
    {
        // Create test data
        $orderid = 12345;
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

        Subscription::factory()->create([
            'order_id' => $orderid,
            'user_id' => $user,
            'product_id' => $product->id,
        ]);

        $renewController = new RenewController;
        $response = $renewController->generateInvoice($product, $user, $orderid, $plan->id, $planPrice->renew_price, $code = '', '4', 'INR');
        $url = url('autopaynow/'.$response->invoice_id);

        $expectedUrl = request()->getSchemeAndHttpHost().'/autopaynow/'.$response->invoice_id;

        $this->assertEquals($expectedUrl, $url);
    }
}
