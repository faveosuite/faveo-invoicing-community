<?php

namespace App\Plugins\Zoho\Tests\Helpers;

use App\Plugins\Zoho\Helpers\ZohoConnectHelper;
use App\Plugins\Zoho\Models\FaveoLocalFields;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use App\Plugins\Zoho\Models\ZohoFields;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use Tests\DBTestCase;

class ZohoConnectHelperTest extends DBTestCase
{
    use DatabaseTransactions;

    public function test_get_modules_fields_returns_correct_structure(): void
    {
        ZohoFields::insert([
            [
                'platform' => 'crm',
                'module' => 'Contacts',
                'zoho_field_uid' => 'Contacts.First_Name',
                'display_name' => 'First Name',
                'field_type' => 'text',
            ],
            [
                'platform' => 'crm',
                'module' => 'Contacts',
                'zoho_field_uid' => 'Contacts.Email',
                'display_name' => 'Email',
                'field_type' => 'email',
            ],
        ]);

        $result = ZohoConnectHelper::getModulesFields('crm', 'Contacts');

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('field_name', $result[0]);
        $this->assertArrayHasKey('type', $result[0]);
        $this->assertEquals('Email', $result[0]['field_name']);
        $this->assertEquals('email', $result[0]['type']);
    }

    public function test_get_modules_fields_filters_by_platform_and_module(): void
    {
        ZohoFields::insert([
            [
                'platform' => 'crm',
                'module' => 'Contacts',
                'display_name' => 'CRM Contact Field',
                'zoho_field_uid' => 'Contacts.CRM Contact Field',
                'field_type' => 'text',
            ],
            [
                'platform' => 'campaigns',
                'module' => 'Contacts',
                'display_name' => 'Campaign Field',
                'zoho_field_uid' => 'Contacts.Campaign Field',
                'field_type' => 'email',
            ],
            [
                'platform' => 'crm',
                'module' => 'Accounts',
                'display_name' => 'CRM Account Field',
                'zoho_field_uid' => 'Accounts.CRM Account Field',
                'field_type' => 'email',
            ],
        ]);

        $result = ZohoConnectHelper::getModulesFields('crm', 'Contacts');

        $this->assertCount(1, $result);
        $this->assertEquals('CRM Contact Field', $result[0]['field_name']);
    }

    public function test_get_existing_mappings_returns_zoho_option_mapping(): void
    {
        $zohoField = ZohoFields::create([
            'platform' => 'crm',
            'module' => 'Contacts',
        ]);

        ZohoFieldMappings::create([
            'zoho_field_id' => $zohoField->id,
            'selected_option' => json_encode(['value' => 'Active']),
            'faveo_local_field_id' => null,
        ]);

        $result = ZohoConnectHelper::getExistingMappings('crm', 'Contacts');

        $this->assertCount(1, $result);
        $this->assertEquals($zohoField->id, $result[0]['zoho_field_id']);
        $this->assertEquals('zoho', $result[0]['selected']['type']);
        $this->assertEquals('Active', json_decode((string) $result[0]['selected']['value'])->value);
    }

    public function test_get_existing_mappings_returns_local_field_mapping(): void
    {
        $zohoField = ZohoFields::create([
            'platform' => 'crm',
            'module' => 'Leads',
        ]);

        $localField = FaveoLocalFields::create();

        ZohoFieldMappings::create([
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => $localField->id,
            'selected_option' => null,
        ]);

        $result = ZohoConnectHelper::getExistingMappings('crm', 'Leads');

        $this->assertCount(1, $result);
        $this->assertEquals($zohoField->id, $result[0]['zoho_field_id']);
        $this->assertEquals('local', $result[0]['selected']['type']);
        $this->assertEquals($localField->id, $result[0]['selected']['value']);
    }

    public function test_get_existing_mappings_filters_null_mappings(): void
    {
        $zohoField = ZohoFields::create([
            'platform' => 'crm',
            'module' => 'Leads',
        ]);

        ZohoFieldMappings::create([
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => null,
            'selected_option' => null,
        ]);

        $result = ZohoConnectHelper::getExistingMappings('crm', 'Leads');

        $this->assertCount(0, $result);
    }

    public function test_merge_fields_combines_zoho_and_local_fields(): void
    {
        $zohoField = ZohoFields::create([
            'id' => 1,
            'zoho_key' => 'First_Name',
            'display_name' => 'First Name',
        ]);

        $localField = FaveoLocalFields::whereFieldKey('first_name')->first();

        $zohoFields = collect([$zohoField]);
        $localFields = collect([$localField]);

        $result = ZohoConnectHelper::mergeFields($zohoFields, $localFields);

        $this->assertCount(1, $result);
        $this->assertEquals('First_Name', $result[0]['zoho']['api_name']);
        $this->assertCount(1, $result[0]['options']);
        $this->assertEquals('local_field', $result[0]['options'][0]['type']);
        $this->assertEquals('First Name', $result[0]['options'][0]['label']);
    }

