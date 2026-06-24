<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Product;

use Tests\DBTestCase;

class PlanControllerTest extends DBTestCase
{
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

    // --- PUT /plans — exact validation errors ---

    public function test_create_empty_body_returns_422_with_required_field_errors(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->putJson('/plans', []);

        $response->assertStatus(422);
        $errors = $response->json('errors');

        foreach (['name', 'currency', 'add_price', 'renew_price'] as $field) {
            $this->assertArrayHasKey($field, $errors, "Expected '$field' in plan errors");
        }
    }

    public function test_create_missing_name_has_name_error(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->putJson('/plans', [
            'product' => 1, 'status' => 1,
            'currency' => ['USD'], 'add_price' => [0], 'renew_price' => [0],
            'product_quantity' => 1,
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('name', $response->json('errors'));
    }

    public function test_create_negative_add_price_has_add_price_error(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->putJson('/plans', [
            'name' => 'Bad Plan', 'product' => 1, 'status' => 1,
            'currency' => ['USD'], 'add_price' => [-10], 'renew_price' => [0],
            'product_quantity' => 1,
        ]);

        $response->assertStatus(422);
        // add_price.* min:0 violated
        $errors = $response->json('errors');
        $this->assertTrue(
            array_key_exists('add_price.0', $errors) || array_key_exists('add_price', $errors),
            'Expected add_price error for negative value'
        );
    }

    // --- PATCH /plan/{planId} ---

    public function test_update_nonexistent_plan_returns_422(): void
    {
        $this->getLoggedInUser('admin');
        $this->patchJson('/plan/999999999', ['name' => 'Test'])->assertStatus(422);
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
}
