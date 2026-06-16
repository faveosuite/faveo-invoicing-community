<?php

namespace App\Plugins\Zoho\Tests\Helpers;

use App\Plugins\Zoho\Controllers\Api\ZohoRegion;
use App\Plugins\Zoho\Models\FaveoLocalFields;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use App\Plugins\Zoho\Models\ZohoFields;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class HelpersTest extends DBTestCase
{
    use DatabaseTransactions;

    public function test_zoho_mapped_fields_returns_correct_crm_field_mapping()
    {
        $zohoField = ZohoFields::create([
            'id' => 1,
            'platform' => 'crm',
            'zoho_key' => 'First_Name',
            'display_name' => 'First Name',
        ]);

        $localField = FaveoLocalFields::whereFieldKey('first_name')->first();

        $mapping = ZohoFieldMappings::create([
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => $localField->id,
            'selected_option' => null,
        ]);

        $zohoFields = collect([$zohoField]);
        $mappings = collect([$mapping]);
        $source = ['first_name' => 'John'];

        $result = zohoMappedFields($zohoFields, $mappings, $source);

        $this->assertEquals(['First_Name' => 'John'], $result);
    }

    public function test_zoho_mapped_fields_returns_correct_campaigns_field_mapping()
    {
        $zohoField = ZohoFields::create([
            'id' => 1,
            'platform' => 'campaigns',
            'zoho_key' => 'contact_email',
            'display_name' => 'Contact Email',
        ]);

        $localField = FaveoLocalFields::whereFieldKey('email')->first();

        $mapping = ZohoFieldMappings::create([
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => $localField->id,
        ]);

        $zohoFields = collect([$zohoField]);
        $mappings = collect([$mapping]);
        $source = ['email' => 'test@example.com'];

        $result = zohoMappedFields($zohoFields, $mappings, $source);

        $this->assertEquals(['contact_email' => 'test@example.com'], $result);
    }

    public function test_zoho_mapped_fields_uses_zoho_static_value_when_selected()
    {
        $zohoField = ZohoFields::create([
            'id' => 1,
            'platform' => 'crm',
            'zoho_key' => 'Status',
            'display_name' => 'Status',
        ]);

        $mapping = ZohoFieldMappings::create([
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => null,
            'selected_option' => json_encode(['value' => 'Active']),
        ]);

        $zohoFields = collect([$zohoField]);
        $mappings = collect([$mapping]);
        $source = [];

        $result = zohoMappedFields($zohoFields, $mappings, $source);

        $this->assertEquals(['Status' => 'Active'], $result);
    }

    public function test_zoho_mapped_fields_uses_default_value_when_source_is_null()
    {
        $zohoField = ZohoFields::create([
            'id' => 1,
            'platform' => 'crm',
            'zoho_key' => 'Country',
        ]);

        $localField = FaveoLocalFields::whereFieldKey('country')->first();

        $mapping = ZohoFieldMappings::create([
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => $localField->id,
            'default_value' => json_encode(['value' => 'USA']),
        ]);

        $zohoFields = collect([$zohoField]);
        $mappings = collect([$mapping]);
        $source = [];

        $result = zohoMappedFields($zohoFields, $mappings, $source);

        $this->assertEquals(['Country' => json_encode(['value' => 'USA'])], $result);
    }

    public function test_zoho_mapped_fields_skips_null_or_empty_values()
    {
        $zohoField = ZohoFields::create([
            'id' => 1,
            'platform' => 'crm',
            'zoho_key' => 'Optional_Field',
        ]);

        $localField = FaveoLocalFields::create([
            'field_key' => 'optional',
        ]);

        $mapping = ZohoFieldMappings::create([
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => $localField->id,
            'default_value' => null,
        ]);

        $zohoFields = collect([$zohoField]);
        $mappings = collect([$mapping]);
        $source = ['optional' => ''];

        $result = zohoMappedFields($zohoFields, $mappings, $source);

        $this->assertEquals([], $result);
    }

    public function test_zoho_mapped_fields_skips_missing_zoho_field()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $localField = FaveoLocalFields::create([
            'field_key' => 'test',
        ]);

        $mapping = ZohoFieldMappings::create([
            'zoho_field_id' => 999,
            'faveo_local_field_id' => $localField->id,
        ]);

        $zohoFields = collect([]);
        $mappings = collect([$mapping]);
        $source = ['test' => 'value'];

        $result = zohoMappedFields($zohoFields, $mappings, $source);

        $this->assertEquals([], $result);
    }

    public function test_resolve_options_returns_picklist_options_for_zoho_field()
    {
        $zohoField = ZohoFields::create([
            'field_type' => 'picklist',
            'raw_metadata' => [
                'pick_list_values' => [
                    ['actual_value' => 'Option1', 'display_value' => 'Option 1'],
                    ['actual_value' => '-None-', 'display_value' => 'None'],
                    ['actual_value' => 'Option2', 'display_value' => 'Option 2'],
                ],
            ],
        ]);

        $localFields = collect([]);

        $result = resolveOptions($zohoField, $localFields);

        $this->assertCount(2, $result);
        $this->assertEquals('zoho', $result[0]['type']);
        $this->assertEquals('Option1', $result[0]['value']);
        $this->assertEquals('Option 1', $result[0]['label']);
    }

    public function test_resolve_options_excludes_none_value()
    {
        $zohoField = ZohoFields::create([
            'field_type' => 'picklist',
            'raw_metadata' => [
                'pick_list_values' => [
                    ['actual_value' => '-None-', 'display_value' => 'None'],
                    ['actual_value' => 'Valid', 'display_value' => 'Valid Option'],
                ],
            ],
        ]);

        $result = resolveOptions($zohoField, collect([]));

        $this->assertCount(1, $result);
        $this->assertEquals('Valid', $result[0]['value']);
    }

    public function test_resolve_options_returns_local_fields_for_non_picklist()
    {
        $zohoField = ZohoFields::create([
            'field_type' => 'text',
        ]);

        $localFields = FaveoLocalFields::whereIn('field_key', [
            'first_name',
            'last_name',
        ])->get();

        $result = resolveOptions($zohoField, $localFields);

        $this->assertCount(2, $result);
        $this->assertEquals('local', $result[0]['type']);
        $this->assertEquals('First Name', $result[0]['label']);
    }

    public function test_resolve_selected_returns_zoho_type_for_picklist()
    {
        $zohoField = ZohoFields::create([
            'field_type' => 'picklist',
        ]);

        $mapping = ZohoFieldMappings::create([
            'zoho_field_id' => $zohoField->id,
            'selected_option' => json_encode(['value' => 'Selected']),
        ]);

        $result = resolveSelected($mapping);

        $this->assertEquals('zoho', $result['type']);
        $this->assertEquals(json_encode(['value' => 'Selected']), $result['value']);
    }

    public function test_resolve_selected_returns_local_type_for_non_picklist()
    {
        $zohoField = ZohoFields::create([
            'field_type' => 'text',
        ]);

        $mapping = ZohoFieldMappings::create([
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => 5,
        ]);

        $result = resolveSelected($mapping);

        $this->assertEquals('local', $result['type']);
        $this->assertEquals(5, $result['value']);
    }

    public function test_resolve_selected_returns_null_when_no_mapping()
    {
        $zohoField = ZohoFields::create();

        $result = resolveSelected(null);

        $this->assertNull($result);
    }

    public function test_get_zoho_region_returns_correct_region_from_string()
    {
        $this->assertEquals(ZohoRegion::India, getZohoRegion('in'));
        $this->assertEquals(ZohoRegion::UnitedStates, getZohoRegion('us'));
        $this->assertEquals(ZohoRegion::Europe, getZohoRegion('eu'));
    }

    public function test_get_zoho_region_falls_back_to_default_region()
    {
        config(['zoho.default_region' => 'us']);

        $result = getZohoRegion('invalid');

        $this->assertEquals(ZohoRegion::UnitedStates, $result);
    }

    public function test_get_zoho_region_falls_back_to_india_when_no_default()
    {
        config(['zoho.default_region' => null]);

        $result = getZohoRegion('invalid');

        $this->assertEquals(ZohoRegion::India, $result);
    }

    public function test_zoho_mapped_fields_handles_object_as_source()
    {
        $zohoField = ZohoFields::create([
            'id' => 1,
            'platform' => 'crm',
            'zoho_key' => 'Email',
        ]);

        $localField = FaveoLocalFields::whereFieldKey('email')->first();

        $mapping = ZohoFieldMappings::create([
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => $localField->id,
        ]);

        $zohoFields = collect([$zohoField]);
        $mappings = collect([$mapping]);
        $source = (object) ['email' => 'object@example.com'];

        $result = zohoMappedFields($zohoFields, $mappings, $source);

        $this->assertEquals(['Email' => 'object@example.com'], $result);
    }
}
