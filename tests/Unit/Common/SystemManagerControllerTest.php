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

    public function test_it_updates_manager_settings()
    {
        // Create old and new account managers
        $oldAcc = User::factory()->create(['role' => 'admin', 'position' => 'account_manager']);
        $newAcc = User::factory()->create(['role' => 'admin']);

        // Assign old account manager to a user
        $userAcc = User::factory()->create(['account_manager' => $oldAcc->id]);

        // Create old and new sales managers
        $oldSales = User::factory()->create(['role' => 'admin', 'position' => 'manager']);
        $newSales = User::factory()->create(['role' => 'admin']);

        // Assign old sales manager to a user
        $userSales = User::factory()->create(['manager' => $oldSales->id]);

        // Set initial auto_assign values
        ManagerSetting::updateOrCreate(
            ['manager_role' => 'account'],
            ['auto_assign' => false]
        );
        ManagerSetting::updateOrCreate(
            ['manager_role' => 'sales'],
            ['auto_assign' => false]
        );

        $response = $this->postJson(url('updateSystemManager'), [
            'existingAccManager' => $oldAcc->id,
            'newAccManager' => $newAcc->id,
            'existingSaleManager' => $oldSales->id,
            'newSaleManager' => $newSales->id,
            'autoAssignAccount' => true,
            'autoAssignSales' => true,
        ]);

        $response->assertJson(['success' => true]);

        $this->assertEquals($newAcc->id, $userAcc->fresh()->account_manager);
        $this->assertEquals($newSales->id, $userSales->fresh()->manager);

        $this->assertDatabaseHas('users', [
            'id' => $newAcc->id,
            'position' => 'account_manager',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $newSales->id,
            'position' => 'manager',
        ]);

        $this->assertTrue((bool) ManagerSetting::where('manager_role', 'account')->value('auto_assign'));
        $this->assertTrue((bool) ManagerSetting::where('manager_role', 'sales')->value('auto_assign'));
    }

    public function test_it_fails_when_same_manager_is_selected()
    {
        $user = User::factory()->create(['role' => 'admin', 'position' => 'manager']);

        $response = $this->postJson(url('updateSystemManager'), [
            'existingAccManager' => $user->id,
            'newAccManager' => $user->id,
            'existingSaleManager' => $user->id,
            'newSaleManager' => $user->id,
            'autoAssignAccount' => true,
            'autoAssignSales' => true,
        ]);

        $response->assertStatus(422);
    }

    public function test_it_only_updates_auto_assign_flags()
    {
        ManagerSetting::updateOrCreate(['manager_role' => 'account'], ['auto_assign' => false]);
        ManagerSetting::updateOrCreate(['manager_role' => 'sales'], ['auto_assign' => true]);

        $response = $this->postJson(url('updateSystemManager'), [
            'autoAssignAccount' => true,
            'autoAssignSales' => false,
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('manager_settings', [
            'manager_role' => 'account',
            'auto_assign' => true,
        ]);

        $this->assertDatabaseHas('manager_settings', [
            'manager_role' => 'sales',
            'auto_assign' => false,
        ]);
    }
}
