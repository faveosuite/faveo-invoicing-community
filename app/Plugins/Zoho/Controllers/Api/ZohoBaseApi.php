<?php

namespace App\Plugins\Zoho\Controllers\Api;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

abstract class ZohoBaseApi
{
    public function __construct(
        protected ZohoRegion $region,
        protected ZohoAccessToken $accessToken,
        protected int $integrationId
    ) {}

    protected function newRequest(): PendingRequest
    {
        return Http::baseUrl($this->endpoint())
            ->withToken(
                $this->accessToken->get($this->integrationId),
                'Zoho-oauthtoken'
            )
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]);
    }

    abstract protected function endpoint(): string;
}
