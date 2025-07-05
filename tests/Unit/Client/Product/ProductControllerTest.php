<?php

namespace Tests\Unit\Client\Product;

use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ProductControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    public function test_it_returns_subscription_dropdown_when_plans_exist()
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
        Currency::where('code', 'INR')->update(['status' => 1]);

        $product = Product::create([
            'name' => 'Helpdesk',
        ]);

        $plan = Plan::create([
            'name' => 'Helpdesk 1 year',
            'product' => $product->id,
            'days' => 365,
        ]);

        PlanPrice::create([
            'plan_id' => $plan->id,
            'currency' => 'INR',
        ]);

        $response = $this->get("/get-subscription/{$product->id}?user_id={$this->user->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);

        $this->assertStringContainsString('Helpdesk 1 year', $response['data']);
        $this->assertStringContainsString('<select', $response['data']);
        $this->assertStringContainsString('subscription-msg', $response['data']);
        $this->assertStringContainsString('getPrice(this.value)', $response['data']);
    }

    public function test_it_returns_error_when_no_plans_found()
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');

        $product = Product::create([
            'name' => 'Helpdesk',
        ]);

        $response = $this->get('get-subscription/'.$product->id, [
            'user_id' => $this->user->id,
        ]);

        $response->assertJson([
            'success' => false,
            'message' => __('message.order_no_active_plan_cancelled'),
        ]);
    }
}
