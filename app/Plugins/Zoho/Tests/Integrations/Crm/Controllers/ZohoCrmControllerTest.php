<?php

namespace App\Plugins\Zoho\Tests\Integrations\Crm\Controllers;

use App\Plugins\Zoho\Integrations\Crm\Controllers\ZohoCrmController;
use App\Plugins\Zoho\Models\FaveoLocalFields;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use App\Plugins\Zoho\Models\ZohoFields;
use App\Plugins\Zoho\Models\ZohoIntegration;
use App\Plugins\Zoho\Models\ZohoOAuthClient;
use App\Plugins\Zoho\Models\ZohoOAuthToken;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\DBTestCase;

class ZohoCrmControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    private ZohoCrmController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $integration = ZohoIntegration::firstOrCreate(['platform' => 'crm']);
        ZohoOAuthClient::updateOrCreate(
            ['integration_id' => $integration->id],
            ['region' => 'us']
        );
        ZohoOAuthToken::updateOrCreate(
            ['integration_id' => $integration->id],
            [
                'access_token' => 'test_token',
                'expires_at' => now()->addHour(),
            ]
        );

        $this->controller = new ZohoCrmController();
    }

    public function test_it_syncs_crm_fields_successfully()
    {
        Http::fake([
            '*' => Http::response([
                'fields' => [
                    [
                        'id' => '1',
                        'api_name' => 'First_Name',
                        'field_label' => 'First Name',
                        'data_type' => 'text',
                        'system_mandatory' => false,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->controller->syncFields();

        $this->assertTrue($response->getData()->success);
        $this->assertDatabaseHas('zoho_fields', [
            'platform' => 'crm',
            'module' => 'Contacts',
        ]);
    }

    public function test_it_handles_sync_fields_error()
    {
        Http::fake([
            '*' => Http::response([
                'code' => 'INVALID_MODULE',
                'details' => [],
                'message' => 'the module name given seems to be invalid',
                'status' => 'error',
            ], 500),
        ]);

        $response = $this->controller->syncFields();

        $this->assertFalse($response->getData()->success);
    }

    public function test_it_gets_crm_mapped_fields_for_module()
    {
        $zohoField = ZohoFields::create([
            'platform' => 'crm',
            'module' => 'contacts',
            'display_name' => 'Email',
        ]);

        $localField = FaveoLocalFields::firstOrCreate([
            'field_key' => 'email',
        ]);

        ZohoFieldMappings::firstOrCreate([
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => $localField->id,
        ]);

        $response = $this->controller->getCrmMappedFields('contacts');

        $this->assertTrue($response->getData()->success);
    }

    public function test_it_gets_crm_contacts_fields()
    {
        ZohoFields::create([
            'platform' => 'crm',
            'module' => 'Contacts',
            'display_name' => 'Name',
        ]);

        $response = $this->controller->getCrmContactsFields();

        $this->assertTrue($response->getData()->success);
    }

    public function test_it_gets_crm_accounts_fields()
    {
        ZohoFields::create([
            'platform' => 'crm',
            'module' => 'Accounts',
            'display_name' => 'Account Name',
        ]);

        $response = $this->controller->getCrmAccountsFields();

        $this->assertTrue($response->getData()->success);
    }

    public function test_it_validates_email_when_updating_to_zoho_crm()
    {
        $request = new Request(['email' => 'invalid-email']);

        $response = $this->controller->updateToZohoCrm($request);

        $this->assertFalse($response->getData()->success);
    }

    public function test_it_updates_user_to_zoho_crm_successfully()
    {
        $user = User::create(['email' => 'test@example.com']);

        ZohoFields::create([
            'platform' => 'crm',
            'module' => 'Contacts',
            'zoho_key' => 'Email',
        ]);

        Http::fake([
            '*' => Http::response(['data' => [['code' => 'SUCCESS']]], 200),
        ]);

        $request = new Request(['email' => 'test@example.com']);

        $response = $this->controller->updateToZohoCrm($request);

        $this->assertTrue($response->getData()->success);
    }

    public function test_it_adds_user_data_to_both_contacts_and_accounts()
    {
        $user = User::create(['email' => 'test@example.com']);

        $contactField = ZohoFields::create([
            'platform' => 'crm',
            'module' => 'Contacts',
            'zoho_key' => 'Email',
        ]);

        $accountField = ZohoFields::create([
            'platform' => 'crm',
            'module' => 'Accounts',
            'zoho_key' => 'Account_Name',
        ]);

        // Create local field mappings
        $emailField = FaveoLocalFields::firstOrCreate([
            'field_key' => 'email',
        ]);

        ZohoFieldMappings::firstOrCreate([
            'zoho_field_id' => $contactField->id,
            'faveo_local_field_id' => $emailField->id,
        ]);

        ZohoFieldMappings::firstOrCreate([
            'zoho_field_id' => $accountField->id,
            'faveo_local_field_id' => $emailField->id,
        ]);

        Http::fake([
            '*' => Http::response(['data' => [['status' => 'success']]], 200),
        ]);

        $this->controller->addUserDataToCrm('test@example.com');

        Http::assertSentCount(2);
    }

    public function test_it_skips_insertion_when_no_record_data()
    {
        $user = User::create(['email' => 'test@example.com']);

        Http::fake();

        $this->controller->addUserDataToCrm('test@example.com');

        Http::assertNothingSent();
    }

    public function test_it_throws_exception_when_user_not_found()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->controller->addUserDataToCrm('nonexistent@example.com');
    }

    public function test_it_uses_mapped_fields_when_inserting_module_data()
    {
        $user = User::create([
            'email' => 'user@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $zohoField = ZohoFields::create([
            'platform' => 'crm',
            'module' => 'Contacts',
            'zoho_key' => 'First_Name',
        ]);

        $localField = FaveoLocalFields::firstOrCreate([
            'field_key' => 'first_name',
        ]);

        ZohoFieldMappings::firstOrCreate([
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => $localField->id,
        ]);

        Http::fake([
            '*' => Http::response(['data' => [['status' => 'success']]], 200),
        ]);

        $this->controller->addUserDataToCrm('user@example.com');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'Contacts'));
    }
}
