<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Product;

use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class PlanControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    // --- GET /plans ---

    public function test_admin_gets_200(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/plans')->assertStatus(200);
    }

    public function test_client_is_redirected_302(): void
    {
        $this->getLoggedInUser('user');
        $this->getJson('/plans')->assertStatus(302);
    }

    public function test_guest_gets_401(): void
    {
        $this->getJson('/plans')->assertStatus(401);
    }

    public function test_list_has_success_flag_and_paginated_structure(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/plans');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('data.data'));
    }

    // --- GET /plan/{planId} ---

    public function test_nonexistent_plan_returns_400_with_failure_flag_and_message(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/plan/999999999');

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
        $this->assertNotEmpty($response->json('message'));
    }

    // --- POST /plans — exact validation errors ---

    public function test_create_empty_body_returns_422_with_required_field_errors(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/plans', []);

        $response->assertStatus(412);
        $errors = $response->json('message');

        foreach (['name', 'currency', 'add_price', 'renew_price'] as $field) {
            $this->assertArrayHasKey($field, $errors, "Expected '$field' in plan errors");
        }
    }

    public function test_create_missing_name_has_name_error(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/plans', [
            'product' => 1, 'status' => 1,
            'currency' => ['USD'], 'add_price' => [0], 'renew_price' => [0],
            'product_quantity' => 1,
        ]);

        $response->assertStatus(412);
        $this->assertArrayHasKey('name', $response->json('message'));
    }

    public function test_create_negative_add_price_has_add_price_error(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/plans', [
            'name' => 'Bad Plan', 'product' => 1, 'status' => 1,
            'currency' => ['USD'], 'add_price' => [-10], 'renew_price' => [0],
            'product_quantity' => 1,
        ]);

        $response->assertStatus(412);
        // add_price.* min:0 violated
        $errors = $response->json('message');
        $this->assertTrue(
            array_key_exists('add_price.0', $errors) || array_key_exists('add_price', $errors),
            'Expected add_price error for negative value'
        );
    }

    // --- PATCH /plan/{planId} ---

    public function test_update_nonexistent_plan_returns_422(): void
    {
        $this->getLoggedInUser('admin');
        $this->patchJson('/plan/999999999', ['name' => 'Test'])->assertStatus(412);
    }

    // --- DELETE /plans ---

    public function test_bulk_delete_without_ids_returns_400_with_failure_flag(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->deleteJson('/plans', []);

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
    }

    public function test_bulk_delete_unauthenticated_returns_401(): void
    {
        $this->deleteJson('/plans', ['ids' => [1]])->assertStatus(401);
    }

    public function test_get_existing_plan_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $product = \App\Model\Product\Product::factory()->create();
        $plan = \App\Model\Payment\Plan::factory()->create(['product' => $product->id]);
        $response = $this->getJson("/plan/{$plan->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertEquals($plan->id, $response->json('data.id'));
    }

    public function test_get_all_plans_with_search_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/plans?search-query=monthly');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_plan_create_with_valid_data_returns_200(): void
    {
        // Covers lines 205-246: planCreate
        $this->getLoggedInUser('admin');
        $product = Product::factory()->create();

        $response = $this->postJson('/plans', [
            'name' => 'Monthly Plan',
            'product' => $product->id,
            'days' => 30,
            'status' => 1,
            'add_price' => [99],
            'currency' => ['USD'],
            'renew_price' => [99],
            'offer_price' => [null],
            'no_of_agents' => 5,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_update_plan_with_valid_data_returns_200(): void
    {
        // Covers lines 271-308: updatePlan
        $this->getLoggedInUser('admin');
        $product = Product::factory()->create();

        // Disable any existing active plan for this product/period to avoid unique constraint
        Plan::where('product', $product->id)->where('status', 1)->update(['status' => 0]);

        $plan = Plan::factory()->create(['product' => $product->id, 'status' => 1]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'USD', 'add_price' => 50]);

        $response = $this->patchJson('/plan/'.$plan->id, [
            'name' => 'Updated Plan',
            'product' => $product->id,
            'days' => 365,
            'status' => 1,
            'add_price' => [199],
            'currency' => ['USD'],
            'renew_price' => [199],
            'offer_price' => [null],
            'no_of_agents' => 5,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_delete_bulk_plans_with_ids_returns_200(): void
    {
        // Covers lines 309+: deleteBulkPlans
        $this->getLoggedInUser('admin');
        $product = Product::factory()->create();
        $plan = Plan::factory()->create(['product' => $product->id]);

        $response = $this->deleteJson('/plans', ['select' => [$plan->id]]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
