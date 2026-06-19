<?php

namespace App\Plugins\Zoho\Integrations\Crm\Controllers\Api;

use App\Plugins\Zoho\Controllers\Api\ZohoBaseApi;
use App\Plugins\Zoho\Integrations\Crm\Controllers\Exceptions\ZohoCrmApiException;
use Illuminate\Http\Client\HttpClientException;

class ZohoCrmApi extends ZohoBaseApi
{
    /**
     * Get fields of a CRM module.
     *
     * @link https://www.zoho.com/crm/developer/docs/api/v8/field-meta.html
     *
     * @param  string  $module  (Leads, Contacts, Deals, etc.)
     *
     * @throws ZohoCrmApiException
     * @throws HttpClientException
     * @return array<mixed>
     */
    public function fields(string $module): array
    {
        $response = $this->newRequest()
            ->get('/crm/v8/settings/fields', [
                'module' => $module,
            ])
            ->json();

        if (isset($response['status']) && $response['status'] === 'error') {
            throw ZohoCrmApiException::fromResponse($response);
        }

        return $response['fields'] ?? [];
    }

    /**
     * Get records from a CRM module.
     *
     * @link https://www.zoho.com/crm/developer/docs/api/v8/get-records.html
     * @param array<mixed> $params
     * @return array<mixed>
     */
    public function records(string $module, array $params = []): array
    {
        $response = $this->newRequest()
            ->get('/crm/v8/'.$module, $params)
            ->json();

        if (isset($response['status']) && $response['status'] === 'error') {
            throw ZohoCrmApiException::fromResponse($response);
        }

        return $response['data'] ?? [];
    }

    /**
     * Create a CRM record.
     *
     * @link https://www.zoho.com/crm/developer/docs/api/v8/insert-records.html
     * @param array<mixed> $data
     */
    public function create(string $module, array $data): void
    {
        $response = $this->newRequest()
            ->post('/crm/v8/'.$module, [
                'data' => [
                    $data,
                ],
            ])
            ->json();

        if (($response['data'][0]['status'] ?? '') !== 'success') {
            throw ZohoCrmApiException::fromResponse($response);
        }
    }

    /**
     * Update a CRM record.
     *
     * @link https://www.zoho.com/crm/developer/docs/api/v8/update-records.html
     * @param array<mixed> $data
     */
    public function update(string $module, string $recordId, array $data): void
    {
        $response = $this->newRequest()
            ->put(sprintf('/crm/v8/%s/%s', $module, $recordId), [
                'data' => [$data],
            ])
            ->json();

        if (($response['data'][0]['status'] ?? '') !== 'success') {
            throw ZohoCrmApiException::fromResponse($response);
        }
    }

    /**
     * Delete a CRM record.
     *
     * @link https://www.zoho.com/crm/developer/docs/api/v8/delete-records.html
     */
    public function delete(string $module, string $recordId): void
    {
        $response = $this->newRequest()
            ->delete(sprintf('/crm/v8/%s/%s', $module, $recordId))
            ->json();

        if (($response['data'][0]['status'] ?? '') !== 'success') {
            throw ZohoCrmApiException::fromResponse($response);
        }
    }

    /**
     * CRM API endpoint.
     */
    protected function endpoint(): string
    {
        return sprintf('https://%s', $this->region->apiDomain());
    }
}
