<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\User;

use App\User;
use Tests\DBTestCase;

class ClientControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    // --- GET /users: role gates ---

    public function test_admin_gets_200(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/users')->assertStatus(200);
    }

    public function test_client_is_redirected_302(): void
    {
        $this->getLoggedInUser('user');
        $this->getJson('/users')->assertStatus(302);
    }

    public function test_guest_gets_401(): void
    {
        $this->getJson('/users')->assertStatus(401);
    }

    // --- Response shape: list items have known fields ---

    public function test_list_items_have_expected_fields(): void
    {
        $this->getLoggedInUser('admin');
        // Create a client so list is not empty
        User::factory()->create(['role' => 'user']);

        $response = $this->getJson('/users');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));

        $items = $response->json('data.data');
        $this->assertNotEmpty($items, 'Users list must return at least one item');

        $user = $items[0];
        foreach (['id', 'first_name', 'last_name', 'email', 'mobile', 'country', 'created_at'] as $field) {
            $this->assertArrayHasKey($field, $user, "User list item missing field: $field");
        }
    }

    // --- Search ---

    public function test_sql_injection_in_search_returns_200_and_does_not_echo_payload(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson("/users?search=' OR 1=1 --");

        $response->assertStatus(200);
        $this->assertStringNotContainsString('OR 1=1', (string) $response->getContent());
    }

    // --- Pagination boundary ---

    public function test_page_beyond_last_returns_200_with_empty_data_array(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/users?page=99999');

        $response->assertStatus(200);
        $this->assertIsArray($response->json('data.data'));
    }

    // --- GET /user/{id}: verify exact user data returned ---

    public function test_admin_viewing_client_returns_200_with_correct_user_data(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create([
            'role' => 'user',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
        ]);

        $response = $this->getJson("/user/{$client->id}");

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));

        $data = $response->json('data');
        $this->assertSame($client->id, $data['id']);
        $this->assertSame('Jane', $data['first_name']);
        $this->assertSame('Doe', $data['last_name']);
        $this->assertSame('jane.doe@example.com', $data['email']);
        $this->assertSame('user', $data['role']);
        // Full name must be composed correctly
        $this->assertSame('Jane Doe', $data['full_name']);
    }

    public function test_client_cannot_view_other_user_gets_302(): void
    {
        $this->getLoggedInUser('user');
        $other = User::factory()->create(['role' => 'admin']);

        $this->getJson("/user/{$other->id}")->assertStatus(302);
    }

    // --- GET /user/{id}/invoices ---

    public function test_user_invoices_for_real_user_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create();

        $this->getJson("/user/{$client->id}/invoices")->assertStatus(200);
    }

    // --- GET /user/{id}/summary ---

    public function test_user_summary_for_nonexistent_user_returns_404(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/user/999999/summary')->assertStatus(404);
    }

    // --- DELETE /users ---

    public function test_bulk_delete_without_ids_returns_400_with_failure_flag(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->deleteJson('/users', []);

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
    }

    public function test_bulk_delete_unauthenticated_returns_401(): void
    {
        $this->deleteJson('/users', ['ids' => [1]])->assertStatus(401);
    }
}
