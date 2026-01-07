<?php

namespace App\Plugins\Zoho\Controllers\Api;

use App\Plugins\Zoho\Models\ZohoOAuthClient;
use App\Plugins\Zoho\Models\ZohoOAuthToken;
use Illuminate\Support\Arr;

class ZohoAccessToken
{
    protected ?ZohoOAuthToken $accessToken = null;
    protected ?ZohoOAuthToken $refreshToken = null;

    public function get(int $integrationId): string
    {
        if ($this->accessToken === null) {
            $this->accessToken = ZohoOAuthToken::findActiveAccessToken($integrationId) ?? $this->refreshAccessToken($integrationId);
        }

        if ($this->accessToken === null) {
            return '';
        }

        if (! $this->accessToken->isValid(now()->addMinute())) {
            $this->accessToken = $this->refreshAccessToken($integrationId);
        }

        return $this->accessToken->access_token ?? '';
    }

    protected function refreshAccessToken(int $integrationId): ?ZohoOAuthToken
    {
        if ($this->refreshToken === null) {
            $this->refreshToken = ZohoOAuthToken::findRefreshToken($integrationId);
        }

        if ($this->refreshToken === null) {
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
