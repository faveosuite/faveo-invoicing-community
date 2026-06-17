<?php

namespace App\Plugins\Zoho\Tests\Integrations\Crm\Controllers;

use App\Plugins\Zoho\Integrations\Crm\Controllers\Crm;
use App\Plugins\Zoho\Models\ZohoIntegration;
use App\Plugins\Zoho\Models\ZohoOAuthClient;
use App\Plugins\Zoho\Models\ZohoOAuthToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Tests\DBTestCase;

class CrmTest extends DBTestCase
{
    use DatabaseTransactions;

    private Crm $crm;

    protected function setUp(): void
    {
        parent::setUp();

        $integration = ZohoIntegration::firstOrCreate(['platform' => 'crm']);
        ZohoOAuthClient::create([
            'integration_id' => $integration->id,
            'region' => 'us',
        ]);
        ZohoOAuthToken::create([
            'integration_id' => $integration->id,
            'access_token' => 'test_token',
            'expires_at' => now()->addHour(),
        ]);

        $this->crm = new Crm();
    }

    public function test_it_retrieves_crm_module_fields(): void
    {
        Http::fake([
            '*' => Http::response([
                'fields' => [
                    [
                        'id' => '123',
                        'api_name' => 'First_Name',
                        'field_label' => 'First Name',
                        'data_type' => 'text',
                    ],
                ],
            ], 200),
        ]);

        $fields = $this->crm->fields('Accounts');

        $this->assertNotEmpty($fields);
        $this->assertInstanceOf(Collection::class, $fields);
    }

    public function test_it_retrieves_crm_records(): void
    {
        Http::fake([
            '*' => Http::response([
                'data' => [
                    ['id' => '1', 'First_Name' => 'John'],
                    ['id' => '2', 'First_Name' => 'Jane'],
                ],
            ], 200),
        ]);

        $records = $this->crm->records('Accounts');

        $this->assertNotEmpty($records);
        $this->assertInstanceOf(Collection::class, $records);
    }

    public function test_it_creates_crm_record(): void
    {
        Http::fake([
            '*' => Http::response([
                'data' => [
                    ['code' => 'SUCCESS', 'status' => 'success'],
                ],
            ], 200),
        ]);

        $this->crm->create('Accounts', [
            'First_Name' => 'Test',
            'Email' => 'test@example.com',
        ]);

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'Accounts'));
    }

    public function test_it_updates_crm_record(): void
    {
        Http::fake([
            '*' => Http::response([
                'data' => [
                    ['code' => 'SUCCESS', 'status' => 'success'],
                ],
            ], 200),
        ]);

        $this->crm->update('Contacts', '123456', [
            'Phone' => '1234567890',
        ]);

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'Contacts') &&
               str_contains((string) $request->url(), '123456'));
    }

    public function test_it_deletes_crm_record(): void
    {
        Http::fake([
            '*' => Http::response([
                'data' => [
                    ['code' => 'SUCCESS', 'status' => 'success'],
                ],
            ], 200),
        ]);

        $this->crm->delete('Leads', '789');

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'Leads') &&
               str_contains((string) $request->url(), '789'));
    }

    public function test_it_passes_params_to_records_query(): void
    {
        Http::fake([
            '*' => Http::response(['data' => []], 200),
        ]);

        $this->crm->records('Deals', ['per_page' => 50, 'page' => 2]);

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'Deals'));
    }
}
