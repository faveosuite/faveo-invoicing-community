<?php

namespace App\Plugins\Zoho\Controllers\Api;

use App\Plugins\Zoho\Controllers\Exceptions\ZohoAccountsApiException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Facades\Http;

class ZohoAccountsApi
{
    public function __construct(
        protected string $clientId,
        protected string $clientSecret,
        protected ZohoRegion $region,
    ) {}

    /**
     * @return array{
     *     access_token: string,
     *     refresh_token: string,
     *     api_domain: string,
     *     token_type: string,
     *     expires_in: int,
     * }
     *
     * @throws ZohoAccountsApiException
     * @throws HttpClientException
     */
    public function generateAccessToken(string $authorizationCode): array
    {
        $params = http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'code' => $authorizationCode,
        ]);

        $response = Http::post(sprintf('%s/token?%s', $this->endpoint(), $params))
            ->throw()
            ->json();

        if (isset($response['error'])) {
            throw match ($response['error']) {
                'invalid_client' => ZohoAccountsApiException::invalidClient(),
                'invalid_client_secret' => ZohoAccountsApiException::invalidClientSecret(),
                'invalid_code' => ZohoAccountsApiException::invalidCode(),
                default => new ZohoAccountsApiException($response['error'], 'An error occurred while generating access token'),
            };
        }

        return $response;
    }

    /**
     * @return array{
     *     access_token: string,
     *     api_domain: string,
     *     token_type: string,
     *     expires_in: int,
     * }
     *
     * @throws ZohoAccountsApiException
     * @throws HttpClientException
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $params = http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        $response = Http::post(sprintf('%s/token?%s', $this->endpoint(), $params))
            ->throw()
            ->json();

        if (isset($response['error'])) {
            throw match ($response['error']) {
                'invalid_client' => ZohoAccountsApiException::invalidClient(),
                'invalid_client_secret' => ZohoAccountsApiException::invalidClientSecret(),
                'invalid_code' => ZohoAccountsApiException::invalidCode(),
                default => new ZohoAccountsApiException($response['error'], 'An error occurred while refreshing access token'),
            };
        }

        return $response;
    }

    protected function endpoint(): string
    {
        return sprintf('https://%s/oauth/v2', $this->region->accountsDomain());
    }
}