<?php

namespace Tests\Unit\Common;

use App\Model\Common\ManagerSetting;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class SystemManagerControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected $admin;

    public function setUp(): void
    {
        parent::setUp();

        $this->getLoggedInUser('admin');

        $this->withoutMiddleware();
    }

    public function test_it_shows_system_managers()
    {
        User::factory()->create([
            'role' => 'admin',
            'position' => 'account_manager',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        User::factory()->create([
            'role' => 'admin',
            'position' => 'manager',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
        ]);

        $response = $this->get(url('system-managers'));
        $response->assertStatus(200)
            ->assertViewIs('themes.default1.common.system-managers');
        $response->assertViewHas(['accountManagers', 'salesManager', 'accountManagersAutoAssign', 'salesManagerAutoAssign']);
    }

    public function test_it_returns_filtered_admin_users()
    {
        User::factory()->create([
            'role' => 'admin',
            'first_name' => 'Filtered',
            'email' => 'filter@example.com',
        ]);

        $response = $this->getJson(route('search-admins', ['q' => 'filter']));

        $response->assertOk()->assertJsonFragment([
            'text' => 'filter@example.com',
        ]);
    }

    public function test_it_replaces_account_manager()
    {
        $old = User::factory()->create(['role' => 'admin', 'position' => 'account_manager']);
        $new = User::factory()->create(['role' => 'admin']);

        $userAssigned = User::factory()->create(['account_manager' => $old->id]);

        $response = $this->postJson(url('replace-acc-manager'), [
            'existingAccManager' => $old->id,
            'newAccManager' => $new->id,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('users', [
            'id' => $new->id,
            'position' => 'account_manager',
        ]);
        $this->assertEquals($new->id, $userAssigned->fresh()->account_manager);
    }

    public function test_it_throws_error_if_same_account_manager_is_selected()
    {
        $user = User::factory()->create(['role' => 'admin', 'position' => 'account_manager']);

        $response = $this->postJson(url('replace-acc-manager'), [
            'existingAccManager' => $user->id,
            'newAccManager' => $user->id,
        ]);

        $response->assertJson(['success' => false]);
    }

    public function test_it_replaces_sales_manager()
    {
        $old = User::factory()->create(['role' => 'admin', 'position' => 'manager']);
        $new = User::factory()->create(['role' => 'admin']);

        $userAssigned = User::factory()->create(['manager' => $old->id]);

        $response = $this->postJson(url('replace-sales-manager'), [
            'existingSaleManager' => $old->id,
            'newSaleManager' => $new->id,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('users', [
            'id' => $new->id,
            'position' => 'manager',
        ]);
        $this->assertEquals($new->id, $userAssigned->fresh()->manager);
    }

    public function test_it_throws_error_if_same_sales_manager_is_selected()
    {
        $user = User::factory()->create(['role' => 'admin', 'position' => 'manager']);

        $response = $this->postJson(url('replace-sales-manager'), [
            'existingSaleManager' => $user->id,
            'newSaleManager' => $user->id,
        ]);

        $response->assertJson(['success' => false]);
    }

    public function test_it_sets_auto_assign_flag()
    {
        ManagerSetting::where('manager_role', 'account')->update([
            'auto_assign' => false,
        ]);

        $response = $this->postJson(url('managerAutoAssign'), [
            'manager_role' => 'account',
            'status' => true,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertTrue((bool) ManagerSetting::where('manager_role', 'account')->first()->auto_assign);
    }

    public function test_it_fails_on_invalid_auto_assign_input()
    {
        $response = $this->postJson(url('managerAutoAssign'), [
            'manager_role' => 'invalid_role',
            'status' => 'not-a-boolean',
        ]);

        $response->assertStatus(422);
    }
}
