<?php

namespace App\Plugins\Zoho\Integrations\Campaigns\Controllers\Api;

use App\Plugins\Zoho\Controllers\Api\ZohoBaseApi;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\Exceptions\ContactNotFoundException;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\Exceptions\TagNotFoundException;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\Exceptions\ZohoCampaignsApiException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Override;

class ZohoCampaignsApi extends ZohoBaseApi
{
    /**
     * Subscribes a contact to a list.
     *
     * @link https://www.zoho.com/campaigns/help/developers/contact-subscribe.html
     *
     * @param  string  $listKey  The list key.
     * @param  string  $email  The email address to subscribe.
     * @param  array  $contactInfo  Additional contact information to subscribe.
     * @param  array  $additionalParams  Additional parameters to pass to the API.
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function listSubscribe(string $listKey, string $email, array $contactInfo = [], array $additionalParams = [], ?int $topic = null): void
    {
        $params = array_merge([
            'listkey' => $listKey,
            'resfmt' => 'JSON',
            'topic_id' => $topic,
            'contactinfo' => json_encode(array_merge([
                'Contact Email' => $email,
            ], $contactInfo)),
        ], $additionalParams);

        $response = $this->newRequest()
            ->post(sprintf('/json/listsubscribe?%s', http_build_query($params)))
            ->json();

        if (isset($response['status']) && $response['status'] === 'error') {
            throw ZohoCampaignsApiException::fromResponse($response);
        }
    }

    /**
     * Unsubscribes a contact from a list.
     *
     * @link https://www.zoho.com/campaigns/help/developers/contact-unsubscribe.html
     *
     * @param  string  $listKey  The list key.
     * @param  string  $email  The email address to unsubscribe.
     * @param  array  $additionalParams  Additional parameters to pass to the API.
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function listUnsubscribe(string $listKey, string $email, array $additionalParams = []): void
    {
        $params = array_merge([
            'listkey' => $listKey,
            'resfmt' => 'JSON',
            'contactinfo' => json_encode([
                'Contact Email' => $email,
            ]),
        ], $additionalParams);

        $response = $this->newRequest()
            ->post(sprintf('/json/listunsubscribe?%s', http_build_query($params)))
            ->json();

        if (isset($response['status']) && $response['status'] === 'error') {
            throw ZohoCampaignsApiException::fromResponse($response);
        }
    }

    /**
     * Retrieves the list of subscribers for a given list key with various options.
     *
     * @link https://www.zoho.com/campaigns/help/developers/get-list-subscribers.html
     *
     * @param  string  $listKey  The list key.
     * @param  string  $status  The status of the subscribers to retrieve. Possible values are 'active', 'recent', 'mostrecent', 'unsub', and 'bounce'. Default is 'active'
     * @param  string  $sort  The sort order of the results. Possible values are 'asc' and 'desc'. Default is 'asc'.
     * @param  int  $fromIndex  The starting index for the results. Default is 1.
     * @param  int  $range  The range of results to retrieve. Default is 25.
     * @return array<array-key, ZohoCustomer> The list of subscribers.
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function listSubscribers(
        string $listKey,
        string $status = 'active',
        string $sort = 'asc',
        int $fromIndex = 1,
        int $range = 20,
        array $additionalParams = []
    ): array {
        $params = array_merge([
            'listkey' => $listKey,
            'resfmt' => 'JSON',
            'fromindex' => $fromIndex,
            'range' => $range,
            'sort' => $sort,
            'status' => $status,
        ], $additionalParams);

        $response = $this->newRequest()
            ->get(sprintf('/getlistsubscribers?%s', http_build_query($params)))
            ->json();

        if (isset($response['status']) && $response['status'] === 'error') {
            // If there are no other subscribers the api will return error 2502 with message "Yet,There are no contacts in this list."
            if ($response['code'] === '2502') {
                return [];
            }

            throw ZohoCampaignsApiException::fromResponse($response);
        }

        return $response['list_of_details'] ?? [];
    }

    /**
     * Retrieves the count of subscribers for a given list key and status.
     *
     * @link https://www.zoho.com/campaigns/help/developers/view-total-contacts.html
     *
     * @param  string  $listKey  The list key.
     * @param  string  $status  The status of the subscribers to retrieve. Possible values are 'active', 'unsub', 'bounce' and 'spam'. Default is 'active'
     * @return int The count of subscribers.
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function listSubscribersCount(
        string $listKey,
        string $status = 'active',
        array $additionalParams = []
    ): int {
        $params = array_merge([
            'listkey' => $listKey,
            'resfmt' => 'JSON',
            'status' => $status,
        ], $additionalParams);

        $response = $this->newRequest()
            ->get(sprintf('/listsubscriberscount?%s', http_build_query($params)))
            ->json();

        if (isset($response['status']) && $response['status'] === 'error') {
            throw ZohoCampaignsApiException::fromResponse($response);
        }

        return $response['no_of_contacts'] ?? 0;
    }

    /**
     * Create a new tag to associate with contacts.
     *
     * @link https://www.zoho.com/campaigns/help/developers/tag-management/create-tag.html
     *
     * @param  array{
     *     tagDesc?: string,
     *     color?: string,
     * }  $additionalParams
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function tagCreate(string $tagName, array $additionalParams = []): void
    {
        $params = array_merge([
            'resfmt' => 'JSON',
            'tagName' => $tagName,
        ], $additionalParams);

        $response = $this->newRequest()
            ->get(sprintf('/tag/add?%s', http_build_query($params)))
            ->json();

        if (isset($response['status']) && $response['status'] === 'error') {
            throw ZohoCampaignsApiException::fromResponse($response);
        }
    }

    /**
     * Delete an existing tag.
     *
     * @link https://www.zoho.com/campaigns/help/developers/tag-management/delete-tag.html
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function tagDelete(string $tagName): void
    {
        $response = $this->newRequest()
            ->get(sprintf('/tag/delete?%s', http_build_query([
                'resfmt' => 'JSON',
                'tagName' => $tagName,
            ])))
            ->json();

        if (isset($response['status']) && $response['status'] === 'error') {
            throw ZohoCampaignsApiException::fromResponse($response);
        }
    }

    /**
     * Retrieve all existing tags.
     *
     * @link https://www.zoho.com/campaigns/help/developers/tag-management/get-all-tags.html
     *
     * @return array<array-key, ZohoTag>
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function tags(): array
    {
        $response = $this->newRequest()
            ->get(sprintf('/tag/getalltags?%s', http_build_query([
                'resfmt' => 'JSON',
            ])))
            ->json();

        if (isset($response['status']) && $response['status'] === 'error') {
            throw ZohoCampaignsApiException::fromResponse($response);
        }

        return Collection::make($response['tags'] ?? [])
            ->flatMap(fn (array $tag) => $tag)
            ->all();
    }

    /**
     * Associate a tag with a contact.
     *
     * @link https://www.zoho.com/campaigns/help/developers/tag-management/associate-tag.html
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function tagAssociate(string $tagName, string $email): void
    {
        $params = [
            'resfmt' => 'JSON',
            'tagName' => $tagName,
            'lead_email' => $email,
        ];

        $response = $this->newRequest()
            ->get(sprintf('/tag/associate?%s', http_build_query($params)))
            ->json();

        if (isset($response['status']) && $response['status'] === 'error') {
            throw match ($response['code']) {
                '993' => ContactNotFoundException::fromResponse($response),
                '992', '9001' => TagNotFoundException::fromResponse($response),
                default => ZohoCampaignsApiException::fromResponse($response),
            };
        }
    }

    /**
     * Deassociate a tag from a contact.
     *
     * @link https://www.zoho.com/campaigns/help/developers/tag-management/deassociate-tag.html
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function tagDeassociate(string $tagName, string $email): void
    {
        $params = [
            'resfmt' => 'JSON',
            'tagName' => $tagName,
            'lead_email' => $email,
        ];

        $response = $this->newRequest()
            ->get(sprintf('/tag/deassociate?%s', http_build_query($params)))
            ->json();

        if (isset($response['status']) && $response['status'] === 'error') {
            throw match ((int) $response['code']) {
                993, 9001 => TagNotFoundException::fromResponse($response),
                default => ZohoCampaignsApiException::fromResponse($response),
            };
        }
    }

    /**
     * Get all contact fields.
     *
     * @link https://www.zoho.com/campaigns/help/developers/get-contact-fields.html
     *
     * @return array<array-key, ZohoContactField>
     *
     * @throws HttpClientException
     */
    public function contactFields(): array
    {
        $response = $this->newRequest()
            ->get(sprintf('/contact/allfields?%s', http_build_query([
                'type' => 'json',
            ])))
            ->json();

        return $response['response']['fieldnames']['fieldname'] ?? [];
    }

