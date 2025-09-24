<?php

namespace App\Plugins\Zoho\Controllers\Api;

use App\Plugins\Zoho\Models\ZohoOAuthClient;
use App\Plugins\Zoho\Models\ZohoOAuthToken;
use Illuminate\Support\Arr;

class ZohoAccessToken
{
    protected array $accessToken = [];
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

        $zohoIntegration = ZohoOAuthClient::whereIntegrationId($integrationId)->first();

        $refreshToken = ZohoOAuthToken::findRefreshToken($integrationId)->refresh_token;

        $response = (new ZohoAccountsApi(
            $zohoIntegration->client_id,
            $zohoIntegration->client_secret,
            getZohoRegion($zohoIntegration->region)
        ))->refreshAccessToken(
            $refreshToken
        );

        if (Arr::get($response, 'access_token') === null) {
            return null;
        }

        return ZohoOAuthToken::saveAccessToken($integrationId, $response['access_token'], $response['expires_in']);
    }
}
