<?php

namespace App\Plugins\Zoho\Tests\Integrations\Campaigns\Controllers;

use App\Plugins\Zoho\Integrations\Campaigns\Controllers\Campaigns;
use App\Plugins\Zoho\Models\ZohoIntegration;
use App\Plugins\Zoho\Models\ZohoOAuthClient;
use App\Plugins\Zoho\Models\ZohoOAuthToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\LazyCollection;
use RuntimeException;
use Tests\DBTestCase;

class CampaignsTest extends DBTestCase
{
    use DatabaseTransactions;

    private Campaigns $campaigns;

    protected function setUp(): void
    {
        parent::setUp();

        $integration = ZohoIntegration::wherePlatform('campaigns')->firstOrFail();

        ZohoOAuthClient::create([
            'integration_id' => $integration->id,
            'region' => 'us',
        ]);
        ZohoOAuthToken::create([
            'integration_id' => $integration->id,
            'access_token' => 'test_token',
            'expires_at' => now()->addHour(),
        ]);

        Config::set('zoho_campaigns.defaultListName', 'Test List');

        Http::fake([
            '*/api/v1.1/getmailinglists*' => Http::response([
                'list_of_details' => [
                    ['listname' => 'Test List', 'listkey' => 'test_list_key'],
                ],
            ], 200),
        ]);

        $this->campaigns = new Campaigns;
    }

