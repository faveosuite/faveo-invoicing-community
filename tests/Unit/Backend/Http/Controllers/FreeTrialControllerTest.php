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
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id, 'trial_status' => 1]);

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
        $response->assertJson(['success' => false, 'message' => __('message.limit_is_up')]);
    }

    public function test_products_with_trial_disabled_are_not_listed_or_startable(): void
    {
        $this->withoutMiddleware();
        $this->actingAs(User::factory()->create(['role' => 'user', 'country' => 'IN']));

        $on = Product::create(['name' => 'Trial On']);
        $off = Product::create(['name' => 'Trial Off']);
        CloudProducts::create(['cloud_product' => $on->id, 'cloud_free_plan' => Plan::create(['name' => 'P', 'product' => $on->id, 'days' => 30])->id, 'trial_status' => 1]);
        CloudProducts::create(['cloud_product' => $off->id, 'cloud_free_plan' => Plan::create(['name' => 'P', 'product' => $off->id, 'days' => 30])->id, 'trial_status' => 0]);

        $ids = collect($this->getJson('store/cloud-products')->assertOk()->json('data.products'))->pluck('id');
        $this->assertContains($on->id, $ids);
        $this->assertNotContains($off->id, $ids);

        $this->postJson('free-trial/start', ['domain' => 'testdomain', 'product_id' => $off->id])
            ->assertStatus(400)
            ->assertJson(['message' => __('message.cannot_find_product')]);
    }
}
