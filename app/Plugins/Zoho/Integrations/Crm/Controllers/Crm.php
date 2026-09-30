<?php

namespace App\Plugins\Zoho\Integrations\Crm\Controllers;

use App\Plugins\Zoho\Controllers\Api\ZohoAccessToken;
use App\Plugins\Zoho\Integrations\Crm\Controllers\Api\ZohoCrmApi;
use App\Plugins\Zoho\Integrations\Crm\Controllers\Exceptions\ZohoCrmApiException;
use App\Plugins\Zoho\Models\ZohoIntegration;
use App\Plugins\Zoho\Models\ZohoOAuthClient;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Collection;

/**
 * CRM service wrapper.
 */
class Crm
{
    protected ZohoCrmApi $zohoApi;

    public function __construct()
    {
        $crmIntegration = ZohoIntegration::with(['client', 'token'])
            ->where('platform', 'crm')
            ->firstOrFail();

        /** @var ZohoOAuthClient $crmIntClient */
        $crmIntClient = $crmIntegration->client;

        $this->zohoApi = new ZohoCrmApi(
            getZohoRegion($crmIntClient->region),
            resolve(ZohoAccessToken::class),
            $crmIntegration->id
        );
    }

    /**
     * Get fields of a CRM module.
     *
     * @param  string  $module  (Leads, Contacts, Deals, etc.)
     * @return Collection<int|string, mixed>
     *
     * @throws ZohoCrmApiException
     * @throws HttpClientException
     */
    public function fields(string $module): Collection
    {
        return Collection::make(
            $this->zohoApi->fields($module)
        );
    }

    /**
     * Get records from a CRM module.
     *
     *
     * @param  array<mixed>  $params
     * @return Collection<int|string, mixed>
     *
     * @throws ZohoCrmApiException
     * @throws HttpClientException
     */
    public function records(string $module, array $params = []): Collection
    {
        return Collection::make(
            $this->zohoApi->records($module, $params)
        );
    }

    /**
     * Create a CRM record.
     *
     *
     * @param  array<mixed>  $data
     *
     * @throws ZohoCrmApiException
     * @throws HttpClientException
     */
    public function create(string $module, array $data): void
    {
        $this->zohoApi->create($module, $data);
    }

    /**
     * Update a CRM record.
     *
     *
     * @param  array<mixed>  $data
     *
     * @throws ZohoCrmApiException
     * @throws HttpClientException
     */
    public function update(string $module, string $recordId, array $data): void
    {
        $this->zohoApi->update($module, $recordId, $data);
    }

    /**
     * Delete a CRM record.
     *
     *
     * @throws ZohoCrmApiException
     * @throws HttpClientException
     */
    public function delete(string $module, string $recordId): void
    {
        $this->zohoApi->delete($module, $recordId);
    }
}