    public function test_it_subscribes_contact_to_list(): void
    {
        Http::fake([
            '*' => Http::response([
                'response' => ['message' => 'success'],
            ], 200),
        ]);

        $this->campaigns->subscribe('test@example.com', ['First_Name' => 'John']);

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'listsubscribe'));
    }

    public function test_it_resubscribes_contact_to_list(): void
    {
        Http::fake([
            '*' => Http::response([
                'response' => ['message' => 'success'],
            ], 200),
        ]);

        $this->campaigns->resubscribe('test@example.com', ['First_Name' => 'John']);

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'donotmail_resub=true'));
    }

    public function test_it_unsubscribes_contact_from_list(): void
    {
        Http::fake([
            '*' => Http::response([
                'response' => ['message' => 'success'],
            ], 200),
        ]);

        $this->campaigns->unsubscribe('test@example.com');

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'listunsubscribe'));
    }

    public function test_it_retrieves_subscribers_lazily(): void
    {
        Http::fake([
            '*' => Http::response([
                'list_of_details' => [
                    ['contact_email' => 'subscriber1@example.com'],
                    ['contact_email' => 'subscriber2@example.com'],
                ],
            ], 200),
        ]);

        $subscribers = $this->campaigns->subscribers('active', 'asc', 500);

        $this->assertInstanceOf(LazyCollection::class, $subscribers);
        $this->assertCount(2, $subscribers->take(10));
    }

    public function test_it_retrieves_subscribers_count(): void
    {
        Http::fake([
            '*' => Http::response([
                'list_of_details' => [
                    'subscriber_count' => 100,
                ],
            ], 200),
        ]);

        $count = $this->campaigns->subscribersCount('active');

        $this->assertIsInt($count); // @phpstan-ignore method.alreadyNarrowedType
    }

    public function test_it_retrieves_all_tags(): void
    {
        Http::fake([
            '*' => Http::response([
                'tags' => [
                    ['tag_name' => 'VIP', 'tag_id' => '1'],
                    ['tag_name' => 'Customer', 'tag_id' => '2'],
                ],
            ], 200),
        ]);

        $tags = $this->campaigns->tags();

        $this->assertInstanceOf(Collection::class, $tags);
        $this->assertGreaterThanOrEqual(2, $tags->count());
    }

    public function test_it_attaches_tag_to_contact(): void
    {
        Http::fake([
            '*/api/v1.1/getmailinglists*' => Http::response([
                'list_of_details' => [
                    ['listname' => 'Test List', 'listkey' => 'test_list_key'],
                ],
            ], 200),
            '*/tag/associate*' => Http::response([
                'response' => ['message' => 'success'],
            ], 200),
        ]);

        $this->campaigns->attachTag('test@example.com', 'VIP');

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'associate'));
    }

    public function test_it_creates_tag_if_not_exists_when_attaching(): void
    {
        Http::fake([
            '*/api/v1.1/getmailinglists*' => Http::response([
                'list_of_details' => [
                    ['listname' => 'Test List', 'listkey' => 'test_list_key'],
                ],
            ], 200),
            '*/tag/associate*' => Http::sequence()
                ->push(['status' => 'error', 'code' => '992', 'message' => 'Tag not found'], 200)
                ->push(['response' => ['message' => 'success']], 200),
            '*/tag/add*' => Http::response(['response' => ['message' => 'Tag created']], 200),
        ]);

        $this->campaigns->attachTag('test@example.com', 'NewTag');

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'tag/add'));
    }

    public function test_it_detaches_tag_from_contact(): void
    {
        Http::fake([
            '*/api/v1.1/getmailinglists*' => Http::response([
                'list_of_details' => [
                    ['listname' => 'Test List', 'listkey' => 'test_list_key'],
                ],
            ], 200),
            '*/tag/deassociate*' => Http::response([
                'response' => ['message' => 'success'],
            ], 200),
        ]);

        $this->campaigns->detachTag('test@example.com', 'VIP');

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'tag/deassociate'));
    }

    public function test_it_handles_detach_tag_when_tag_not_found(): void
    {
        Http::fake([
            '*/api/v1.1/getmailinglists*' => Http::response([
                'list_of_details' => [
                    ['listname' => 'Test List', 'listkey' => 'test_list_key'],
                ],
            ], 200),
            '*/tag/deassociate*' => Http::response([
                'status' => 'error',
                'code' => '9001',
                'message' => 'Tag not found',
            ], 200),
        ]);

        // Should not throw exception
        $this->campaigns->detachTag('test@example.com', 'NonExistent');

        $this->assertTrue(condition: true); // @phpstan-ignore method.alreadyNarrowedType, argument.named
    }

    public function test_it_retrieves_contact_fields(): void
    {
        Http::fake([
            '*' => Http::response([
                [
                    'FIELD_ID' => '1',
                    'FIELD_NAME' => 'Contact Email',
                    'DISPLAY_NAME' => 'Email',
                ],
            ], 200),
        ]);

        $fields = $this->campaigns->contactFields();

        $this->assertInstanceOf(Collection::class, $fields);
    }

    public function test_it_syncs_topics_from_config(): void
    {
        Config::set('zoho_campaigns.topics', [
            'newsletter' => [
                'name' => 'Newsletter',
                'description' => 'Monthly newsletter',
            ],
        ]);

        Http::fake([
            '*/api/v1.1/getmailinglists*' => Http::response([
                'list_of_details' => [
                    ['listname' => 'Test List', 'listkey' => 'test_list_key'],
                ],
            ], 200),
            '*/api/v1.1/topics' => Http::response([
                'topicDetails' => [
                    ['topicName' => 'Existing Topic', 'topicId' => '1'],
                ],
            ], 200),
            '*/api/v1.1/topics?*' => Http::response(['code' => '200', 'message' => 'Topic created'], 200),
        ]);

        $this->campaigns->syncTopics();

        Http::assertSent(fn ($request): bool => $request->method() === 'POST' && str_contains((string) $request->url(), 'topics'));
    }

    public function test_it_skips_existing_topics_when_syncing(): void
    {
        Config::set('zoho_campaigns.topics', [
            'newsletter' => [
                'name' => 'Newsletter',
                'description' => 'Monthly newsletter',
            ],
        ]);

        Http::fake([
            '*/api/v1.1/getmailinglists*' => Http::response([
                'list_of_details' => [
                    ['listname' => 'Test List', 'listkey' => 'test_list_key'],
                ],
            ], 200),
            '*/api/v1.1/topics' => Http::response([
                'topicDetails' => [
                    ['topicName' => 'Newsletter', 'topicId' => '1'],
                ],
            ], 200),
        ]);

        $this->campaigns->syncTopics();

        Http::assertSent(fn ($request): bool => $request->method() === 'GET' &&
            str_contains((string) $request->url(), 'topics'));
    }

    public function test_it_resolves_list_key_from_list_name(): void
    {
        Http::fake([
            '*' => Http::response(['response' => ['message' => 'success']], 200),
        ]);

        $this->campaigns->subscribe('test@example.com', [], 'Test List');

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'test_list_key'));
    }

    public function test_it_throws_exception_when_default_list_cannot_be_resolved(): void
    {
        $this->expectException(RuntimeException::class);

        Config::set('zoho_campaigns.defaultListName', 'NonExistent List');

        Http::fake([
            '*/api/v2/lists*' => Http::response([
                'list_of_details' => [
                    ['listname' => 'Other List', 'listkey' => 'other_key'],
                ],
            ], 200),
        ]);

        $campaigns = new Campaigns;
        $campaigns->subscribe('test@example.com');
    }

    public function test_it_limits_chunk_size_to_650_for_subscribers(): void
    {
        Http::fake([
            '*/api/v1.1/getmailinglists*' => Http::response([
                'list_of_details' => [
                    ['listname' => 'Test List', 'listkey' => 'test_list_key'],
                ],
            ], 200),
            '*/getlistsubscribers*' => Http::response([
                'list_of_details' => [],
            ], 200),
        ]);

        $subscribers = $this->campaigns->subscribers('active', 'asc', 1000);

        // Force lazy collection evaluation by iterating
        $subscribers->take(1)->all();

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'range=650'));
    }

    public function test_it_subscribes_with_topic(): void
    {
        Config::set('zoho_campaigns.topics.newsletter.name', 'Newsletter');

        Http::fake([
            '*/topics*' => Http::response([
                ['topicName' => 'Newsletter', 'topicId' => 'topic_123'],
            ], 200),
            '*' => Http::response(['response' => ['message' => 'success']], 200),
        ]);

        $this->campaigns->subscribe('test@example.com', [], topic: 'Newsletter');

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'listsubscribe'));
    }
}
