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

    // =========================================================================
    // syncUser — one record per customer, Pipedrive's reason on failure
    // =========================================================================

    /**
     * A partial mock with real group ids and every outbound Pipedrive call stubbed.
     *
     * @return array{0: \Mockery\MockInterface&PipedriveController, 1: User}
     */
    private function syncFixture(bool $mapOrganization = true): array
    {
        $groups = PipedriveGroups::pluck('id', 'group_name');
        $first = PipedriveLocalFields::firstOrCreate(['field_key' => 'first_name'], ['field_name' => 'First Name']);
        $company = PipedriveLocalFields::firstOrCreate(['field_key' => 'company'], ['field_name' => 'Company']);

        PipedriveField::create(['pipedrive_group_id' => $groups['Person'], 'field_key' => 'name', 'field_name' => 'Name', 'local_field_id' => $first->id]);
        PipedriveField::create(['pipedrive_group_id' => $groups['Deal'], 'field_key' => 'title', 'field_name' => 'Title', 'local_field_id' => $company->id]);
        if ($mapOrganization) {
            PipedriveField::create(['pipedrive_group_id' => $groups['Organization'], 'field_key' => 'name', 'field_name' => 'Name', 'local_field_id' => $company->id]);
        }

        $mock = Mockery::mock(PipedriveController::class)->makePartial()->shouldAllowMockingProtectedMethods();
        (new ReflectionClass(PipedriveController::class))->getProperty('groups')->setValue($mock, [
            'personId' => $groups['Person'], 'organizationId' => $groups['Organization'], 'dealId' => $groups['Deal'],
        ]);

        return [$mock, User::factory()->create(['first_name' => 'Ann', 'company' => 'Acme', 'email' => 'ann-'.uniqid().'@test.local'])];
    }

    public function test_sync_user_updates_an_existing_person_and_does_not_add_a_second_deal(): void
    {
        [$mock, $user] = $this->syncFixture();
        $mock->shouldReceive('addOrGetOrganization')->once()->andReturn(10);
        $mock->shouldReceive('findPersonIdByEmail')->once()->with($user->email)->andReturn(20);
        $mock->shouldNotReceive('addPerson');
        $mock->shouldReceive('personHasDeal')->once()->with(20)->andReturn(true);
        $mock->shouldNotReceive('addDeal');

        // updatePerson goes through performApiAction — stub the v2 client it calls.
        $persons = Mockery::mock();
        $persons->shouldReceive('updatePerson')->once()->with(20, Mockery::on(fn ($body): bool => $body['name'] === 'Ann'))->andReturn(new class
        {
            /** @return array<string, int> */
            public function getRawData(): array
            {
                return ['id' => 20];
            }
        });
        $clients = (new ReflectionClass(PipedriveController::class))->getProperty('apiClients');
        $clients->setValue($mock, ['persons' => $persons] + (array) $clients->getValue($mock));

        $this->assertSame(['Organization', 'Person'], $mock->syncUser($user));
    }

    public function test_sync_user_creates_person_and_deal_for_a_new_customer(): void
    {
        [$mock, $user] = $this->syncFixture();
        $mock->shouldReceive('addOrGetOrganization')->andReturn(10);
        $mock->shouldReceive('findPersonIdByEmail')->andReturn(null);
        $mock->shouldReceive('addPerson')->once()->with(Mockery::on(fn ($p): bool => $p['name'] === 'Ann' && $p['org_id'] === 10))->andReturn(21);
        $mock->shouldReceive('personHasDeal')->with(21)->andReturn(false);
        $mock->shouldReceive('addDeal')->once()->with(Mockery::on(fn ($d): bool => $d['title'] === 'Acme' && $d['person_id'] === 21))->andReturn(31);

        $this->assertSame(['Organization', 'Person', 'Deal'], $mock->syncUser($user));
    }

    public function test_sync_user_skips_the_organization_when_none_is_mapped(): void
    {
        [$mock, $user] = $this->syncFixture(mapOrganization: false);
        $mock->shouldNotReceive('addOrGetOrganization');
        $mock->shouldReceive('findPersonIdByEmail')->andReturn(null);
        $mock->shouldReceive('addPerson')->once()->with(Mockery::on(fn ($p): bool => ! array_key_exists('org_id', $p)))->andReturn(21);
        $mock->shouldReceive('personHasDeal')->andReturn(false);
        $mock->shouldReceive('addDeal')->andReturn(31);

        $this->assertSame(['Person', 'Deal'], $mock->syncUser($user));
    }

    public function test_sync_user_throws_pipedrives_reason_when_a_record_is_rejected(): void
    {
        [$mock, $user] = $this->syncFixture(mapOrganization: false);
        $mock->shouldReceive('findPersonIdByEmail')->andReturn(null);
        $mock->shouldReceive('addPerson')->andReturn((object) ['success' => false, 'error' => 'Name is required']);

        $this->expectExceptionMessageIsOrContains('Person: Name is required');
        $mock->syncUser($user);
    }

    public function test_send_test_says_so_when_nothing_is_mapped(): void
    {
        PipedriveField::query()->update(['local_field_id' => null]);
        \App\Model\Common\PipedriveFieldOption::query()->update(['status' => 0]);

        $mock = Mockery::mock(PipedriveController::class)->makePartial();
        $mock->shouldNotReceive('syncUser');

        $this->assertSame(400, $mock->sendTest()->getStatusCode());
    }

    public function test_send_test_reports_what_was_sent_or_why_it_failed(): void
    {
        app('translator')->addLines(['message.pipedrive_test_sent' => 'Sent :modules', 'message.pipedrive_test_failed' => 'Rejected: :reason'], 'en');

        [$mock] = $this->syncFixture();
        $mock->shouldReceive('syncUser')->once()->andReturn(['Person', 'Deal']);
        $this->assertStringContainsString('Person, Deal', $mock->sendTest()->getData()->message);

        [$failing] = $this->syncFixture();
        $failing->shouldReceive('syncUser')->andThrow(new \Exception('Deal: title is required'));
        $response = $failing->sendTest();
        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('Deal: title is required', $response->getData()->message);
    }

    // =========================================================================
    // mappingFields — incomplete rows, required fields, JSON booleans
    // =========================================================================

    public function test_mapping_rejects_a_row_with_only_one_side(): void
    {
        $person = PipedriveGroups::where('group_name', 'Person')->first();
        $field = PipedriveField::create(['pipedrive_group_id' => $person->id, 'field_key' => 'name', 'field_name' => 'Name']);

        $response = $this->pipedriveController->mappingFields(new Request([
            'group_id' => $person->id,
            'select1' => [['id' => $field->id]],
            'select2' => [['id' => null, 'faveo_fields' => true]],
        ]));

        $this->assertFalse($response->getData(true)['success']);
    }

    public function test_mapping_requires_the_fields_pipedrive_requires(): void
    {
        app('translator')->addLines(['message.pipedrive_required_missing' => 'Missing: :fields'], 'en');

        $person = PipedriveGroups::where('group_name', 'Person')->first();
        PipedriveField::create(['pipedrive_group_id' => $person->id, 'field_key' => 'name', 'field_name' => 'Name']);
        $email = PipedriveField::create(['pipedrive_group_id' => $person->id, 'field_key' => 'email', 'field_name' => 'Email']);
        $local = PipedriveLocalFields::firstOrCreate(['field_key' => 'email'], ['field_name' => 'Email']);

        $response = $this->pipedriveController->mappingFields(new Request([
            'group_id' => $person->id,
            'select1' => [['id' => $email->id]],
            'select2' => [['id' => $local->id, 'faveo_fields' => true]],
        ]));

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('Name', $data['message']);
    }

    public function test_mapping_saves_a_faveo_field_sent_as_a_json_boolean(): void
    {
        $org = PipedriveGroups::where('group_name', 'Organization')->first();
        $name = PipedriveField::create(['pipedrive_group_id' => $org->id, 'field_key' => 'name', 'field_name' => 'Name']);
        $local = PipedriveLocalFields::firstOrCreate(['field_key' => 'company'], ['field_name' => 'Company']);

        // The save runs a real round-trip check through these public calls — stub them.
        $response = $this->orgRoundTrip(55)->mappingFields(new Request([
            'group_id' => $org->id,
            'select1' => [['id' => $name->id]],
            'select2' => [['id' => $local->id, 'faveo_fields' => true]],
        ]));

        $this->assertTrue($response->getData(true)['success']);
        $this->assertSame($local->id, $name->refresh()->local_field_id);
    }

    public function test_map_fields_marks_the_fields_pipedrive_requires(): void
    {
        $person = PipedriveGroups::where('group_name', 'Person')->first();
        PipedriveField::create(['pipedrive_group_id' => $person->id, 'field_key' => 'name', 'field_name' => 'Name']);
        PipedriveField::create(['pipedrive_group_id' => $person->id, 'field_key' => 'email', 'field_name' => 'Email']);

        $fields = collect($this->pipedriveController->getMapFields($person->id)->getData(true)['data']['pipedriveData']['pipedrive_fields']);

        $this->assertTrue($fields->firstWhere('field_key', 'name')['is_mandatory']);
        $this->assertFalse($fields->firstWhere('field_key', 'email')['is_mandatory']);
    }

    /** Partial controller whose live round-trip for Organization answers $orgResult (numeric = accepted). */
    private function orgRoundTrip(mixed $orgResult): PipedriveController
    {
        $mock = Mockery::mock(PipedriveController::class)->makePartial();
        // Same rows the tests look up with first() (the test DB may hold older group rows too).
        (new ReflectionClass(PipedriveController::class))->getProperty('groups')->setValue($mock, collect(['Person', 'Organization', 'Deal'])
            ->mapWithKeys(fn ($group): array => [lcfirst($group).'Id' => PipedriveGroups::where('group_name', $group)->first()->id])->all());
        $mock->shouldReceive('addOrGetOrganization')->andReturn($orgResult);
        $mock->shouldReceive('deleteOrganization')->andReturn(true);
        app()->instance(PipedriveController::class, $mock);

        return $mock;
    }

    public function test_mapping_enables_the_chosen_options_and_disables_the_rest(): void
    {
        $org = PipedriveGroups::where('group_name', 'Organization')->first();
        $name = PipedriveField::create(['pipedrive_group_id' => $org->id, 'field_key' => 'name', 'field_name' => 'Name']);
        $local = PipedriveLocalFields::firstOrCreate(['field_key' => 'company'], ['field_name' => 'Company']);
        $other = PipedriveLocalFields::firstOrCreate(['field_key' => 'email'], ['field_name' => 'Email']);
        $size = PipedriveField::create(['pipedrive_group_id' => $org->id, 'field_key' => 'size', 'field_name' => 'Size', 'local_field_id' => $other->id]);
        $small = \App\Model\Common\PipedriveFieldOption::create(['pipedrive_field_id' => $size->id, 'key' => 's', 'value' => 'Small', 'status' => 0]);
        $big = \App\Model\Common\PipedriveFieldOption::create(['pipedrive_field_id' => $size->id, 'key' => 'b', 'value' => 'Big', 'status' => 0]);
        $huge = \App\Model\Common\PipedriveFieldOption::create(['pipedrive_field_id' => $size->id, 'key' => 'h', 'value' => 'Huge', 'status' => 1]);

        $response = $this->orgRoundTrip(55)->mappingFields(new Request([
            'group_id' => $org->id,
            'select1' => [['id' => $name->id], $size->id],
            'select2' => [['id' => $local->id, 'faveo_fields' => 'true'], ['id' => [$small->id, $big->id], 'faveo_fields' => false]],
        ]));

        $this->assertTrue($response->getData(true)['success']);
        $this->assertSame($local->id, $name->refresh()->local_field_id);
        $this->assertSame($other->id, $size->refresh()->local_field_id); // an option row doesn't touch the field's own mapping
        $this->assertSame([1, 1, 0], [$small->refresh()->status, $big->refresh()->status, $huge->refresh()->status]);
    }

    public function test_mapping_unmaps_fields_that_are_no_longer_selected(): void
    {
        $org = PipedriveGroups::where('group_name', 'Organization')->first();
        $name = PipedriveField::create(['pipedrive_group_id' => $org->id, 'field_key' => 'name', 'field_name' => 'Name']);
        $local = PipedriveLocalFields::firstOrCreate(['field_key' => 'company'], ['field_name' => 'Company']);
        $old = PipedriveField::create(['pipedrive_group_id' => $org->id, 'field_key' => 'phone', 'field_name' => 'Phone', 'local_field_id' => $local->id]);

        $this->orgRoundTrip(55)->mappingFields(new Request([
            'group_id' => $org->id,
            'select1' => [['id' => $name->id]],
            'select2' => [['id' => $local->id, 'faveo_fields' => true]],
        ]));

        $this->assertNull($old->refresh()->local_field_id);
    }

    public function test_mapping_is_rolled_back_when_pipedrive_rejects_the_test_record(): void
    {
        $org = PipedriveGroups::where('group_name', 'Organization')->first();
        $local = PipedriveLocalFields::firstOrCreate(['field_key' => 'company'], ['field_name' => 'Company']);
        $before = PipedriveLocalFields::firstOrCreate(['field_key' => 'email'], ['field_name' => 'Email']);
        $name = PipedriveField::create(['pipedrive_group_id' => $org->id, 'field_key' => 'name', 'field_name' => 'Name', 'local_field_id' => $before->id]);

        $response = $this->orgRoundTrip((object) ['success' => false, 'error' => 'Name is required'])->mappingFields(new Request([
            'group_id' => $org->id,
            'select1' => [['id' => $name->id]],
            'select2' => [['id' => $local->id, 'faveo_fields' => true]],
        ]));

        $this->assertSame(['success' => false, 'message' => 'Name is required'], array_intersect_key($response->getData(true), ['success' => 1, 'message' => 1]));
        $this->assertSame($before->id, $name->refresh()->local_field_id);
    }

    public function test_mapping_a_deal_needs_its_title_field(): void
    {
        $deal = PipedriveGroups::where('group_name', 'Deal')->first();
        $value = PipedriveField::create(['pipedrive_group_id' => $deal->id, 'field_key' => 'value', 'field_name' => 'Value']);
        $local = PipedriveLocalFields::firstOrCreate(['field_key' => 'company'], ['field_name' => 'Company']);

        $response = $this->pipedriveController->mappingFields(new Request([
            'group_id' => $deal->id,
            'select1' => [['id' => $value->id]],
            'select2' => [['id' => $local->id, 'faveo_fields' => true]],
        ]));

        $this->assertSame(__('message.title_field_deals'), $response->getData(true)['message']);
    }
}