    /**
     * Get all mailing lists.
     *
     * @link https://www.zoho.com/campaigns/help/developers/get-mailing-lists.html
     *
     * @return array<int,array{
     *     listname: string,
     *     listkey: string,
     *     no_of_contacts?: int
     * }>
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function lists(): array
    {
        $response = $this->newRequest()
            ->get('/getmailinglists?resfmt=JSON')
            ->json();

        if (($response['status'] ?? '') === 'error') {
            throw ZohoCampaignsApiException::fromResponse($response);
        }

        return $response['list_of_details'] ?? [];
    }

    /**
     * Get all topics.
     *
     * @link https://www.zoho.com/campaigns/help/developers/get-topics.html
     *
     * @return array<int,array>
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function topics(): array
    {
        $response = $this->newRequest()
            ->get('/topics')
            ->json();

        if (($response['status'] ?? '') === 'error') {
            throw ZohoCampaignsApiException::fromResponse($response);
        }

        return $response['topicDetails'] ?? [];
    }

    /**
     * Create a new topic.
     *
     * @link https://www.zoho.com/campaigns/help/developers/create-topic.html
     *
     * @throws ZohoCampaignsApiException
     * @throws HttpClientException
     */
    public function topicCreate(string $topicName, string $description = ''): void
    {
        $params = [
            'details' => json_encode([
                'topic_name' => $topicName,
                'topic_desc' => $description,
            ]),
        ];

        $response = $this->newRequest()
            ->post(sprintf('/topics?%s', http_build_query($params)))
            ->json();

        if (($response['code'] ?? '') !== '200') {
            throw ZohoCampaignsApiException::fromResponse($response ?? []);
        }
    }

    #[Override]
    protected function newRequest(): PendingRequest
    {
        return parent::newRequest()->throw();
    }

    protected function endpoint(): string
    {
        return sprintf('https://%s/api/v1.1', $this->region->campaignsDomain());
    }
}
