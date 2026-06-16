<?php

namespace App\Plugins\Zoho\Tests\Controllers\Api;

use App\Plugins\Zoho\Controllers\Api\ZohoAccountsApi;
use App\Plugins\Zoho\Controllers\Api\ZohoRegion;
use App\Plugins\Zoho\Controllers\Exceptions\ZohoAccountsApiException;
use Illuminate\Support\Facades\Http;
use Tests\DBTestCase;
use Throwable;

class ZohoAccountsApiTest extends DBTestCase
{
    private ZohoAccountsApi $api;

    protected function setUp(): void
    {
        parent::setUp();

        $this->api = new ZohoAccountsApi(
            'test_client_id',
            'test_client_secret',
            ZohoRegion::UnitedStates
        );
    }

    public function test_it_generates_access_token_successfully()
    {
        Http::fake([
            '*' => Http::response([
                'access_token' => 'test_access_token',
                'refresh_token' => 'test_refresh_token',
                'api_domain' => 'https://www.zohoapis.com',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ], 200),
        ]);

        $result = $this->api->generateAccessToken('auth_code_123');

        $this->assertEquals('test_access_token', $result['access_token']);
        $this->assertEquals('test_refresh_token', $result['refresh_token']);
        $this->assertEquals(3600, $result['expires_in']);
    }

    public function test_it_throws_exception_for_invalid_client()
    {
        $this->expectException(ZohoAccountsApiException::class);

        Http::fake([
            '*' => Http::response([
                'error' => 'invalid_client',
            ]),
        ]);

        $this->api->generateAccessToken('auth_code_123');
    }

    public function test_it_throws_exception_for_invalid_client_secret()
    {
        $this->expectException(ZohoAccountsApiException::class);

        Http::fake([
            '*' => Http::response([
                'error' => 'invalid_client_secret',
            ]),
        ]);

        $this->api->generateAccessToken('auth_code_123');
    }

    public function test_it_throws_exception_for_invalid_code()
    {
        $this->expectException(ZohoAccountsApiException::class);

        Http::fake([
            '*' => Http::response([
                'error' => 'invalid_code',
            ]),
        ]);

        $this->api->generateAccessToken('invalid_code');
    }

    public function test_it_throws_generic_exception_for_unknown_errors()
    {
        $this->expectException(ZohoAccountsApiException::class);

        Http::fake([
            '*' => Http::response([
                'error' => 'unknown_error',
            ]),
        ]);

        $this->api->generateAccessToken('auth_code_123');
    }

    public function test_it_refreshes_access_token_successfully()
    {
        Http::fake([
            '*' => Http::response([
                'access_token' => 'new_access_token',
                'api_domain' => 'https://www.zohoapis.com',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ], 200),
        ]);

        $result = $this->api->refreshAccessToken('refresh_token_123');

        $this->assertEquals('new_access_token', $result['access_token']);
        $this->assertEquals(3600, $result['expires_in']);
        $this->assertArrayNotHasKey('refresh_token', $result);
    }

    public function test_it_sends_correct_parameters_when_generating_token()
    {
        Http::fake();

        try {
            $this->api->generateAccessToken('test_code');
        } catch (Throwable) {
            // Ignore response errors for this test
        }

        Http::assertSent(function ($request) {
            $url = $request->url();

            return str_contains($url, 'client_id=test_client_id') &&
                   str_contains($url, 'client_secret=test_client_secret') &&
                   str_contains($url, 'grant_type=authorization_code') &&
                   str_contains($url, 'code=test_code');
        });
    }

    public function test_it_sends_correct_parameters_when_refreshing_token()
    {
        Http::fake();

        try {
            $this->api->refreshAccessToken('test_refresh_token');
        } catch (Throwable) {
            // Ignore response errors for this test
        }

        Http::assertSent(function ($request) {
            $url = $request->url();

            return str_contains($url, 'client_id=test_client_id') &&
                   str_contains($url, 'client_secret=test_client_secret') &&
                   str_contains($url, 'grant_type=refresh_token') &&
                   str_contains($url, 'refresh_token=test_refresh_token');
        });
    }

    public function test_it_uses_correct_endpoint_for_us_region()
    {
        $api = new ZohoAccountsApi(
            'client_id',
            'client_secret',
            ZohoRegion::UnitedStates
        );

        Http::fake();

        try {
            $api->generateAccessToken('code');
        } catch (Throwable) {
        }

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'accounts.zoho.com/oauth/v2/token'));
    }

    public function test_it_uses_correct_endpoint_for_eu_region()
    {
        $api = new ZohoAccountsApi(
            'client_id',
            'client_secret',
            ZohoRegion::Europe
        );

        Http::fake();

        try {
            $api->generateAccessToken('code');
        } catch (Throwable) {
        }

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'accounts.zoho.eu/oauth/v2/token'));
    }

    public function test_it_uses_correct_endpoint_for_india_region()
    {
        $api = new ZohoAccountsApi(
            'client_id',
            'client_secret',
            ZohoRegion::India
        );

        Http::fake();

        try {
            $api->generateAccessToken('code');
        } catch (Throwable) {
        }

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'accounts.zoho.in/oauth/v2/token'));
    }

    public function test_it_handles_error_in_refresh_token()
    {
        $this->expectException(ZohoAccountsApiException::class);

        Http::fake([
            '*' => Http::response([
                'error' => 'invalid_client',
            ]),
        ]);

        $this->api->refreshAccessToken('invalid_refresh_token');
    }
}
