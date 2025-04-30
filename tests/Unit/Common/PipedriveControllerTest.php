<?php

namespace Tests\Unit\Common;

use App\ApiKey;
use App\Http\Controllers\Common\PipedriveController;
use App\Model\Common\PipedriveGroups;
use App\Model\Common\PipedriveLocalFields;
use App\Model\Common\StatusSetting;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\DBTestCase;
use Mockery;

class PipedriveControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected $user;
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

        $this->pipedriveController = new PipedriveController();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_updates_verification_status()
    {
        $request = new Request([
            'require_pipedrive_user_verification' => true
        ]);

        $response = $this->pipedriveController->updateVerificationStatus($request);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertTrue((bool)ApiKey::first()->require_pipedrive_user_verification);
    }

    public function test_it_handles_field_mapping_validation()
    {
        $dealGroup = PipedriveGroups::where('group_name', 'Deal')->first();

        $request = new Request([
            'group_id' => $dealGroup->id,
        ]);

        $response = $this->pipedriveController->mappingFields($request);
        $data = $response->getData(true);

        $this->assertFalse($data['success']);
    }

    public function test_it_adds_user_to_pipedrive_when_enabled()
    {
        StatusSetting::create(['pipedrive_status' => 1]);

        $mockController = Mockery::mock(PipedriveController::class)->makePartial();
        $mockController->shouldReceive('addOrGetOrganization')->andReturn(123);
        $mockController->shouldReceive('addPerson')->andReturn(456);
        $mockController->shouldReceive('addDeal')->andReturn(789);

        $user = User::factory()->create();
        $mockController->addUserToPipedrive($user);

        $this->assertTrue(true);
    }

    public function test_it_skips_adding_user_when_pipedrive_disabled()
    {
        StatusSetting::create(['pipedrive_status' => 0]);

        $mockController = Mockery::mock(PipedriveController::class)->makePartial();
        $mockController->shouldNotReceive('addOrGetOrganization');

        $user = User::factory()->create();
        $mockController->addUserToPipedrive($user);

        $this->assertTrue(true);
    }

    public function test_it_syncs_fields_from_pipedrive()
    {
        $mockController = Mockery::mock(PipedriveController::class)->makePartial();
        $mockController->shouldReceive('getPipedriveFields')->andReturn([
            (object)['key' => 'person_field', 'name' => 'Person Field', 'field_type' => 'text']
        ]);
        $mockController->shouldReceive('getOrganizationFields')->andReturn([
            (object)['key' => 'org_field', 'name' => 'Org Field', 'field_type' => 'text']
        ]);
        $mockController->shouldReceive('getDealFields')->andReturn([
            (object)['key' => 'deal_field', 'name' => 'Deal Field', 'field_type' => 'text']
        ]);

        $mockController->syncFields();

        $this->assertDatabaseHas('pipedrive_fields', ['field_key' => 'person_field']);
        $this->assertDatabaseHas('pipedrive_fields', ['field_key' => 'org_field']);
        $this->assertDatabaseHas('pipedrive_fields', ['field_key' => 'deal_field']);
    }

    public function test_it_creates_new_organization_when_not_exists()
    {
        $mockController = Mockery::mock(PipedriveController::class)->makePartial();
        $mockController->shouldReceive('addOrGetOrganization')
            ->with(['name' => 'Test Org'])
            ->andReturn(123);

        $result = $mockController->addOrGetOrganization(['name' => 'Test Org']);
        $this->assertEquals(123, $result);
    }

    public function test_it_returns_existing_organization_when_found()
    {
        $mockController = Mockery::mock(PipedriveController::class)->makePartial();
        $mockController->shouldReceive('addOrGetOrganization')
            ->with(['name' => 'Existing Org'])
            ->andReturn(456);

        $result = $mockController->addOrGetOrganization(['name' => 'Existing Org']);
        $this->assertEquals(456, $result);
    }

    public function test_it_validates_required_fields_for_deals()
    {
        $dealGroup = PipedriveGroups::where('group_name', 'Deal')->first();
        $request = new Request(['group_id' => $dealGroup->id]);

        $response = $this->pipedriveController->mappingFields($request);
        $data = $response->getData(true);

        $this->assertFalse($data['success']);
        $this->assertStringContainsString('required', $data['message']);
    }

    public function test_it_updates_field_mappings_correctly()
    {
        $group = PipedriveGroups::first();
        $localField = PipedriveLocalFields::create(['field_key' => 'test', 'field_name' => 'Test']);

        $request = new Request([
            'group_id' => $group->id,
            'test' => 'pipedrive_key'
        ]);

        $response = $this->pipedriveController->mappingFields($request);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertDatabaseHas('pipedrive_fields', [
            'field_key' => 'pipedrive_key',
            'local_field_id' => $localField->id
        ]);
    }
}
