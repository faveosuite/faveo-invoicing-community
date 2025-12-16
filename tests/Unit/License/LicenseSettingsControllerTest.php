<?php

namespace Tests\Unit\License;

use App\Model\License\LicenseType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class LicenseSettingsControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getLoggedInUser('admin');
        $this->withoutMiddleware();
    }

    public function test_get_license_settings_success()
    {
        LicenseType::factory()->create(['name' => 'Starter']);
        LicenseType::factory()->create(['name' => 'Premium']);

        $response = $this->getJson('get-license-type');

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Starter'])
                 ->assertJsonFragment(['name' => 'Premium']);
    }

    public function test_get_license_types_search_filter()
    {
        LicenseType::factory()->create(['name' => 'Starter']);
        LicenseType::factory()->create(['name' => 'Business']);

        $response = $this->getJson('get-license-type?search-query=Start');

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Starter'])
                 ->assertJsonMissing(['name' => 'Business']);
    }

    public function test_create_license_saves_successfully()
    {

        $payload = ['name' => 'Enterprise'];

        $response = $this->postJson('create-license-type', $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => __('message.saved-successfully')]);

        $this->assertDatabaseHas('license_types', ['name' => 'Enterprise']);
    }

    public function test_update_license_updates_record()
    {
        $type = LicenseType::factory()->create(['name' => 'Basic']);

        $payload = ['name' => 'Updated Basic'];

        $response = $this->putJson("/update-license-type/{$type->id}", $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => __('message.updated-successfully')]);

        $this->assertDatabaseHas('license_types', ['id' => $type->id, 'name' => 'Updated Basic']);
    }

    public function test_delete_license_returns_error_if_no_ids()
    {
        $response = $this->deleteJson('/delete-license-type', ['select' => []]);

        $response->assertStatus(400)
                 ->assertJsonFragment(['message' => __('message.select-a-row')]);
    }

    public function test_delete_license_deletes_selected_records()
    {
        $type1 = LicenseType::factory()->create();
        $type2 = LicenseType::factory()->create();
        $type3 = LicenseType::factory()->create();

        $payload = ['select' => [$type1->id, $type2->id]];

        $response = $this->deleteJson('/delete-license-type', $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => __('message.deleted-successfully')]);

        $this->assertDatabaseMissing('license_types', ['id' => $type1->id]);
        $this->assertDatabaseMissing('license_types', ['id' => $type2->id]);
        $this->assertDatabaseHas('license_types', ['id' => $type3->id]);
    }

}
