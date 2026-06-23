<?php

namespace Tests\Unit\Backend\Http\Controllers;

use App\Model\Payment\Plan;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\User;
use DB;
use Tests\DBTestCase;

class FreeTrialControllerTest extends DBTestCase
{
    public function test_first_login_attempt_return_exception_when_not_first_time_register_users(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create(['role' => 'user', 'country' => 'IN']);
        $this->actingAs($user);

        $product = Product::create(['name' => 'Helpdesk']);
        $plan = Plan::create(['name' => 'Trial Plan', 'product' => $product->id, 'days' => 30]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);

        // Simulate user already used free trial for this product
        DB::table('free_trial_allowed')->insert([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->postJson('free-trial/start', [
            'domain' => 'testdomain',
            'product_id' => $product->id,
        ]);

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }
}
