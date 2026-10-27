<?php

namespace App\Plugins\Zoho\Tests\Controllers;

use App\Plugins\Zoho\Controllers\ZohoSync;
use App\Plugins\Zoho\Models\FaveoLocalFields;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use App\Plugins\Zoho\Models\ZohoFields;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ZohoSyncTest extends DBTestCase
{
    use DatabaseTransactions;

    private ZohoSync $sync;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sync = new ZohoSync;
    }

    public function test_it_syncs_crm_fields_correctly(): void
    {
        $fields = [
            [
                'id' => '123456',
                'api_name' => 'First_Name',
                'field_label' => 'First Name',
                'data_type' => 'text',
                'system_mandatory' => true,
            ],
        ];

        $this->sync->sync('crm', 'Leads', $fields);

        $this->assertDatabaseHas('zoho_fields', [
            'platform' => 'crm',
            'module' => 'Leads',
            'zoho_field_uid' => '123456',
            'zoho_key' => 'First_Name',
            'display_name' => 'First Name',
            'field_type' => 'text',
            'is_mandatory' => true,
        ]);
    }

    public function test_it_syncs_campaigns_fields_correctly(): void
    {
        $fields = [
            [
                'FIELD_ID' => '789',
                'FIELD_NAME' => 'Contact Email',
                'DISPLAY_NAME' => 'Email Address',
                'UITYPE' => 'email',
                'IS_MANDATORY' => true,
            ],
        ];

        $this->sync->sync('campaigns', 'contacts', $fields);

        $this->assertDatabaseHas('zoho_fields', [
            'platform' => 'campaigns',
            'module' => 'contacts',
            'zoho_field_uid' => '789',
            'zoho_key' => 'Contact Email',
            'display_name' => 'Email Address',
            'field_type' => 'email',
            'is_mandatory' => true,
        ]);
    }

    public function test_it_updates_existing_fields_when_syncing(): void
    {
        $existingField = ZohoFields::create([
            'platform' => 'crm',
            'module' => 'Leads',
            'zoho_field_uid' => '123',
            'zoho_key' => 'Old_Key',
            'display_name' => 'Old Name',
        ]);

        $fields = [
            [
                'id' => '123',
                'api_name' => 'New_Key',
                'field_label' => 'New Name',
                'data_type' => 'text',
                'system_mandatory' => false,
            ],
        ];

        $this->sync->sync('crm', 'Leads', $fields);

        $this->assertDatabaseHas('zoho_fields', [
            'id' => $existingField->id,
            'zoho_key' => 'New_Key',
            'display_name' => 'New Name',
        ]);
    }

    public function test_it_throws_exception_for_unsupported_platform(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unsupported platform unsupported');

        $fields = [
            ['id' => '123', 'api_name' => 'test'],
        ];

        $this->sync->sync('unsupported', 'module', $fields);
    }

    public function test_it_normalizes_crm_field_types_correctly(): void
    {
        $fieldTypes = [
            'text' => 'text',
            'textarea' => 'textarea',
            'email' => 'email',
            'phone' => 'phone',
            'bigint' => 'number',
            'double' => 'decimal',
            'boolean' => 'checkbox',
            'date' => 'date',
            'datetime' => 'datetime',
            'picklist' => 'picklist',
            'lookup' => 'lookup',
            'ownerlookup' => 'owner',
            'profileimage' => 'image',
            'unknown_type' => 'text',
        ];

        foreach ($fieldTypes as $dataType => $expectedType) {
            $fields = [
                [
                    'id' => 'id_'.$dataType,
                    'api_name' => 'field_'.$dataType,
                    'field_label' => 'Field '.$dataType,
                    'data_type' => $dataType,
                    'system_mandatory' => false,
                ],
            ];

            $this->sync->sync('crm', 'Leads', $fields);

            $this->assertDatabaseHas('zoho_fields', [
                'zoho_field_uid' => 'id_'.$dataType,
                'field_type' => $expectedType,
            ]);
        }
    }

    public function test_it_handles_campaigns_field_types(): void
    {
        $fields = [
            [
                'FIELD_ID' => '1',
                'FIELD_NAME' => 'test_field',
                'DISPLAY_NAME' => 'Test Field',
                'UITYPE' => 'TEXT',
                'IS_MANDATORY' => false,
            ],
        ];

        $this->sync->sync('campaigns', 'contacts', $fields);

        $this->assertDatabaseHas('zoho_fields', [
            'zoho_field_uid' => '1',
            'field_type' => 'text',
        ]);
    }

    public function test_it_stores_raw_metadata_for_fields(): void
    {
        $fields = [
            [
                'id' => '123',
                'api_name' => 'Custom_Field',
                'field_label' => 'Custom',
                'data_type' => 'text',
                'system_mandatory' => false,
                'custom_property' => 'custom_value',
            ],
        ];

        $this->sync->sync('crm', 'Leads', $fields);

        $field = ZohoFields::where('zoho_field_uid', '123')->firstOrFail();

        $this->assertIsArray($field->raw_metadata); // @phpstan-ignore method.alreadyNarrowedType
        $this->assertEquals('custom_value', $field->raw_metadata['custom_property']);
    }

    public function test_it_handles_mandatory_fields_for_crm(): void
    {
        $fields = [
            [
                'id' => '1',
                'api_name' => 'mandatory_field',
                'field_label' => 'Mandatory',
                'data_type' => 'text',
                'system_mandatory' => true,
            ],
            [
                'id' => '2',
                'api_name' => 'optional_field',
                'field_label' => 'Optional',
                'data_type' => 'text',
                'system_mandatory' => false,
            ],
        ];

        $this->sync->sync('crm', 'Leads', $fields);

        $this->assertDatabaseHas('zoho_fields', [
            'zoho_field_uid' => '1',
            'is_mandatory' => true,
        ]);

        $this->assertDatabaseHas('zoho_fields', [
            'zoho_field_uid' => '2',
            'is_mandatory' => false,
        ]);
    }

    public function test_it_handles_mandatory_fields_for_campaigns(): void
    {
        $fields = [
            [
                'FIELD_ID' => '1',
                'FIELD_NAME' => 'mandatory',
                'DISPLAY_NAME' => 'Mandatory',
                'UITYPE' => 'text',
                'IS_MANDATORY' => true,
            ],
        ];

        $this->sync->sync('campaigns', 'contacts', $fields);

        $this->assertDatabaseHas('zoho_fields', [
            'zoho_field_uid' => '1',
            'is_mandatory' => true,
        ]);
    }

    public function test_it_syncs_multiple_fields_in_single_call(): void
    {
        $fields = [
            [
                'id' => '1',
                'api_name' => 'field1',
                'field_label' => 'Field 1',
                'data_type' => 'text',
                'system_mandatory' => false,
            ],
            [
                'id' => '2',
                'api_name' => 'field2',
                'field_label' => 'Field 2',
                'data_type' => 'email',
                'system_mandatory' => true,
            ],
            [
                'id' => '3',
                'api_name' => 'field3',
                'field_label' => 'Field 3',
                'data_type' => 'phone',
                'system_mandatory' => false,
            ],
        ];

        $this->sync->sync('crm', 'Contacts', $fields);

        $this->assertEquals(3, ZohoFields::count());
    }

    public function test_reconnecting_another_zoho_account_keeps_fields_and_mappings(): void
    {
        $field = ZohoFields::create([
            'platform' => 'crm', 'module' => 'Leads', 'zoho_field_uid' => 'old-account-1',
            'zoho_key' => 'Email', 'display_name' => 'Email', 'field_type' => 'email', 'raw_metadata' => [],
        ]);
        $mapping = ZohoFieldMappings::create([
            'zoho_field_id' => $field->id,
            'faveo_local_field_id' => FaveoLocalFields::updateOrCreate(['field_key' => 'email'], ['display_name' => 'Email', 'field_type' => 'email'])->id,
        ]);

        $removed = $this->sync->sync('crm', 'Leads', [
            ['id' => 'new-account-1', 'api_name' => 'Email', 'field_label' => 'Email', 'data_type' => 'email'],
        ]);

        $this->assertSame([], $removed);
        $this->assertSame(1, ZohoFields::wherePlatform('crm')->whereModule('Leads')->count());
        $this->assertSame('new-account-1', $field->refresh()->zoho_field_uid);
        $this->assertNotNull($mapping->fresh());
    }

    public function test_it_removes_mappings_that_no_longer_fit_and_fields_zoho_dropped(): void
    {
        $local = FaveoLocalFields::updateOrCreate(['field_key' => 'first_name'], ['display_name' => 'First Name', 'field_type' => 'text']);

        $phone = ZohoFields::create([
            'platform' => 'crm', 'module' => 'Leads', 'zoho_field_uid' => '1',
            'zoho_key' => 'Phone', 'display_name' => 'Phone', 'field_type' => 'text', 'raw_metadata' => [],
        ]);
        ZohoFieldMappings::create(['zoho_field_id' => $phone->id, 'faveo_local_field_id' => $local->id]);

        $source = ZohoFields::create([
            'platform' => 'crm', 'module' => 'Leads', 'zoho_field_uid' => '2',
            'zoho_key' => 'Lead_Source', 'display_name' => 'Lead Source', 'field_type' => 'picklist', 'raw_metadata' => [],
        ]);
        ZohoFieldMappings::create(['zoho_field_id' => $source->id, 'selected_option' => json_encode(['value' => 'Advertisement'])]);

        $gone = ZohoFields::create([
            'platform' => 'crm', 'module' => 'Leads', 'zoho_field_uid' => '3',
            'zoho_key' => 'Old_Field', 'display_name' => 'Old Field', 'field_type' => 'text', 'raw_metadata' => [],
        ]);
        ZohoFieldMappings::create(['zoho_field_id' => $gone->id, 'faveo_local_field_id' => $local->id]);

        $removed = $this->sync->sync('crm', 'Leads', [
            // Phone became a picklist with its own choices, so a text field can't feed it any more.
            ['id' => '1', 'api_name' => 'Phone', 'field_label' => 'Phone', 'data_type' => 'picklist',
                'pick_list_values' => [['actual_value' => 'Home', 'display_value' => 'Home']]],
            // Lead Source no longer offers "Advertisement".
            ['id' => '2', 'api_name' => 'Lead_Source', 'field_label' => 'Lead Source', 'data_type' => 'picklist',
                'pick_list_values' => [['actual_value' => 'Web', 'display_value' => 'Web']]],
        ]);

        $this->assertEqualsCanonicalizing(['Old Field', 'Phone', 'Lead Source'], $removed);
        $this->assertSame(0, ZohoFieldMappings::whereIn('zoho_field_id', [$phone->id, $source->id, $gone->id])->count());
        $this->assertNull($gone->fresh());
    }
}
