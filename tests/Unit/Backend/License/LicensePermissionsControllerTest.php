<?php

namespace Tests\Unit\Backend\License;

use App\Model\License\LicensePermission;
use App\Model\License\LicenseType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class LicensePermissionsControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getLoggedInUser('admin');
        $this->withoutMiddleware();
    }

    public function test_get_permissions_returns_license_types_with_permissions(): void
    {
        // Create permissions
        $permA = LicensePermission::create(['permissions' => 'view']);
        $permB = LicensePermission::create(['permissions' => 'edit']);

        // Create license type and assign one permission
        $type = LicenseType::factory()->create(['name' => 'Standard']);
        $type->permissions()->attach($permA->id);

        $response = $this->getJson('/get-license-permission');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Standard'])
            ->assertJsonFragment(['permissions' => ['view']]);

        $response->assertJsonFragment([
            'id' => $permA->id,
            'permissions' => 'view',
            'assigned' => true,
        ]);

        $response->assertJsonFragment([
            'id' => $permB->id,
            'permissions' => 'edit',
            'assigned' => false,
        ]);
    }

    public function test_get_permissions_search_filter(): void
    {
        LicenseType::factory()->create(['name' => 'Starter']);
        LicenseType::factory()->create(['name' => 'Enterprise']);

        $response = $this->getJson('/get-license-permission?search-query=Start');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Starter'])
            ->assertJsonMissing(['name' => 'Enterprise']);
    }

    public function test_get_permissions_includes_all_permission_mapping(): void
    {
        $perm1 = LicensePermission::create(['permissions' => 'view']);
        LicensePermission::create(['permissions' => 'delete']);

        $type = LicenseType::factory()->create(['name' => 'Business']);
        $type->permissions()->attach($perm1->id);

        $response = $this->getJson('/get-license-permission');

        // Should include both permissions in all_permissions
        $response->assertJsonFragment(['permissions' => 'view']);
        $response->assertJsonFragment(['permissions' => 'delete']);

        // Check assigned flag
        $response->assertJsonFragment(['assigned' => true]);
        $response->assertJsonFragment(['assigned' => false]);
    }

    public function test_add_permission_updates_license_permissions(): void
    {
        $type = LicenseType::factory()->create(['name' => 'Professional']);

        $perm1 = LicensePermission::create(['permissions' => 'create']);
        $perm2 = LicensePermission::create(['permissions' => 'update']);

        $payload = [
            'licenseId' => $type->id,
            'permissionid' => [$perm1->id, $perm2->id],
        ];

        $response = $this->postJson('/add-permission', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.permissions_updated_successfully')]);

        // Assert they are attached
        $this->assertDatabaseHas('license_license_permissions', [
            'license_type_id' => $type->id,
            'license_permission_id' => $perm1->id,
        ]);

        $this->assertDatabaseHas('license_license_permissions', [
            'license_type_id' => $type->id,
            'license_permission_id' => $perm2->id,
        ]);
    }

    public function test_add_permission_allows_empty_sync(): void
    {
        $type = LicenseType::create(['name' => 'Basic']);
        $perm = LicensePermission::create(['permissions' => 'read']);

        // Attach initially
        $type->permissions()->attach($perm->id);

        // Now remove by passing empty array
        $payload = [
            'licenseId' => $type->id,
            'permissionid' => [],
        ];

        $response = $this->postJson('/add-permission', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.permissions_updated_successfully')]);

        $this->assertDatabaseMissing('license_license_permissions', [
            'license_type_id' => $type->id,
            'license_permission_id' => $perm->id,
        ]);
    }

    public function test_add_permission_fails_for_invalid_license_id(): void
    {
        $payload = [
            'licenseId' => 999999,
            'permissionid' => [1],
        ];

        $response = $this->postJson('/add-permission', $payload);

        $response->assertStatus(404)
            ->assertJsonFragment(['success' => false]);
    }
}
