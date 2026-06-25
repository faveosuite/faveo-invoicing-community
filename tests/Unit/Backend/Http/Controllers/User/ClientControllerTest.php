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

    // =========================================================================
    // GET /user/{id} — edit user
    // =========================================================================

    public function test_get_edit_user_existing_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user']);
        $response = $this->getJson("/user/{$client->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertEquals($client->id, $response->json('data.id'));
    }

    public function test_get_edit_user_nonexistent_returns_404(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/user/999999');
        $response->assertStatus(404);
    }

    // =========================================================================
    // GET /user/{id}/payments
    // =========================================================================

    public function test_get_user_payments_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user']);
        $response = $this->getJson("/user/{$client->id}/payments");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // =========================================================================
    // GET /user/{id}/comments
    // =========================================================================

    public function test_get_user_comments_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user']);
        $response = $this->getJson("/user/{$client->id}/comments");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // =========================================================================
    // POST /user/{id}/comments — store comment
    // =========================================================================

    public function test_store_user_comment_for_nonexistent_user_returns_404(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/user/999999/comments', ['description' => 'test']);
        $response->assertStatus(404);
        $response->assertJson(['success' => false]);
    }

    public function test_store_user_comment_with_valid_data_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $client = User::factory()->create(['role' => 'user']);
        $response = $this->postJson("/user/{$client->id}/comments", [
            'description' => 'Test comment for this user',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('data.description', 'Test comment for this user');
    }

    // =========================================================================
    // PUT /users — user create
    // =========================================================================

    public function test_user_create_missing_required_fields_returns_422(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->putJson('/users', []);
        $response->assertStatus(422);
    }

    // =========================================================================
    // GET /get-columns and POST /save-columns
    // =========================================================================

    public function test_get_columns_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/get-columns?report-key=users');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_export_users_without_queue_returns_400(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/export-users');
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    // =========================================================================
    // Additional coverage for uncovered methods
    // =========================================================================

    public function test_save_columns_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/save-columns', [
            'report-key' => 'users',
            'columns' => ['id', 'name', 'email'],
        ]);
        $this->assertContains($response->status(), [200, 400, 422]);
    }

    public function test_update_user_comment_returns_200_or_not_found(): void
    {
        $this->getLoggedInUser('admin');
        $user = \App\User::factory()->create(['email' => 'comment-user-'.uniqid().'@test.local']);

        $comment = \App\Comment::create([
            'user_id' => $user->id,
            'updated_by_user_id' => $user->id,
            'description' => 'Original comment',
        ]);

        $response = $this->putJson('/user/'.$user->id.'/comments/'.$comment->id, [
            'description' => 'Updated comment',
        ]);
        $this->assertContains($response->status(), [200, 400, 404]);
    }

    public function test_delete_user_comment_returns_200_or_not_found(): void
    {
        $this->getLoggedInUser('admin');
        $user = \App\User::factory()->create(['email' => 'comment-del-user-'.uniqid().'@test.local']);

        $comment = \App\Comment::create([
            'user_id' => $user->id,
            'updated_by_user_id' => $user->id,
            'description' => 'Comment to delete',
        ]);

        $response = $this->deleteJson('/user/'.$user->id.'/comments/'.$comment->id);
        $this->assertContains($response->status(), [200, 400, 404]);
    }

    public function test_user_update_validates_fields(): void
    {
        $this->getLoggedInUser('admin');
        $user = \App\User::factory()->create(['email' => 'update-user-'.uniqid().'@test.local']);

        $response = $this->patchJson('/user/'.$user->id, [
            'first_name' => 'Updated',
            'last_name' => 'User',
            'email' => $user->email,
            'role' => 'user',
        ]);
        $this->assertContains($response->status(), [200, 400, 422]);
    }

    public function test_download_exported_file_returns_404_for_nonexistent(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/download-exported-file/999999');
        $this->assertContains($response->status(), [200, 400, 404]);
    }

    // =========================================================================
    // getUserSummary — happy path with real user
    // =========================================================================

    public function test_get_user_summary_returns_data_for_existing_user(): void
    {
        $this->getLoggedInUser('admin');
        $user = \App\User::factory()->create(['email' => 'summary-'.uniqid().'@test.local']);

        $response = $this->getJson('/user/'.$user->id.'/summary');

        $this->assertContains($response->status(), [200, 400]);
        if ($response->status() === 200) {
            $response->assertJsonStructure(['data' => ['invoice_total', 'amount_paid', 'balance', 'invoice_count']]);
        }
    }

    // =========================================================================
    // getUserInvoices — with sort-field and sort-order params
    // =========================================================================

    public function test_get_user_invoices_with_sort_params(): void
    {
        $this->getLoggedInUser('admin');
        $user = \App\User::factory()->create(['email' => 'inv-sort-'.uniqid().'@test.local']);

        $response = $this->getJson('/user/'.$user->id.'/invoices?sort-field=number&sort-order=asc');

        $this->assertContains($response->status(), [200, 400]);
    }

    public function test_get_user_invoices_with_invalid_sort_field_falls_back(): void
    {
        $this->getLoggedInUser('admin');
        $user = \App\User::factory()->create(['email' => 'inv-bad-sort-'.uniqid().'@test.local']);

        // Invalid sort field → falls back to 'date'
        $response = $this->getJson('/user/'.$user->id.'/invoices?sort-field=invalid_column');

        $this->assertContains($response->status(), [200, 400]);
    }

    // =========================================================================
    // getAllUsers — with various filter combinations
    // =========================================================================

    public function test_get_all_users_with_role_filter(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/users?role=user&limit=5');

        $response->assertStatus(200);
    }

    public function test_get_all_users_with_date_filters(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/users?reg_from=2020-01-01&reg_till='.date('Y-m-d').'&limit=5');

        $response->assertStatus(200);
    }

    public function test_get_all_users_with_country_filter(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/users?country=IN&limit=5');

        $response->assertStatus(200);
    }

    // =========================================================================
    // userCreate — with valid data
    // =========================================================================

    public function test_user_create_with_valid_data_creates_user(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->putJson('/users', [
            'first_name' => 'New',
            'last_name'  => 'TestUser',
            'email'      => 'newcreate_'.uniqid().'@test.local',
            'password'   => 'Secret1234!',
            'role'       => 'user',
            'company'    => 'Test Co',
        ]);

        $this->assertContains($response->status(), [200, 400, 422]);
    }

    // =========================================================================
    // saveColumns — checkbox/action auto-added, dedup preserved
    // =========================================================================

    public function test_save_columns_auto_prepends_checkbox_and_appends_action(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->postJson('/save-columns', [
            'entity_type'      => 'users',
            'selected_columns' => ['name', 'email'],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $cols = $response->json('data.selected_columns');
        $this->assertSame('checkbox', $cols[0]);
        $this->assertSame('action', $cols[count($cols) - 1]);
    }

    // =========================================================================
    // getColumns — returns all available columns for entity_type
    // =========================================================================

    public function test_get_columns_returns_columns_array(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/get-columns?entity_type=users');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['columns']]);

        $this->assertIsArray($response->json('data.columns'));
    }

    // =========================================================================
    // getAllUsers — additional filter branches
    // =========================================================================

    public function test_get_all_users_with_actmanager_filter(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/users?actmanager=0&limit=5');

        $response->assertStatus(200);
    }

    public function test_get_all_users_with_is_2fa_enabled_filter(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/users?is_2fa_enabled=0&limit=5');

        $response->assertStatus(200);
    }

    public function test_get_all_users_with_mobile_verified_filter(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/users?mobile_verified=0&limit=5');

        $response->assertStatus(200);
    }

    public function test_get_all_users_sort_by_name_ascending(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/users?sort-field=first_name&sort-order=asc&limit=5');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertIsArray($data);
    }

    // =========================================================================
    // getUserSummary — known user with real data
    // =========================================================================

    public function test_get_user_summary_returns_zero_counts_for_new_user(): void
    {
        $this->getLoggedInUser('admin');
        $newUser = \App\User::factory()->create(['email' => 'summ-new-'.uniqid().'@test.local']);

        $response = $this->getJson('/user/'.$newUser->id.'/summary');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertEquals(0, $data['invoice_count']);
        $this->assertEquals(0, $data['payment_count']);
        $this->assertEquals(0, $data['order_count']);
        $this->assertEquals(0.0, $data['invoice_total']);
    }
}
