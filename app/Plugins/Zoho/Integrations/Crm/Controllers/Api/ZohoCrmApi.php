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
     * @link https://www.zoho.com/crm/developer/docs/api/v2/get-fields.html
     *
     * @param  string  $module  (Leads, Contacts, Deals, etc.)
     * @return array
     *
     * @throws ZohoCrmApiException
     * @throws HttpClientException
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
     * @link https://www.zoho.com/crm/developer/docs/api/v2/get-records.html
     *
     * @param  string  $module
     * @param  array  $params
     * @return array
     */
    public function records(string $module, array $params = []): array
    {
        $response = $this->newRequest()
            ->get("/crm/v8/{$module}", $params)
            ->json();

        if (isset($response['status']) && $response['status'] === 'error') {
            throw ZohoCrmApiException::fromResponse($response);
        }

        return $response['data'] ?? [];
    }

    /**
     * Create a CRM record.
     *
     * @link https://www.zoho.com/crm/developer/docs/api/v2/insert-records.html
     *
     * @param  string  $module
     * @param  array  $data
     */
    public function create(string $module, array $data): void
    {
        $response = $this->newRequest()
            ->post("/crm/v8/{$module}", [
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
     * @link https://www.zoho.com/crm/developer/docs/api/v2/update-records.html
     */
    public function update(string $module, string $recordId, array $data): void
    {
        $response = $this->newRequest()
            ->put("/crm/v8/{$module}/{$recordId}", [
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
     * @link https://www.zoho.com/crm/developer/docs/api/v2/delete-records.html
     */
    public function delete(string $module, string $recordId): void
    {
        $response = $this->newRequest()
            ->delete("/crm/v8/{$module}/{$recordId}")
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
