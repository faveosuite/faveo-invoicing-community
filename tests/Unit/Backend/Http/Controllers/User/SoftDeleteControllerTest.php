<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\User;

use Tests\DBTestCase;

class SoftDeleteControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    // --- GET /soft-delete ---

    public function test_admin_gets_200(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/soft-delete')->assertStatus(200);
    }

    public function test_client_is_redirected_302(): void
    {
        $this->getLoggedInUser('user');
        $this->getJson('/soft-delete')->assertStatus(302);
    }

    public function test_guest_gets_401(): void
    {
        $this->getJson('/soft-delete')->assertStatus(401);
    }

    public function test_list_has_success_flag_and_paginated_structure(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/soft-delete');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('data.data'));
    }

    // --- GET /user/restore/{id} ---

    public function test_restore_nonexistent_user_returns_404(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/user/restore/999999999')->assertStatus(404);
    }

    public function test_restore_unauthenticated_returns_401(): void
    {
        $this->getJson('/user/restore/1')->assertStatus(401);
    }

    // --- DELETE /permanent-delete-client ---

    public function test_permanent_delete_nonexistent_returns_400_with_error_shape(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->deleteJson('/permanent-delete-client', ['id' => 999999999]);

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
        $this->assertNotEmpty($response->json('message'));
    }

    public function test_permanent_delete_unauthenticated_returns_401(): void
    {
        $this->deleteJson('/permanent-delete-client', ['id' => 1])->assertStatus(401);
    }
}