    public function test_merge_fields_includes_zoho_picklist_options(): void
    {
        $zohoField = ZohoFields::create([
            'id' => 1,
            'zoho_key' => 'Status',
            'display_name' => 'Status',
            'raw_metadata' => [
                'options' => ['Active', 'Inactive'],
            ],
        ]);

        $zohoFields = collect([$zohoField]);
        $localFields = collect([]);

        $result = ZohoConnectHelper::mergeFields($zohoFields, $localFields);

        $this->assertArrayHasKey('options', $result[0]);
    }

    public function test_merge_fields_includes_selected_mapping(): void
    {
        $zohoField = ZohoFields::create([
            'id' => 1,
        ]);

        $localField = FaveoLocalFields::find(5);

        ZohoFieldMappings::create([
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => $localField->id,
        ]);

        $zohoFields = collect([$zohoField]);
        $localFields = collect([]);

        $result = ZohoConnectHelper::mergeFields($zohoFields, $localFields);

        $this->assertEquals(5, $result[0]['selected']['local_field_id']);
    }

    public function test_update_mapping_creates_new_local_field_mapping(): void
    {
        $zohoField = ZohoFields::create(['id' => 1]);

        ZohoConnectHelper::updateMapping(
            $zohoField->id,
            ['type' => 'local', 'value' => 5]
        );

        $this->assertDatabaseHas('zoho_field_mappings', [
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => 5,
        ]);
    }

    public function test_update_mapping_creates_new_zoho_option_mapping(): void
    {
        $zohoField = ZohoFields::create(['id' => 1]);

        ZohoConnectHelper::updateMapping(
            $zohoField->id,
            ['type' => 'zoho', 'value' => 'Active']
        );

        $this->assertDatabaseHas('zoho_field_mappings', [
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => null,
        ]);

        $mapping = ZohoFieldMappings::where('zoho_field_id', $zohoField->id)->first();
        $selectedOption = json_decode((string) $mapping->selected_option, associative: true);
        $this->assertEquals('Active', $selectedOption['value']);
    }

    public function test_update_mapping_updates_existing_mapping(): void
    {
        $zohoField = ZohoFields::create(['id' => 1]);

        $mapping = ZohoFieldMappings::create([
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => 5,
        ]);

        ZohoConnectHelper::updateMapping(
            $zohoField->id,
            ['type' => 'local', 'value' => 10]
        );

        $this->assertDatabaseHas('zoho_field_mappings', [
            'id' => $mapping->id,
            'zoho_field_id' => 1,
            'faveo_local_field_id' => 10,
        ]);
    }

    public function test_update_mapping_throws_exception_for_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ZohoConnectHelper::updateMapping(
            1,
            ['type' => 'invalid', 'value' => 'test'],
            []
        );
    }

    public function test_update_mapping_sets_default_meta_values(): void
    {
        ZohoFields::create(['id' => 1]);

        ZohoConnectHelper::updateMapping(
            1,
            ['type' => 'local', 'value' => 5],
            []
        );

        $this->assertDatabaseHas('zoho_field_mappings', [
            'zoho_field_id' => 1,
            'default_value' => null,
            'use_default_if_empty' => false,
            'is_active' => true,
        ]);
    }

    public function test_update_mapping_can_set_use_default_if_empty(): void
    {
        ZohoFields::create(['id' => 1]);

        ZohoConnectHelper::updateMapping(
            1,
            ['type' => 'local', 'value' => 5],
            [
                'default_value' => json_encode(['value' => 'fallback']),
                'use_default_if_empty' => true,
            ]
        );

        $mapping = ZohoFieldMappings::where('zoho_field_id', 1)
            ->first();

        $this->assertEquals(1, $mapping->use_default_if_empty);
        $this->assertEquals(
            ['value' => 'fallback'],
            json_decode((string) $mapping->default_value, associative: true)
        );
    }

    public function test_update_mapping_clears_unused_fields(): void
    {
        ZohoFields::create(['id' => 1]);

        $mapping = ZohoFieldMappings::create([
            'zoho_field_id' => 1,
            'faveo_local_field_id' => 5,
            'selected_option' => json_encode(['value' => 'old']),
        ]);

        ZohoConnectHelper::updateMapping(
            1,
            ['type' => 'local', 'value' => 10],
            []
        );

        $updated = ZohoFieldMappings::find($mapping->id);
        $this->assertNull($updated->selected_option);
    }

    public function test_get_existing_mappings_includes_relationships(): void
    {
        $zohoField = ZohoFields::create([
            'platform' => 'crm',
            'module' => 'Leads',
        ]);

        $localField = FaveoLocalFields::whereFieldKey('email')->first();

        ZohoFieldMappings::create([
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => $localField->id,
        ]);

        $result = ZohoConnectHelper::getExistingMappings('crm', 'Leads');

        $this->assertNotNull($result);
        $this->assertCount(1, $result);
    }
}
