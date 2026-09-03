<?php

namespace App\Plugins\Zoho\Tests\Controllers;

use App\Plugins\Zoho\Controllers\ZohoBaseController;
use App\Plugins\Zoho\Models\FaveoLocalFields;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use App\Plugins\Zoho\Models\ZohoFields;
use App\Plugins\Zoho\Models\ZohoIntegration;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\DBTestCase;

class ZohoBaseControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    private ZohoBaseController $controller;

    private ZohoIntegration $integration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integration = ZohoIntegration::firstOrCreate(['platform' => 'crm']);
        $this->controller = new ZohoBaseController;
    }

    public function test_it_rejects_an_incompatible_mapping_without_persisting_anything(): void
    {
        $zohoField = ZohoFields::create(['platform' => 'crm', 'module' => 'Contacts', 'field_type' => 'date']);
        $localField = FaveoLocalFields::whereFieldKey('first_name')->firstOrFail();

        $request = new Request([
            'integration_id' => $this->integration->id,
            'module' => 'Contacts',
            'mappings' => [
                ['zoho_field_id' => $zohoField->id, 'selected' => ['type' => 'local', 'value' => $localField->id]],
            ],
        ]);

        $response = $this->controller->updateMapping($request);

        $this->assertFalse($response->getData()->success);
        $this->assertDatabaseMissing('zoho_field_mappings', ['zoho_field_id' => $zohoField->id]);
    }

    public function test_it_saves_a_compatible_mapping_successfully(): void
    {
        $zohoField = ZohoFields::create(['platform' => 'crm', 'module' => 'Contacts', 'field_type' => 'text']);
        $localField = FaveoLocalFields::whereFieldKey('first_name')->firstOrFail();

        $request = new Request([
            'integration_id' => $this->integration->id,
            'module' => 'Contacts',
            'mappings' => [
                ['zoho_field_id' => $zohoField->id, 'selected' => ['type' => 'local', 'value' => $localField->id]],
            ],
        ]);

        $response = $this->controller->updateMapping($request);

        $this->assertTrue($response->getData()->success);
        $this->assertDatabaseHas('zoho_field_mappings', [
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => $localField->id,
        ]);
    }

    public function test_it_still_saves_a_zoho_static_option_mapping_for_a_blocked_type(): void
    {
        // 'zoho' selections never carry the local-mapping risk, so a
        // picklist mapped to one of its own options must keep working.
        $zohoField = ZohoFields::create([
            'platform' => 'crm', 'module' => 'Contacts', 'field_type' => 'picklist',
            'raw_metadata' => [
                'pick_list_values' => [
                    ['actual_value' => 'Advertisement', 'display_value' => 'Advertisement'],
                ],
            ],
        ]);

        $request = new Request([
            'integration_id' => $this->integration->id,
            'module' => 'Contacts',
            'mappings' => [
                ['zoho_field_id' => $zohoField->id, 'selected' => ['type' => 'zoho', 'value' => 'Advertisement']],
            ],
        ]);

        $response = $this->controller->updateMapping($request);

        $this->assertTrue($response->getData()->success);
        $this->assertDatabaseHas('zoho_field_mappings', ['zoho_field_id' => $zohoField->id]);
    }

    public function test_it_removes_a_previously_saved_mapping_that_is_no_longer_submitted(): void
    {
        // Sanity check: adding the compatibility guard shouldn't disturb the
        // existing stale-mapping cleanup — a row simply left out of the next
        // save is still deleted, same as before.
        $removedField = ZohoFields::create([
            'platform' => 'crm', 'module' => 'Contacts', 'field_type' => 'text', 'zoho_field_uid' => 'removed',
        ]);
        $keptField = ZohoFields::create([
            'platform' => 'crm', 'module' => 'Contacts', 'field_type' => 'text', 'zoho_field_uid' => 'kept',
        ]);
        $localField = FaveoLocalFields::whereFieldKey('first_name')->firstOrFail();

        ZohoFieldMappings::create(['zoho_field_id' => $removedField->id, 'faveo_local_field_id' => $localField->id]);

        $request = new Request([
            'integration_id' => $this->integration->id,
            'module' => 'Contacts',
            'mappings' => [
                ['zoho_field_id' => $keptField->id, 'selected' => ['type' => 'local', 'value' => $localField->id]],
            ],
        ]);

        $response = $this->controller->updateMapping($request);

        $this->assertTrue($response->getData()->success);
        $this->assertDatabaseMissing('zoho_field_mappings', ['zoho_field_id' => $removedField->id]);
        $this->assertDatabaseHas('zoho_field_mappings', ['zoho_field_id' => $keptField->id]);
    }
}
