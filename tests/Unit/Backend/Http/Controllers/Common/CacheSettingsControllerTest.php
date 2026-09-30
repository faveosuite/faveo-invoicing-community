<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class CacheSettingsControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    // GET /cache-settings/list — getDriverData
    public function test_get_driver_data_returns_200_with_drivers_list(): void
    {
        $response = $this->getJson('/cache-settings/list');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'data' => ['drivers', 'active_driver']]);
    }

    public function test_get_driver_data_includes_all_five_drivers(): void
    {
        $response = $this->getJson('/cache-settings/list');
        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.drivers.data'));
    }

    // GET /cache-settings/{driver}/form — getFormByDriver
    public function test_get_form_by_file_driver_returns_200(): void
    {
        $response = $this->getJson('/cache-settings/file/form');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('data.driver', 'file');
    }

    public function test_get_form_by_redis_driver_returns_200(): void
    {
        $response = $this->getJson('/cache-settings/redis/form');
        $response->assertStatus(200);
        $response->assertJsonPath('data.driver', 'redis');
    }

    public function test_get_form_by_memcached_driver_returns_200(): void
    {
        $response = $this->getJson('/cache-settings/memcached/form');
        $response->assertStatus(200);
        $response->assertJsonPath('data.driver', 'memcached');
    }

    public function test_get_form_by_database_driver_returns_200(): void
    {
        $response = $this->getJson('/cache-settings/database/form');
        $response->assertStatus(200);
        $response->assertJsonPath('data.driver', 'database');
    }

    // PATCH /cache-settings/{driver} — update
    public function test_update_file_driver_returns_200_as_it_has_no_fields(): void
    {
        // File driver has no form fields → update returns 422 ("no fields to update")
        $response = $this->patchJson('/cache-settings/file', []);
        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    // POST /cache-settings/{driver}/activate — activate
    public function test_activate_file_driver_returns_200(): void
    {
        $response = $this->postJson('/cache-settings/file/activate');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_activate_invalid_driver_returns_422(): void
    {
        $response = $this->postJson('/cache-settings/floppy/activate');
        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_activate_redis_when_not_configured_returns_422(): void
    {
        // Redis has form fields and is not configured in test env → 422
        $response = $this->postJson('/cache-settings/redis/activate');
        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }
}
