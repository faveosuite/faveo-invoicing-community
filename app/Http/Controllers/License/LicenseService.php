<?php

namespace App\Http\Controllers\License;

use App\ApiKey;

class LicenseService
{
    /** How long (seconds) the lock can be held before auto-release */
    private const int LOCK_TTL = 10;

    /** How long (seconds) a blocked process will wait for the lock */
    private const int LOCK_WAIT = 15;

    /** Buffer (seconds) — treat tokens as expired this far before actual expiry */
    private const int EXPIRY_BUFFER = 60;

    private string $url;

    private string $clientId;

    private string $clientSecret;

    private string $grantType;

    private string $apiKeySecret;

    public function __construct()
    {
        $license = ApiKey::first();

        $this->url = $license->license_api_url ?? '';
        $this->clientId = $license->license_client_id ?? '';
        $this->clientSecret = $license->license_client_secret ?? '';
        $this->grantType = $license->license_grant_type ?? '';
        $this->apiKeySecret = $license->license_api_secret ?? '';
    }

    /**
     * Get a valid OAuth access token, refreshing if needed.
     */
    public function getValidToken(): string
    {
        $tokenData = $this->getStoredToken();

        // Happy path: token exists and isn't close to expiry
        if ($tokenData && ! $this->isExpired($tokenData)) {
            return $tokenData['access_token'];
        }

        // Token is expired or missing — enter the critical section
        return $this->refreshWithLock();
    }

    /**
     * Return the API key secret.
     */
    public function getApiKeySecret(): string
    {
        return $this->apiKeySecret;
    }

    /**
     * Return the base API URL.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    private function refreshWithLock(): string
    {
        $lock = \Cache::lock($this->lockKey(), self::LOCK_TTL);

        $result = $lock->block(self::LOCK_WAIT, function () {
            // Re-check: another process may have refreshed while we waited
            $tokenData = $this->getStoredToken();
            if ($tokenData && ! $this->isExpired($tokenData)) {
                return $tokenData['access_token'];
            }

            return $this->executeRefresh();
        });

        if ($result === false) {
            throw new \Exception(
                'Could not acquire token-refresh lock within '.self::LOCK_WAIT.'s'
            );
        }

        return $result;
    }

    private function executeRefresh(): string
    {
        $response = \Http::asForm()
            ->timeout(8)
            ->retry(2, 500, throw: false)
            ->post($this->url.'oauth/token', [
                'grant_type' => $this->grantType,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

        if ($response->failed()) {
            throw new \Exception(
                "Token refresh failed with status {$response->status()}: {$response->body()}"
            );
        }

        $data = $response->json();

        $tokenData = [
            'access_token' => $data['access_token'],
            'expires_in' => $data['expires_in'] ?? 3600,
            'stored_at' => now()->timestamp,
            'token_type' => $data['token_type'] ?? 'Bearer',
        ];

        $ttl = now()->addSeconds($tokenData['expires_in']);
        \Cache::put($this->tokenCacheKey(), $tokenData, $ttl);

        return $tokenData['access_token'];
    }

    private function isExpired(array $tokenData): bool
    {
        $expiresAt = ($tokenData['stored_at'] ?? 0) + ($tokenData['expires_in'] ?? 0);

        return now()->timestamp >= ($expiresAt - self::EXPIRY_BUFFER);
    }

    private function getStoredToken(): ?array
    {
        return \Cache::get($this->tokenCacheKey());
    }

    private function tokenCacheKey(): string
    {
        return 'license_response_'.md5($this->url.$this->clientId);
    }

    private function lockKey(): string
    {
        return 'oauth_lock_license_response_'.md5($this->url.$this->clientId);
    }
}
