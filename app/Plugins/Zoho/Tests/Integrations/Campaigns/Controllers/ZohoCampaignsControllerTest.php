<?php

namespace App\Plugins\Zoho\Tests\Integrations\Campaigns\Controllers;

use App\Plugins\Zoho\Integrations\Campaigns\Controllers\ZohoCampaignsController;
use App\Plugins\Zoho\Models\FaveoLocalFields;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use App\Plugins\Zoho\Models\ZohoFields;
use App\Plugins\Zoho\Models\ZohoIntegration;
use App\Plugins\Zoho\Models\ZohoOAuthClient;
use App\Plugins\Zoho\Models\ZohoOAuthToken;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\DBTestCase;

class ZohoCampaignsControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    private ZohoCampaignsController $controller;

    /**
     * Set up default HTTP fakes for Zoho API calls.
     *
     * @param  array<mixed>  $additionalFakes
     */
    protected function fakeZohoHttpCalls(array $additionalFakes = []): void
    {
        $defaults = [
            '*/getmailinglists*' => Http::response([
                'list_of_details' => [
                    ['listname' => 'Test List', 'listkey' => 'test_list_key'],
                ],
            ], 200),
            '*/topics' => Http::response([
                'topicDetails' => [
                    ['topicName' => 'Newsletter', 'topicId' => 1],
                ],
            ], 200),
            '*/contact/allfields*' => Http::response([
                'response' => [
                    'fieldnames' => [
                        'fieldname' => [
                            [
                                'FIELD_ID' => '1',
                                'FIELD_NAME' => 'Contact Email',
                                'DISPLAY_NAME' => 'Email',
                                'UITYPE' => 'email',
                                'IS_MANDATORY' => true,
                            ],
                        ],
                    ],
                ],
            ], 200),
            '*/listsubscribe*' => Http::response(['response' => ['message' => 'success']], 200),
            '*/tag/associate*' => Http::response(['response' => ['message' => 'success']], 200),
            '*/oauth/v2/token*' => Http::response([
                'access_token' => 'refreshed_token',
                'expires_in' => 3600,
            ], 200),
            '*' => Http::response([], 404), // Catch-all for unmocked endpoints
        ];

        // Merge additional fakes, allowing them to override defaults
        Http::fake(array_merge($defaults, $additionalFakes));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $integration = ZohoIntegration::firstOrCreate(['platform' => 'campaigns']);
        ZohoOAuthClient::create([
            'integration_id' => $integration->id,
            'region' => 'us',
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
        ]);
        ZohoOAuthToken::create([
            'integration_id' => $integration->id,
            'access_token' => 'test_token',
            'expires_at' => now()->addHours(2), // Make sure token doesn't expire during test
            'refresh_token' => 'test_refresh_token',
        ]);

        Config::set('zoho_campaigns.defaultListName', 'Test List');

        $this->fakeZohoHttpCalls();

        $this->controller = new ZohoCampaignsController;
    }

    public function test_it_syncs_campaigns_fields_and_topics_successfully(): void
    {
        Config::set('zoho_campaigns.topics', [
            'newsletter' => [
                'name' => 'Newsletter',
                'description' => 'Newsletter topic',
            ],
        ]);

        $response = $this->controller->syncFields();

        $this->assertTrue($response->getData()->success);
        $this->assertDatabaseHas('zoho_fields', [
            'platform' => 'campaigns',
            'module' => 'Contacts',
        ]);
    }

    public function test_it_handles_sync_fields_error(): void
    {
        $this->fakeZohoHttpCalls([
            '*/contact/allfields*' => Http::response([], 500),
        ]);

        $response = $this->controller->syncFields();

        $this->assertFalse($response->getData()->success);
    }

    public function test_it_gets_campaigns_mapped_fields(): void
    {
        $zohoField = ZohoFields::create([
            'platform' => 'campaigns',
            'module' => 'Contacts',
            'display_name' => 'Email',
        ]);

        $localField = FaveoLocalFields::firstOrCreate([
            'field_key' => 'email',
        ]);

        ZohoFieldMappings::create([
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => $localField->id,
        ]);

        $response = $this->controller->getCampaignsMappedFields();

        $this->assertTrue($response->getData()->success);
        $this->assertCount(1, $response->getData()->data);
        $this->assertEquals($zohoField->id, $response->getData()->data[0]->zoho_field_id);
    }

    public function test_it_gets_campaigns_contact_fields(): void
    {
        ZohoFields::create([
            'platform' => 'campaigns',
            'module' => 'Contacts',
            'display_name' => 'First Name',
        ]);

        $response = $this->controller->getCampaignsContactFields();
        $this->assertTrue($response->getData()->success);
        $this->assertCount(1, $response->getData()->data);
        $this->assertEquals('First Name', $response->getData()->data[0]->field_name);
    }

    public function test_it_subscribes_with_topic_name_from_config(): void
    {
        User::create(['email' => 'test@example.com']);

        Config::set('zoho_campaigns.topics.newsletter.name', 'Newsletter');

        $this->controller->subscribe('test@example.com', 'newsletter');

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'listsubscribe'));
    }

    public function test_it_returns_early_when_topic_not_configured(): void
    {
        Config::set('zoho_campaigns.topics.newsletter.name');

        Http::fake();

        $this->controller->subscribe('test@example.com', 'newsletter');

        Http::assertNothingSent();
    }

    public function test_it_subscribes_with_mapped_contact_info(): void
    {
        User::create([
            'email' => 'test@example.com',
            'first_name' => 'John',
        ]);

        $zohoField = ZohoFields::create([
            'platform' => 'campaigns',
            'module' => 'Contacts',
            'zoho_key' => 'First Name',
            'display_name' => 'First Name',
        ]);

        $localField = FaveoLocalFields::firstOrCreate([
            'field_key' => 'first_name',
        ]);

        ZohoFieldMappings::create([
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => $localField->id,
        ]);

        Config::set('zoho_campaigns.topics.newsletter.name', 'Newsletter');

        $this->controller->subscribe('test@example.com', 'newsletter');

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'listsubscribe'));
    }

    public function test_it_subscribes_with_tag(): void
    {
        User::create(['email' => 'test@example.com']);

        Config::set('zoho_campaigns.topics.newsletter.name', 'Newsletter');

        $this->controller->subscribeWithTag('test@example.com', 'newsletter', 'VIP');

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'listsubscribe') ||
               str_contains((string) $request->url(), 'associate'));
    }

    public function test_it_sends_contact_fields_by_display_name_with_the_topic(): void
    {
        User::create(['email' => 'test@example.com', 'first_name' => 'John']);

        $zohoField = ZohoFields::create([
            'platform' => 'campaigns',
            'module' => 'Contacts',
            'zoho_key' => 'firstname',
            'display_name' => 'First Name',
        ]);

        ZohoFieldMappings::create([
            'zoho_field_id' => $zohoField->id,
            'faveo_local_field_id' => FaveoLocalFields::firstOrCreate(['field_key' => 'first_name'])->id,
        ]);

        Config::set('zoho_campaigns.topics.newsletter.name', 'Newsletter');

        $this->controller->subscribe('test@example.com', 'newsletter');

        Http::assertSent(function ($request): bool {
            $url = urldecode((string) $request->url());

            return str_contains($url, 'listsubscribe')
                && str_contains($url, 'topic_id=1')
                && str_contains($url, '"First Name":"John"')
                && ! str_contains($url, '"firstname"');
        });
    }

    public function test_it_saves_the_picked_mailing_list(): void
    {
        $this->controller->saveList(new Request(['list_key' => 'abc']));
        $this->assertSame('abc', ZohoIntegration::wherePlatform('campaigns')->value('list_key'));

        $this->controller->saveList(new Request(['list_key' => null]));
        $this->assertNull(ZohoIntegration::wherePlatform('campaigns')->value('list_key'));
    }

    public function test_get_lists_returns_zoho_lists_and_the_default_name(): void
    {
        $data = $this->controller->getLists()->getData(true)['data'];

        $this->assertSame([['key' => 'test_list_key', 'name' => 'Test List']], $data['lists']);
        $this->assertSame('Test List', $data['default_list_name']);
    }

    public function test_send_test_shows_zohos_reason_when_it_fails(): void
    {
        $admin = User::create(['email' => 'admin-test@example.com']);
        $this->actingAs($admin);
        Config::set('zoho_campaigns.topics.newsletter.name', 'Newsletter');
        Http::swap(new Factory);
        Http::fake([
            '*/getmailinglists*' => Http::response(['list_of_details' => [['listname' => 'Test List', 'listkey' => 'test_list_key']]]),
            '*/topics' => Http::response(['topicDetails' => []]),
            '*/listsubscribe*' => Http::response(['status' => 'error', 'code' => '2501', 'message' => 'Invalid email']),
        ]);

        $response = new ZohoCampaignsController()->sendTest();

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('Invalid email', $response->getData()->message);
    }
}
