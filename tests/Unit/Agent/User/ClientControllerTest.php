<?php

namespace Tests\Unit\Agent\User;

use App\ExportDetail;
use App\Jobs\AddUserToExternalService;
use App\User;
use Bus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use Tests\DBTestCase;

class ClientControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getLoggedInUser('admin');
    }

    #[Group('User')]
    public function test_can_fetch_all_users_paginated()
    {
        User::factory()->count(15)->create();

        $response = $this->getJson('/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => ['id', 'first_name', 'last_name', 'email'],
                    ],
                ],
            ]);

        $this->assertCount(10, $response->json('data.data'));
    }

    #[Group('User')]
    public function test_can_search_users_by_name_or_email()
    {
        $targetUser = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'unique@example.com',
        ]);

        User::factory()->count(5)->create();

        $response = $this->getJson('/users?search-query=unique@example.com');

        $response->assertStatus(200);
        $this->assertEquals($targetUser->id, $response->json('data.data.0.id'));
        $this->assertCount(1, $response->json('data.data'));
    }

    #[Group('User')]
    public function test_can_filter_users_by_country_and_role()
    {
        $usUser = User::factory()->create(['country' => 'USA', 'role' => 'manager']);
        $ukUser = User::factory()->create(['country' => 'UK', 'role' => 'user']);

        $response = $this->getJson('/users?country=USA&role=manager');

        $response->assertStatus(200);
        $data = $response->json('data.data');

        $this->assertCount(1, $data);
        $this->assertEquals($usUser->id, $data[0]['id']);
    }

    #[Group('User')]
    public function test_can_sort_users()
    {
        $userA = User::factory()->create(['first_name' => 'Alpha', 'created_at' => now()->subDays(1)]);
        $userB = User::factory()->create(['first_name' => 'Beta', 'created_at' => now()]);

        $response = $this->getJson('/users?sort-field=first_name&sort-order=asc');

        $data = $response->json('data.data');
        $this->assertEquals('Alpha', $data[0]['first_name']);
        $this->assertEquals('Beta', $data[1]['first_name']);
    }

    #[Group('User')]
    public function test_filter_by_registration_date_range()
    {
        $oldUser = User::factory()->create(['created_at' => '2023-01-01 10:00:00']);
        $newUser = User::factory()->create(['created_at' => '2023-12-01 10:00:00']);

        $response = $this->getJson('/users?reg_from=2023-11-01&reg_till=2023-12-31');

        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals($newUser->id, $data[0]['id']);
    }

    #[Group('User')]
    public function test_bulk_delete_fails_if_no_ids_provided()
    {
        $response = $this->deleteJson('/users', []);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => __('message.select-a-row')]);
    }

    #[Group('User')]
    public function test_bulk_delete_success_for_regular_users()
    {
        $users = User::factory()->count(3)->create();
        $ids = $users->pluck('id')->toArray();

        $response = $this->deleteJson('/users', ['user_ids' => $ids]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.user-suspend-successfully')]);

        $this->assertEquals(0, User::whereIn('id', $ids)->count());
    }

    #[Group('User')]
    public function test_bulk_delete_blocked_if_user_is_account_manager()
    {
        $manager = User::factory()->create([
            'first_name' => 'Boss', 'last_name' => 'Man', 'position' => 'account_manager',
        ]);

        User::factory()->create(['account_manager' => $manager->id]);

        $response = $this->deleteJson('/users', ['user_ids' => [$manager->id]]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'message' => __('message.deletion_blocked', [
                    'names' => 'Boss Man (Account Manager)',
                ]),
            ]);

        $this->assertDatabaseHas('users', ['id' => $manager->id]);
    }

    #[Group('User')]
    public function test_bulk_delete_blocked_if_user_is_sales_manager()
    {
        $salesManager = User::factory()->create(['first_name' => 'Sales', 'last_name' => 'Guru', 'position' => 'manager']);

        User::factory()->create(['manager' => $salesManager->id]);

        $response = $this->deleteJson('/users', ['user_ids' => [$salesManager->id]]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'message' => __('message.deletion_blocked', [
                    'names' => 'Sales Guru (Sales Manager)',
                ]),
            ]);

        $this->assertDatabaseHas('users', ['id' => $salesManager->id]);
    }

    #[Group('User')]
    public function test_create_user_successfully_with_job_dispatch()
    {
        Bus::fake();

        $payload = [
            'user_name' => 'testuser',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'newuser@example.com',
            'country' => 'US',
            'mobile' => '1234567890',
            'active' => true,
            'mobile_verified' => true,
            'company' => 'Test Company',
            'address' => 'Test Address',
            'timezone_id' => 81,
        ];

        $response = $this->putJson('/users', $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);

        // Assert Job was dispatched
        Bus::assertDispatched(AddUserToExternalService::class);
    }

    #[Group('User')]
    public function test_create_user_calculates_mobile_code_from_country()
    {
        Bus::fake();

        $payload = User::factory()->make([
            'country' => 'IN',
            'mobile_code' => null,
            'zip' => '110001',
        ])->toArray();

        $payload['password'] = 'secret';

        $this->putJson('/users', $payload);

        $user = User::where('email', $payload['email'])->first();

        $this->assertNotNull($user);
        $this->assertEquals('91', $user->mobile_code);
    }

    #[Group('User')]
    public function test_create_user_handles_exception_gracefully()
    {
        $existing = User::factory()->create(['email' => 'duplicate@test.com']);

        $response = $this->putJson('/users', [
            'first_name' => 'Dup',
            'last_name' => 'User',
            'email' => 'duplicate@test.com',
            'country' => 'US',
            'zip' => '12345',
            'password' => 'secret',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['email']);
    }

    #[Group('User')]
    public function test_get_edit_user_returns_data()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/user/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['email' => $user->email]);
    }

    #[Group('User')]
    public function test_update_user_successfully()
    {
        $user = User::factory()->create([
            'first_name' => 'OldName',
            'last_name' => 'OldLast',
            'company' => 'Old Company',
            'address' => 'Old Address',
            'mobile' => '9876543210',
            'timezone_id' => 1,
        ]);

        $payload = [
            'email' => $user->email,
            'company' => 'New Company',
            'address' => 'New Street 123',
            'mobile' => '9999999999',
            'timezone_id' => $user->timezone_id,
            'first_name' => 'NewName',
            'last_name' => 'Updated',
        ];

        $response = $this->patchJson("/user/{$user->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('message.updated-successfully'),
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'NewName',
            'last_name' => 'Updated',
            'company' => 'New Company',
        ]);
    }

    #[Group('User')]
    public function test_update_user_handles_non_existent_id()
    {
        $payload = [
            'email' => 'unique_test_'.uniqid().'@example.com',
            'company' => 'New Company',
            'address' => 'New Street 123',
            'mobile' => '9999999999',
            'timezone_id' => 1,
            'first_name' => 'NewName',
            'last_name' => 'Updated',
            'country' => 'IN',
        ];

        $response = $this->patchJson('/user/999999', $payload);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => __('message.user_not_found'),
        ]);
    }

    #[Group('User')]
    public function test_create_user_fails_with_validation_errors()
    {
        $response = $this->putJson('/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'country']);
    }

    #[Group('User')]
    public function test_update_user_fails_if_email_is_taken()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create(['email' => 'existing@example.com']);

        $payload = ['email' => 'existing@example.com'];

        $response = $this->patchJson("/user/{$user1->id}", $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Group('User')]
    public function test_search_returns_no_results_for_unfound_query()
    {
        User::factory()->count(5)->create();

        $response = $this->getJson('/users?search-query=nonexistentuser');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data.data'));
    }

    #[Group('User')]
    public function test_pagination_to_second_page()
    {
        User::factory()->count(15)->create();

        $totalUsers = User::count();
        $perPage = 10;

        $response = $this->getJson('/users?page=2');
        $response->assertStatus(200);

        // Calculate the expected number of users on the second page.
        $expectedCount = ($totalUsers > $perPage) ? $totalUsers - $perPage : 0;

        $this->assertCount($expectedCount, $response->json('data.data'));
    }

    #[Group('User')]
    public function test_filtering_with_no_results()
    {
        User::factory()->create(['country' => 'USA']);
        $response = $this->getJson('/users?country=Canada');
        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data.data'));
    }

    #[Group('User')]
    public function test_combined_filtering_for_country_and_role()
    {
        User::factory()->create(['country' => 'USA', 'role' => 'admin']);
        User::factory()->create(['country' => 'USA', 'role' => 'user']);
        $response = $this->getJson('/users?country=USA&role=admin');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    #[Group('User')]
    public function test_update_user_cannot_change_role()
    {
        $user = User::factory()->create(['role' => 'user']);
        $payload = ['role' => 'admin'];
        $this->patchJson("/user/{$user->id}", $payload);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'user']);
    }

    #[Group('User')]
    public function test_bulk_delete_with_mixed_user_types()
    {
        $deletableUser = User::factory()->create();
        $manager = User::factory()->create(['position' => 'account_manager']);
        User::factory()->create(['account_manager' => $manager->id]);

        $response = $this->deleteJson('/users', [
            'user_ids' => [$deletableUser->id, $manager->id],
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseHas('users', ['id' => $deletableUser->id]);
        $this->assertDatabaseHas('users', ['id' => $manager->id]);
    }

    public function test_it_downloads_export_file_successfully()
    {
        $folderName = 'users_export_'.auth()->id().'_'.now()->format('Ymd_His').'_XLSX';
        $folderPath = storage_path('app/public/export/'.$folderName);

        if (! is_dir(dirname($folderPath))) {
            mkdir(dirname($folderPath), 0777, true);
        }
        file_put_contents($folderPath, 'sample excel content');

        $detail = ExportDetail::create([
            'user_id' => auth()->id(),
            'file' => $folderName,
            'file_path' => $folderPath,
            'name' => 'users',
        ]);

        $response = $this->getJson(route('download.exported.file', $detail->id));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');

        // ZIP should be created automatically
        $zipPath = storage_path('app/public/export/'.$folderName);
        $this->assertFileExists($zipPath);
    }

    public function test_it_returns_error_if_export_detail_not_found()
    {
        $response = $this->getJson(route('download.exported.file', 99999));

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_it_returns_error_if_download_link_is_expired()
    {
        $folderName = 'users_export_'.auth()->id().'_'.now()->format('Ymd_His').'_XLSX';
        $folderPath = storage_path('app/public/export/'.$folderName);

        file_put_contents($folderPath, 'old file');

        $detail = ExportDetail::create([
            'user_id' => auth()->id(),
            'file' => $folderName,
            'file_path' => $folderPath,
            'name' => 'users',
            'created_at' => Carbon::now()->subDays(7),
        ]);

        $response = $this->getJson(route('download.exported.file', $detail->id));

        $response->assertJson([
            'success' => false,
            'message' => __('message.download_link_expired'),
        ]);
    }

    public function test_it_returns_error_if_file_not_found()
    {
        $folderName = 'users_export_'.auth()->id().'_'.now()->format('Ymd_His').'_XLSX';
        $folderPath = storage_path('app/public/export/'.$folderName);

        $detail = ExportDetail::create([
            'user_id' => auth()->id(),
            'file' => $folderName,
            'file_path' => $folderPath,
            'name' => 'users',
        ]);

        $response = $this->getJson(route('download.exported.file', $detail->id));

        $response->assertJson([
            'success' => false,
            'message' => __('message.file_not_found'),
        ]);
    }

    public function test_it_zips_directory_contents_successfully()
    {
        $folderName = 'users_export_'.auth()->id().'_'.now()->format('Ymd_His').'_XLSX';
        $folderPath = storage_path('app/public/export/'.$folderName);

        // create two files in directory
        file_put_contents($folderPath, 'hello');
        file_put_contents($folderPath, 'world');

        $detail = ExportDetail::create([
            'user_id' => auth()->id(),
            'file' => $folderName,
            'file_path' => $folderPath,
            'name' => 'users',
        ]);

        $response = $this->getJson(route('download.exported.file', $detail->id));

        $response->assertStatus(200);

        $zipPath = storage_path('app/public/export/'.$folderName.'.zip');
        $this->assertFileExists($zipPath);
    }
}
