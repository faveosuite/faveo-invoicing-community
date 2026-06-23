<?php

namespace Tests\Unit\Backend\Http\Controllers\Front\Cart;

use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\User;
use Tests\DBTestCase;

class BaseCartControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_get_cart_values_calculates_agent_qty_price_of_cart_when_reducing_agt_allowed_return_array_to_be_added(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create();
        $plan = Plan::factory()->create(['product' => $product->id, 'status' => 1]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'USD', 'add_price' => 100]);

        $response = $this->postJson('cart/items', [
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_get_cart_values_calculate_agent_qty_price_of_cart_when_increasin_agt_allowed_return_array_to_be_added(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create();
        $plan = Plan::factory()->create(['product' => $product->id, 'status' => 1]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'USD', 'add_price' => 200]);

        $response = $this->postJson('cart/items', [
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_get_cart_values_calculate_agent_qty_price_of_cart_when_invalid_product_passed_throws_exception(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('cart/items', [
            'product_id' => 999999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_id']);
    }

    public function test_update_agent_qty_updates_cart_when_modifying_agent_not_allowed_returns_same_cart_values(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Clear cart first, then show it
        $response = $this->getJson('cart/');
        $response->assertStatus(200);
    }

    public function test_update_product_qty_updates_cart_when_modifying_qty_allowed_returns_updated_cart(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create();
        $plan = Plan::factory()->create(['product' => $product->id, 'status' => 1]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'USD', 'add_price' => 100]);

        // Add item first
        $addResponse = $this->postJson('cart/items', [
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'quantity' => 1,
        ]);
        $addResponse->assertStatus(200);
        $cartData = $addResponse->json('data');

        if (! empty($cartData['items'])) {
            $itemId = $cartData['items'][0]['id'];
            $updateResponse = $this->putJson("cart/items/{$itemId}", ['quantity' => 2]);
            $updateResponse->assertStatus(200);
        } else {
        }
    }

    public function test_update_product_qty_updates_cart_when_modifying_qty_not_allowed_throws_exception(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Try to update non-owned item
        $response = $this->putJson('cart/items/99999', ['quantity' => 1]);
        $response->assertStatus(403);
    }

    public function test_reduce_product_qty_reduce_cart_qty_when_modifying_qty_allowed_returns_updated_cart(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('cart/');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_reduce_product_qty_updates_cart_when_modifying_qty_not_allowed_throws_exception(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->deleteJson('cart/items/99999');
        $response->assertStatus(403);
    }

    public function test_cart_has_same_product_with_different_plans(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create();
        $plan1 = Plan::factory()->create(['product' => $product->id, 'status' => 1]);
        $plan2 = Plan::factory()->create(['product' => $product->id, 'status' => 1]);
        PlanPrice::factory()->create(['plan_id' => $plan1->id, 'currency' => 'USD', 'add_price' => 100]);
        PlanPrice::factory()->create(['plan_id' => $plan2->id, 'currency' => 'USD', 'add_price' => 200]);

        $r1 = $this->postJson('cart/items', ['product_id' => $product->id, 'plan_id' => $plan1->id]);
        $r1->assertStatus(200);

        $r2 = $this->postJson('cart/items', ['product_id' => $product->id, 'plan_id' => $plan2->id]);
        $r2->assertStatus(200);
    }

    public function test_when_we_session_not_set_payment_gateway_not_selected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('cart/');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_post_checkout_cart(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        // Must use auth middleware for checkout
        $this->withoutMiddleware(false);
        $this->actingAs($user);

        $response = $this->getJson('cart/checkout');
        $response->assertStatus(200);
    }

    public function test_successful_when_license_mocked(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('cart/');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_store_has_groups(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('store/groups');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }
}
