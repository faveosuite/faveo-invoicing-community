<?php

namespace App\Plugins\Zoho\Tests\Controllers;

use App\Plugins\Zoho\Controllers\ZohoOAuthController;
use App\Plugins\Zoho\Models\ZohoIntegration;
use App\Plugins\Zoho\Models\ZohoOAuthClient;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Tests\DBTestCase;

class ZohoOAuthControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    private ZohoOAuthController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ZohoOAuthController();
    }

    public function test_it_gets_oauth_client_keys_for_integration(): void
    {
        $integration = ZohoIntegration::first();
        ZohoOAuthClient::create([
            'integration_id' => $integration->id,
            'client_id' => 'test_client_id',
        ]);

        $response = $this->controller->getOauthClientKeys($integration->id);

        $this->assertEquals('true', $response->getData()->success);
        $this->assertEquals('test_client_id', $response->getData()->data->client_id);
    }

    public function test_it_saves_oauth_client_keys_and_returns_redirect_url(): void
    {
        $integration = ZohoIntegration::wherePlatform('crm')->first();

        Config::set('zoho.platforms.crm.scope', ['ZohoCRM.modules.all']);

        $request = new Request([
            'integration_id' => $integration->id,
            'client_id' => 'new_client_id',
            'client_secret' => 'new_client_secret',
            'redirect_uri' => 'https://example.com/callback',
            'region' => 'us',
        ]);

        $response = $this->controller->saveOAuthClientKeys($request);

        $this->assertDatabaseHas('zoho_oauth_clients', [
            'integration_id' => $integration->id,
            'client_id' => 'new_client_id',
            'region' => 'us',
        ]);

        $this->assertEquals('success', $response->getData()->success);
        $this->assertArrayHasKey('redirect_url', (array) $response->getData()->data);
    }

    public function test_it_validates_required_fields_when_saving_oauth_keys(): void
    {
        $this->expectException(ValidationException::class);

        $request = new Request([
            'integration_id' => 999,
        ]);

        $this->controller->saveOAuthClientKeys($request);
    }

    public function test_it_validates_region_must_be_valid(): void
    {
        $this->expectException(ValidationException::class);

        $integration = ZohoIntegration::first();

        $request = new Request([
            'integration_id' => $integration->id,
            'client_id' => 'test',
            'client_secret' => 'test',
            'redirect_uri' => 'https://example.com',
            'region' => 'invalid_region',
        ]);

        $this->controller->saveOAuthClientKeys($request);
    }

    public function test_it_generates_authorization_url_for_platform(): void
    {
        $integration = ZohoIntegration::wherePlatform('crm')->first();
        ZohoOAuthClient::create([
            'integration_id' => $integration->id,
            'client_id' => 'test_client',
            'redirect_uri' => 'https://example.com/callback',
            'region' => 'us',
        ]);

        Config::set('zoho.platforms.crm.scope', ['ZohoCRM.modules.all']);

        $url = $this->controller->getAuthorizationUrlByPlatform('crm');

        $this->assertStringContainsString('accounts.zoho.com', $url);
        $this->assertStringContainsString('oauth/v2/auth', $url);
        $this->assertStringContainsString('test_client', $url);
    }

    public function test_it_throws_exception_when_oauth_client_not_configured(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('OAuth client not configured');

        ZohoIntegration::wherePlatform('crm')->first();

        $this->controller->getAuthorizationUrlByPlatform('crm');
    }

    public function test_it_throws_exception_when_scopes_not_configured(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Scopes not configured for [crm]');

        $integration = ZohoIntegration::wherePlatform('crm')->first();

        ZohoOAuthClient::create([
            'integration_id' => $integration->id,
            'client_id' => 'test_client',
            'client_secret' => 'test_secret',
            'redirect_uri' => 'https://example.com',
            'region' => 'us',
        ]);

        Config::set('zoho.platforms.crm.scope');

        $this->controller->getAuthorizationUrlByPlatform('crm');
    }

    public function test_it_handles_zoho_callback_with_authorization_code(): void
    {
        $integration = ZohoIntegration::wherePlatform('crm')->first();
        ZohoOAuthClient::create([
            'integration_id' => $integration->id,
            'region' => 'us',
        ]);

        Http::fake([
            '*' => Http::response([
                'access_token' => 'test_access_token',
                'refresh_token' => 'test_refresh_token',
                'expires_in' => 3600,
                'api_domain' => 'https://www.zohoapis.com',
            ], 200),
        ]);

        Config::set('zoho.platforms.crm.settings_url', '/settings/crm');

        $request = new Request([
            'code' => 'test_authorization_code',
            'state' => 'crm',
        ]);

        $response = $this->controller->handleZohoCallback($request);

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('zoho_status=success', $response->getTargetUrl());

        $this->assertDatabaseHas('zoho_oauth_tokens', [
            'integration_id' => $integration->id,
            'access_token' => 'test_access_token',
        ]);

        $this->assertDatabaseHas('zoho_integrations', [
            'id' => $integration->id,
            'is_active' => true,
        ]);
    }

    public function test_it_handles_callback_error_when_code_not_present(): void
    {
        $integration = ZohoIntegration::wherePlatform('crm')->first();
        ZohoOAuthClient::create(['integration_id' => $integration->id]);

        Config::set('zoho.platforms.crm.settings_url', '/settings/crm');

        $request = new Request([
            'error' => 'access_denied',
            'state' => 'crm',
        ]);

        $response = $this->controller->handleZohoCallback($request);

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('zoho_status=error', $response->getTargetUrl());
    }

    public function test_it_handles_callback_error_when_token_exchange_fails(): void
    {
        $integration = ZohoIntegration::wherePlatform('crm')->first();
        ZohoOAuthClient::create([
            'integration_id' => $integration->id,
            'region' => 'us',
        ]);

        Http::fake([
            '*' => Http::response([
                'error' => 'invalid_code',
                'error_description' => 'Invalid authorization code',
            ], 400),
        ]);

        Config::set('zoho.platforms.crm.settings_url', '/settings/crm');

        $request = new Request([
            'code' => 'invalid_code',
            'state' => 'crm',
        ]);

        $response = $this->controller->handleZohoCallback($request);

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('zoho_status=error', $response->getTargetUrl());
    }

    public function test_it_generates_correct_accounts_base_url_for_each_region(): void
    {
        $regions = [
            'us' => 'accounts.zoho.com',
            'eu' => 'accounts.zoho.eu',
            'in' => 'accounts.zoho.in',
            'au' => 'accounts.zoho.com.au',
            'jp' => 'accounts.zoho.jp',
            'cn' => 'accounts.zoho.com.cn',
        ];

        foreach ($regions as $region => $expectedDomain) {
            $url = $this->controller->accountsBaseUrl($region);
            $this->assertEquals('https://' . $expectedDomain, $url);
        }
    }

    public function test_it_generates_authorization_url_with_query_params(): void
    {
        $queryParams = [
            'client_id' => 'test_client',
            'response_type' => 'code',
            'scope' => 'ZohoCRM.modules.all',
        ];

        $url = $this->controller->authorizationUrl('us', $queryParams);

        $this->assertStringContainsString('accounts.zoho.com/oauth/v2/auth', $url);
        $this->assertStringContainsString('client_id=test_client', $url);
        $this->assertStringContainsString('response_type=code', $url);
    }

    public function test_it_generates_token_url_for_region(): void
    {
        $url = $this->controller->tokenUrl('eu');

        $this->assertEquals('https://accounts.zoho.eu/oauth/v2/token', $url);
    }
}
