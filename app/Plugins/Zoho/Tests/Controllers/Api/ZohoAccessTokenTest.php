<?php

namespace App\Plugins\Zoho\Tests\Controllers\Api;

use App\Plugins\Zoho\Controllers\Api\ZohoAccessToken;
use App\Plugins\Zoho\Controllers\Exceptions\ZohoAccountsApiException;
use App\Plugins\Zoho\Models\ZohoOAuthClient;
use App\Plugins\Zoho\Models\ZohoOAuthToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\DBTestCase;

class ZohoAccessTokenTest extends DBTestCase
{
    use DatabaseTransactions;

    private ZohoAccessToken $accessToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accessToken = new ZohoAccessToken();
    }


    public function test_it_returns_valid_access_token()
    {
        $client = ZohoOAuthClient::create([
            'integration_id' => 1,
            'region' => 'us',
        ]);

        $token = ZohoOAuthToken::create([
            'integration_id' => 1,
            'access_token' => 'valid_token',
            'refresh_token' => 'refresh_token',
            'expires_at' => now()->addHour(),
        ]);

        $result = $this->accessToken->get(1);

        $this->assertEquals('valid_token', $result);
    }


    public function test_it_refreshes_expired_access_token()
    {
        $client = ZohoOAuthClient::create([
            'integration_id' => 1,
            'client_id' => 'test_client',
            'client_secret' => 'test_secret',
            'region' => 'us',
        ]);

        $token = ZohoOAuthToken::create([
            'integration_id' => 1,
            'access_token' => 'expired_token',
            'refresh_token' => 'refresh_token',
            'expires_at' => now()->subHour(),
        ]);

        Http::fake([
            '*' => Http::response([
                'access_token' => 'new_access_token',
                'expires_in' => 3600,
            ], 200),
        ]);

        $result = $this->accessToken->get(1);

        $this->assertEquals('new_access_token', $result);
    }


    public function test_it_returns_empty_string_when_no_token_exists()
    {
        $result = $this->accessToken->get(999);

        $this->assertEquals('', $result);
    }


    public function test_it_refreshes_token_about_to_expire()
    {
        $client = ZohoOAuthClient::create([
            'integration_id' => 1,
            'client_id' => 'test_client',
            'client_secret' => 'test_secret',
            'region' => 'us',
        ]);

        $token = ZohoOAuthToken::create([
            'integration_id' => 1,
            'access_token' => 'expiring_soon_token',
            'refresh_token' => 'refresh_token',
            'expires_at' => now()->addSeconds(30),
        ]);

        Http::fake([
            '*' => Http::response([
                'access_token' => 'refreshed_token',
                'expires_in' => 3600,
            ], 200),
        ]);

        $result = $this->accessToken->get(1);

        $this->assertEquals('refreshed_token', $result);
    }

    public function test_it_caches_access_token_for_subsequent_calls()
    {
        $client = ZohoOAuthClient::create([
            'integration_id' => 1,
            'region' => 'us',
        ]);

        $token = ZohoOAuthToken::create([
            'integration_id' => 1,
            'access_token' => 'cached_token',
            'expires_at' => now()->addHours(2),
        ]);

        $result1 = $this->accessToken->get(1);
        $result2 = $this->accessToken->get(1);

        $this->assertEquals('cached_token', $result1);
        $this->assertEquals('cached_token', $result2);
    }



    public function test_it_handles_failed_token_refresh()
    {
        $client = ZohoOAuthClient::create([
            'integration_id' => 1,
            'client_id' => 'test_client',
            'client_secret' => 'test_secret',
            'region' => 'us',
        ]);

        $token = ZohoOAuthToken::create([
            'integration_id' => 1,
            'access_token' => 'expired_token',
            'refresh_token' => 'invalid_refresh',
            'expires_at' => now()->subHour(),
        ]);

        Http::fake([
            '*' => Http::response([
                'error' => 'invalid_token',
            ]),
        ]);

        $this->expectException(ZohoAccountsApiException::class);

        $this->accessToken->get(1);
    }


    public function test_it_uses_correct_client_credentials_when_refreshing()
    {
        $client = ZohoOAuthClient::create([
            'integration_id' => 1,
            'client_id' => 'specific_client_id',
            'client_secret' => 'specific_client_secret',
            'region' => 'eu',
        ]);

        $token = ZohoOAuthToken::create([
            'integration_id' => 1,
            'access_token' => 'expired',
            'refresh_token' => 'refresh',
            'expires_at' => now()->subHour(),
        ]);

        Http::fake([
            '*' => Http::response([
                'access_token' => 'new_token',
                'expires_in' => 3600,
            ], 200),
        ]);

        $this->accessToken->get(1);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'accounts.zoho.eu') &&
                   str_contains($request->url(), 'client_id=specific_client_id') &&
                   str_contains($request->url(), 'client_secret=specific_client_secret');
        });
    }

    public function test_it_saves_new_access_token_after_refresh()
    {
        $client = ZohoOAuthClient::create([
            'integration_id' => 1,
            'client_id' => 'test_client',
            'client_secret' => 'test_secret',
            'region' => 'us',
        ]);

        $token = ZohoOAuthToken::create([
            'integration_id' => 1,
            'access_token' => 'old_token',
            'refresh_token' => 'refresh_token',
            'expires_at' => now()->subHour(),
        ]);

        Http::fake([
            '*' => Http::response([
                'access_token' => 'brand_new_token',
                'expires_in' => 7200,
            ], 200),
        ]);

        $this->accessToken->get(1);

        $this->assertDatabaseHas('zoho_oauth_tokens', [
            'integration_id' => 1,
            'access_token' => 'brand_new_token',
        ]);
    }

    public function test_it_handles_multiple_integrations_independently()
    {
        ZohoOAuthClient::create([
            'integration_id' => 1,
            'region' => 'us',
        ]);
        ZohoOAuthClient::create([
            'integration_id' => 2,
            'region' => 'eu',
        ]);

        ZohoOAuthToken::create([
            'integration_id' => 1,
            'access_token' => 'token_1',
            'expires_at' => now()->addHour(),
        ]);
        ZohoOAuthToken::create([
            'integration_id' => 2,
            'access_token' => 'token_2',
            'expires_at' => now()->addHour(),
        ]);

        $result1 = $this->accessToken->get(1);
        $result2 = $this->accessToken->get(2);

        $this->assertEquals('token_1', $result1);
        $this->assertEquals('token_2', $result2);
    }
}
