<?php

namespace App\Plugins\Zoho\Integrations\Campaigns\Controllers;

use App\Plugins\Zoho\Controllers\Api\ZohoAccessToken;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\Api\ZohoCampaignsApi;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\Exceptions\TagNotFoundException;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\Exceptions\ZohoCampaignsApiException;
use App\Plugins\Zoho\Models\ZohoIntegration;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use RuntimeException;

/**
 * @phpstan-import-type ZohoCustomer from ZohoCampaignsApi
 * @phpstan-import-type ZohoTag from ZohoCampaignsApi
 * @phpstan-import-type ZohoContactField from ZohoCampaignsApi
 */
class Campaigns
{
    protected string $defaultListName;

    /**
     * @var Collection<string,array{listKey:string}>
     */
    protected Collection $lists;

    protected ZohoCampaignsApi $zohoApi;

    public function __construct()
    {
        /** @var \App\Plugins\Zoho\Models\ZohoIntegration $campaignsIntegration */
        $campaignsIntegration = ZohoIntegration::with(['client', 'token'])
            ->where('platform', 'campaigns')
            ->first();

        /** @var \App\Plugins\Zoho\Models\ZohoOAuthClient $campaignsClient */
        $campaignsClient = $campaignsIntegration->client;

        $this->zohoApi = new ZohoCampaignsApi(
            getZohoRegion($campaignsClient->region),
            resolve(ZohoAccessToken::class),
            $campaignsIntegration->id
        );

        $this->defaultListName = config('zoho_campaigns.defaultListName', '');
        $this->lists = $this->loadLists();
    }

    /**
     * @param  array<mixed>  $contactInfo
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function subscribe(string $email, array $contactInfo = [], ?string $list = null, ?string $topic = null): void
    {
        $listKey = $this->resolveListKey($list);

        $topicId = $topic !== null ? $this->getTopicId($topic) : null;

        $this->zohoApi->listSubscribe($listKey, $email, $contactInfo, [], $topicId); // @phpstan-ignore argument.type
    }

    /**
     * @param  array<mixed>  $contactInfo
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function resubscribe(string $email, array $contactInfo = [], ?string $list = null, ?string $topic = null): void
    {
        $listKey = $this->resolveListKey($list);

        $topicId = $topic !== null ? $this->getTopicId($topic) : null;

        $additionalParams = ['donotmail_resub' => 'true'];

        $this->zohoApi->listSubscribe($listKey, $email, $contactInfo, $additionalParams, $topicId); // @phpstan-ignore argument.type
    }

    /**
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function unsubscribe(string $email, ?string $list = null): void
    {
        $listKey = $this->resolveListKey($list);

        $this->zohoApi->listUnsubscribe($listKey, $email);
    }

    /**
     * Retrieves subscribers for a given list name.
     *
     * @param  string  $status  The status of the subscribers to retrieve. Possible values are 'active', 'recent', 'mostrecent', 'unsub', and 'bounce'. Default is 'active'
     * @param  string  $sort  The sort order of the results. Possible values are 'asc' and 'desc'. Default is 'asc'.
     * @param  int  $chunkSize  The number of subscribers to retrieve per request.
     * @param  string|null  $list  The name or the key of the list. If null, the default list name will be used.
     * @return LazyCollection<array-key, ZohoCustomer> The list of subscribers.
     */
    public function subscribers(string $status = 'active', string $sort = 'asc', int $chunkSize = 500, ?string $list = null): LazyCollection
    {
        // Zoho API has a limit of 650 subscribers per request.
        $chunkSize = min(650, max(1, $chunkSize));

        $listKey = $this->resolveListKey($list);

        return LazyCollection::make(function () use ($status, $sort, $listKey, $chunkSize) {
            $fromIndex = 1;

            while (true) {
                $response = $this->zohoApi->listSubscribers($listKey, status: $status, sort: $sort, fromIndex: $fromIndex, range: $chunkSize);

                foreach ($response as $subscriber) {
                    yield $subscriber;
                }

                if (count($response) < $chunkSize) {
                    break;
                }

                $fromIndex += $chunkSize;
            }
        });
    }

    /**
     * Retrieves the count of subscribers for a given list name and status.
     *
     * @param  string  $status  The status of the subscribers to count. Possible values are 'active', 'unsub', 'bounce', and 'spam'.
     * @param  string|null  $list  The name or the key of the list. If null, the default list name will be used.
     * @return int The count of subscribers.
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function subscribersCount(string $status = 'active', ?string $list = null): int
    {
        $listKey = $this->resolveListKey($list);

        return $this->zohoApi->listSubscribersCount($listKey, $status);
    }

    /**
     * Retrieve all existing tags.
     *
     * @return Collection<array-key,ZohoTag>
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function tags(): Collection
    {
        return Collection::make($this->zohoApi->tags());
    }

    /**
     * Attach a tag to a contact.
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function attachTag(string $email, string $tag): void
    {
        try {
            $this->zohoApi->tagAssociate($tag, $email);
        } catch (TagNotFoundException) {
            $this->zohoApi->tagCreate($tag);

            $this->zohoApi->tagAssociate($tag, $email);
        }
    }

    /**
     * Detach a tag from a contact.
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function detachTag(string $email, string $tag): void
    {
        try {
            $this->zohoApi->tagDeassociate($tag, $email);
        } catch (TagNotFoundException) {
        }
    }

    /**
     * Get all contact fields.
     *
     * @return Collection<array-key,ZohoContactField>
     *
     * @throws HttpClientException
     */
    public function contactFields(): Collection
    {
        return Collection::make($this->zohoApi->contactFields());
    }

    protected function resolveListKey(?string $list = null): string
    {
        $listName = $list ?? $this->defaultListName;

        $listKey = Arr::get($this->lists->get($listName, []), 'listKey');

        if ($listKey === null && $list === null) {
            throw new RuntimeException(sprintf('Cannot resolve list %s', $listName));
        }

        return $listKey ?? $list;
    }

    /**
     * @return \Illuminate\Support\Collection<int|string, mixed>
     */
    protected function loadLists(): Collection
    {
        return collect($this->zohoApi->lists())
            ->mapWithKeys(fn (array $list): array => [
                $list['listname'] => [
                    'listKey' => $list['listkey'],
                ],
            ]);
    }

    public function syncTopics(): void
    {
        $existingTopics = collect($this->zohoApi->topics())
            ->keyBy(fn ($topic) => strtolower(trim($topic['topicName'] ?? '')));

        $configuredTopics = collect(config('zoho_campaigns.topics', []));

        foreach ($configuredTopics as $topicConfig) {
            $name = trim($topicConfig['name'] ?? '');
            $description = trim($topicConfig['description'] ?? '');

            if ($name === '') {
                continue;
            }

            // Topic already exists → skip
            if ($existingTopics->has(strtolower($name))) {
                continue;
            }

            $this->zohoApi->topicCreate($name, $description);
        }
    }

    /**
     * Get Zoho topic ID by topic name.
     */
    protected function getTopicId(string $topicName): ?string
    {
        $topicName = strtolower(trim($topicName));

        $topics = collect($this->zohoApi->topics());

        $matched = $topics->first(fn ($topic): bool => strtolower(trim($topic['topicName'] ?? '')) === $topicName);

        if ($matched) {
            return $matched['topicId'] ?? null;
        }

        $first = $topics->first();

        return $first['topicId'] ?? null;
    }
}
