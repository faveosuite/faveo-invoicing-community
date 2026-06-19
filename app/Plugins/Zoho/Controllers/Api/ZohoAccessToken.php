<?php

namespace App\Plugins\Zoho\Controllers\Api;

use App\Plugins\Zoho\Models\ZohoOAuthClient;
use App\Plugins\Zoho\Models\ZohoOAuthToken;
use Illuminate\Support\Arr;

class ZohoAccessToken
{
    /**
     * @var array<mixed>
     */
    protected array $accessToken = [];

    /**
     * @var array<mixed>
     */
    protected array $refreshToken = [];

    public function get(int $integrationId): string
    {
        if (! isset($this->accessToken[$integrationId])) {
            $this->accessToken[$integrationId] = ZohoOAuthToken::findActiveAccessToken($integrationId) ?? $this->refreshAccessToken($integrationId);
        }

        if ($this->accessToken[$integrationId] === null) {
            return '';
        }

        if (! $this->accessToken[$integrationId]->isValid(now()->addMinute())) {
            $this->accessToken[$integrationId] = $this->refreshAccessToken($integrationId);
        }

        return $this->accessToken[$integrationId]->access_token ?? '';
    }

    protected function refreshAccessToken(int $integrationId): ?ZohoOAuthToken
    {
        if (! isset($this->refreshToken[$integrationId])) {
            $this->refreshToken[$integrationId] = ZohoOAuthToken::findRefreshToken($integrationId);
        }

        if ($this->refreshToken[$integrationId] === null) {
            return null;
        }

        /** @var \App\Plugins\Zoho\Models\ZohoOAuthClient $zohoIntegration */
        $zohoIntegration = ZohoOAuthClient::whereIntegrationId($integrationId)->first();

        /** @var \App\Plugins\Zoho\Models\ZohoOAuthToken $zohoToken */
        $zohoToken = ZohoOAuthToken::findRefreshToken($integrationId);
        $refreshToken = $zohoToken->refresh_token;

        $response = new ZohoAccountsApi(
            $zohoIntegration->client_id,
            $zohoIntegration->client_secret,
            getZohoRegion($zohoIntegration->region)
        )->refreshAccessToken(
            $refreshToken
        );

        if (Arr::get($response, 'access_token') === null) {
            return null;
        }

        return ZohoOAuthToken::saveAccessToken($integrationId, $response['access_token'], $response['expires_in']);
    }
}
