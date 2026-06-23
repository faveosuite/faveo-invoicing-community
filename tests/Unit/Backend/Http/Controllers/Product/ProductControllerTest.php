<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Product;

use Tests\DBTestCase;

class ProductControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    // --- GET /products ---

    public function test_admin_gets_200(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/products')->assertStatus(200);
    }

    public function test_client_is_redirected_302(): void
    {
        $this->getLoggedInUser('user');
        $this->getJson('/products')->assertStatus(302);
    }

    public function test_guest_gets_401(): void
    {
        $this->getJson('/products')->assertStatus(401);
    }

    public function test_list_has_success_flag_and_paginated_structure(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/products');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('data.data'));
        $this->assertIsInt($response->json('data.current_page'));
    }

    // --- GET /product/{productId} ---

    public function test_nonexistent_product_returns_400_with_error_message(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/product/999999999');

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
        $this->assertNotEmpty($response->json('message'));
    }

    // --- PUT /product — every required field produces specific error ---

    public function test_create_empty_body_returns_422_with_all_required_field_errors(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->putJson('/product', []);

        $response->assertStatus(422);
        $errors = $response->json('errors');

        foreach (['name', 'type', 'group'] as $field) {
            $this->assertArrayHasKey($field, $errors, "Expected '$field' in product errors");
        }
    }

    public function test_create_missing_name_has_specific_error_message(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->putJson('/product', [
            'type' => '1', 'group' => '1', 'subscription' => '1', 'currency' => 'USD',
            'github_owner' => 'owner', 'github_repository' => 'repo',
        ]);

        $response->assertStatus(422);
        $this->assertSame('The name field is required.', $response->json('errors.name.0'));
    }

    public function test_create_blocked_for_client_returns_302(): void
    {
        $this->getLoggedInUser('user');
        $this->putJson('/product', ['name' => 'Test'])->assertStatus(302);
    }

    // --- PATCH /product/{productId} ---

    public function test_update_nonexistent_product_returns_422_validation_error(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->patchJson('/product/999999999', ['name' => 'Test']);

        $response->assertStatus(422);
    }

    // --- DELETE /products ---

    public function test_bulk_delete_without_ids_returns_400_with_failure_flag(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->deleteJson('/products', []);

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
    }

    public function test_bulk_delete_unauthenticated_returns_401(): void
    {
        $this->deleteJson('/products', ['ids' => [1]])->assertStatus(401);
    }
}
