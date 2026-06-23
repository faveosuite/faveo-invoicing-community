<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Product;

use Tests\DBTestCase;

class GroupControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    // --- GET /groups ---

    public function test_admin_gets_200(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/groups')->assertStatus(200);
    }

    public function test_client_is_redirected_302(): void
    {
        $this->getLoggedInUser('user');
        $this->getJson('/groups')->assertStatus(302);
    }

    public function test_guest_gets_401(): void
    {
        $this->getJson('/groups')->assertStatus(401);
    }

    public function test_list_has_success_flag_and_paginated_structure(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/groups');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('data.data'));
    }

    // --- GET /group/{group_id} ---

    public function test_nonexistent_group_returns_400_with_failure_flag_and_message(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/group/999999999');

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
        $this->assertNotEmpty($response->json('message'));
    }

    // --- PUT /group — exact field errors ---

    public function test_create_missing_name_returns_422_with_name_error(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->putJson('/group', ['pricing_templates_id' => 1]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('name', $response->json('errors'));
    }

    public function test_create_missing_pricing_template_returns_422_with_template_error(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->putJson('/group', ['name' => 'Support']);

        $response->assertStatus(422);
        $this->assertArrayHasKey('pricing_templates_id', $response->json('errors'));
    }

    public function test_create_blocked_for_client_returns_302(): void
    {
        $this->getLoggedInUser('user');
        $this->putJson('/group', ['name' => 'x', 'pricing_templates_id' => 1])->assertStatus(302);
    }

    // --- PATCH /group/{group_id} ---

    public function test_update_nonexistent_group_returns_422(): void
    {
        $this->getLoggedInUser('admin');
        $this->patchJson('/group/999999999', ['name' => 'New'])->assertStatus(422);
    }

    // --- DELETE /group ---

    public function test_bulk_delete_without_ids_returns_400_with_failure_flag(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->deleteJson('/group', []);

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
    }

    public function test_bulk_delete_unauthenticated_returns_401(): void
    {
        $this->deleteJson('/group', ['ids' => [1]])->assertStatus(401);
    }
}
