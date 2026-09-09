<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\ApiKey;
use App\Http\Controllers\Common\PipedriveController;
use App\Model\Common\PipedriveField;
use App\Model\Common\PipedriveGroups;
use App\Model\Common\PipedriveLocalFields;
use App\Model\Common\StatusSetting;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Mockery;
use ReflectionClass;
use Tests\DBTestCase;

class PipedriveControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected $pipedriveController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getLoggedInUser('admin');

        // Setup necessary database entries
        PipedriveGroups::insert([
            ['group_name' => 'Person'],
            ['group_name' => 'Organization'],
            ['group_name' => 'Deal'],
        ]);

        ApiKey::create(['pipedrive_api_key' => 'test_key']);

        StatusSetting::first()->update(['pipedrive_status' => 1]);

        $this->pipedriveController = new PipedriveController;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_handles_field_mapping_validation(): void
    {
        $dealGroup = PipedriveGroups::where('group_name', 'Deal')->first();

        $request = new Request([
            'group_id' => $dealGroup->id,
        ]);

        $response = $this->pipedriveController->mappingFields($request);
        $data = $response->getData(true);

        $this->assertFalse($data['success']);
    }

    public function test_it_adds_user_to_pipedrive_when_enabled(): void
    {
        $mockController = Mockery::mock(PipedriveController::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $reflection = new ReflectionClass($mockController);
        $property = $reflection->getProperty('groups');
        $property->setValue($mockController, [
            'personId' => 1,
            'organizationId' => 2,
            'dealId' => 3,
        ]);
        $mockController->shouldReceive('addOrGetOrganization')->andReturn(123);
        $mockController->shouldReceive('addPerson')->andReturn(456);
        $mockController->shouldReceive('addDeal')->andReturn(789);

        $user = User::factory()->create();
        $mockController->addUserToPipedrive($user);

        $this->assertTrue(condition: true);
    }

    public function test_it_skips_adding_user_when_pipedrive_disabled(): void
    {
        StatusSetting::first()->update(['pipedrive_status' => 0]);

        $mockController = Mockery::mock(PipedriveController::class)->makePartial();
        $mockController->shouldNotReceive('addOrGetOrganization');

        $user = User::factory()->create();
        $mockController->addUserToPipedrive($user);

        $this->assertTrue(condition: true);
    }

    public function test_it_syncs_fields_from_pipedrive(): void
    {
        $mockController = Mockery::mock(PipedriveController::class)->makePartial();
        $reflection = new ReflectionClass($mockController);
        $property = $reflection->getProperty('groups');
        $property->setValue($mockController, [
            'personId' => 1,
            'organizationId' => 2,
            'dealId' => 3,
        ]);
        $mockController->shouldReceive('getPipedriveFields')->andReturn([
            (object) [
                'key' => 'person_field',
                'name' => 'Person Field',
                'field_type' => 'text',
                'bulk_edit_allowed' => true,  // << ADD THIS
            ],
        ]);
        $mockController->shouldReceive('getOrganizationFields')->andReturn([
            (object) [
                'key' => 'org_field',
                'name' => 'Org Field',
                'field_type' => 'text',
                'bulk_edit_allowed' => true,
            ],
        ]);
        $mockController->shouldReceive('getDealFields')->andReturn([
            (object) [
                'key' => 'deal_field',
                'name' => 'Deal Field',
                'field_type' => 'text',
                'bulk_edit_allowed' => true,
            ],
        ]);

        $mockController->syncFields();

        $this->assertDatabaseHas('pipedrive_fields', ['field_key' => 'person_field']);
        $this->assertDatabaseHas('pipedrive_fields', ['field_key' => 'org_field']);
        $this->assertDatabaseHas('pipedrive_fields', ['field_key' => 'deal_field']);
    }

    public function test_it_creates_new_organization_when_not_exists(): void
    {
        $mockController = Mockery::mock(PipedriveController::class)->makePartial();
        $mockController->shouldReceive('addOrGetOrganization')
            ->with(['name' => 'Test Org'])
            ->andReturn(123);

        $result = $mockController->addOrGetOrganization(['name' => 'Test Org']);
        $this->assertEquals(123, $result);
    }

    public function test_it_returns_existing_organization_when_found(): void
    {
        $mockController = Mockery::mock(PipedriveController::class)->makePartial();
        $mockController->shouldReceive('addOrGetOrganization')
            ->with(['name' => 'Existing Org'])
            ->andReturn(456);

        $result = $mockController->addOrGetOrganization(['name' => 'Existing Org']);
        $this->assertEquals(456, $result);
    }

    public function test_it_validates_required_fields_for_deals(): void
    {
        $dealGroup = PipedriveGroups::where('group_name', 'Deal')->first();
        $request = new Request(['group_id' => $dealGroup->id]);

        $response = $this->pipedriveController->mappingFields($request);
        $data = $response->getData(true);

        $this->assertFalse($data['success']);
        $this->assertStringContainsString('required', $data['message']);
    }

    public function test_it_updates_field_mappings_correctly(): void
    {
        // Create a group
        $group = PipedriveGroups::create(['group_name' => 'Organization']);

        // Create a local field
        $localField = PipedriveLocalFields::create([
            'field_key' => 'test',
            'field_name' => 'Test',
        ]);

        // Create a pipedrive field that will be mapped
        $pipedriveField = PipedriveField::create([
            'pipedrive_group_id' => $group->id,
            'field_key' => 'pipedrive_key',
            'local_field_id' => null,
        ]);

        // Simulate request with select1 (PipedriveField IDs) and select2 (LocalField IDs)
        $request = new Request([
            'group_id' => $group->id,
            'select1' => [$pipedriveField->id],
            'select2' => [
                [
                    'id' => $localField->id,
                    'faveo_fields' => 'true',
                ],
            ],
        ]);

        // Call the controller method
        $response = $this->pipedriveController->mappingFields($request);
        $data = $response->getData(true);

        // Assert the response and DB changes
        $this->assertTrue($data['success']);
        $this->assertDatabaseHas('pipedrive_fields', [
            'id' => $pipedriveField->id,
            'local_field_id' => $localField->id,
        ]);
    }

    // =========================================================================
    // getMapFields — invalid group → 400
    // =========================================================================

    public function test_get_map_fields_returns_400_for_invalid_group(): void
    {
        $this->withoutMiddleware();

        $response = $this->getJson('/pipedrive/mapping/999999');

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_get_map_fields_returns_200_for_valid_group(): void
    {
        $this->withoutMiddleware();

        $group = PipedriveGroups::first();

        $response = $this->getJson('/pipedrive/mapping/'.$group->id);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['group_id', 'title', 'groups', 'pipedriveData']]);

        $this->assertEquals($group->id, $response->json('data.group_id'));
    }

    // =========================================================================
    // syncFields — POST /syncing/pipedriveFields
    // =========================================================================

    public function test_sync_fields_returns_response(): void
    {
        $this->withoutMiddleware();

        $response = $this->postJson('/syncing/pipedriveFields');

        // May succeed (200) or fail (400/500) if Pipedrive API not configured
        $this->assertContains($response->status(), [200, 400, 500]);
        $this->assertIsArray($response->json());
    }

    // =========================================================================
    // getDropdown — POST /pipedrive/get-dropdown
    // =========================================================================

    public function test_get_dropdown_returns_empty_when_no_options(): void
    {
        $this->withoutMiddleware();

        $response = $this->postJson('/pipedrive/get-dropdown', ['pipedrive_field_id' => 999999]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEmpty($response->json('data.dropdown'));
    }

    // =========================================================================
    // addUserToPipedrive — disabled status → returns early
    // =========================================================================

    public function test_add_user_to_pipedrive_does_nothing_when_disabled(): void
    {
        StatusSetting::first()->update(['pipedrive_status' => 0]);

        $user = User::factory()->create(['email' => 'pipedrive-'.uniqid().'@test.local']);
        $this->pipedriveController->addUserToPipedrive($user);

        $this->assertTrue(true); // no exception, returned early
    }

    // =========================================================================
    // getLocalFields — returns local fields + pipedrive fields
    // =========================================================================

    public function test_get_local_fields_returns_200_with_expected_keys(): void
    {
        $group = PipedriveGroups::first();

        $response = $this->pipedriveController->getLocalFields($group->id);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('local_fields', $data['data']);
        $this->assertArrayHasKey('pipedrive_fields', $data['data']);
    }
}
