<?php

namespace Tests\Unit\Common;

use App\ApiKey;
use App\Http\Controllers\Common\SettingsController;
use App\Model\Common\StatusSetting;
use App\User;
use Tests\DBTestCase;

class SettingsControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->classObject = new SettingsController();
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_validation_when_company_not_given()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $response = $this->patch('/settings/system', [
            'company' => '',
            'company_email' => 'demo@gmail.com',
            'website' => 'https://lws.com',
            'phone' => '9789909887',
            'address' => 'bangalore',
            'state' => 'karnataka',
            'default_currency' => 'USD',
            'country' => 'IN',
        ]);
        $errors = session('errors');
        $response->assertStatus(302);
    }

    public function test_license_keys_endpoint_returns_expected_keys()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $apiKey = ApiKey::factory()->create();
        $methodResponse = $this->getPrivateMethod($this->classObject, 'licensekeys', [$apiKey]);
        $this->assertNotEmpty($methodResponse->content());
    }

    public function test_google_captcha_response()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $apiKey = ApiKey::factory()->create([
            'nocaptcha_sitekey' => 'test-site-key',
            'captcha_secretCheck' => 'test-secret-key',
        ]);

        StatusSetting::factory()->create([
            'recaptcha_status' => '1',
            'v3_recaptcha_status' => '1',
            'v3_v2_recaptcha_status' => '1',
        ]);

        $methodResponse = $this->getPrivateMethod($this->classObject, 'googleCaptcha', [$apiKey]);
        $this->assertNotEmpty($methodResponse->content());
    }

    public function test_returns_mobile_verification_details()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $apikey = ApiKey::factory()->create([
            'msg91_auth_key' => 'dummy_auth_key',
            'msg91_sender' => 'dummy_sender',
            'msg91_template_id' => 'dummy_template',
        ]);
        $methodResponse = $this->getPrivateMethod($this->classObject, 'mobileVerification', [$apikey]);
        $this->assertNotEmpty($methodResponse->content());
    }

    public function test_returns_terms_url_from_apikeys()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $apiKey = ApiKey::factory()->create([
            'terms_url' => 'https://example.com/terms',
        ]);
        $methodResponse = $this->getPrivateMethod($this->classObject, 'termsUrl', [$apiKey]);
        $this->assertNotEmpty($methodResponse->content());
    }

    public function test_returns_pipedrive_api_key()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $apiKey = ApiKey::factory()->create([
            'pipedrive_api_key' => 'fake-pipedrive-key-123',
        ]);
        $methodResponse = $this->getPrivateMethod($this->classObject, 'pipedrivekeys', [$apiKey]);
        $this->assertNotEmpty($methodResponse->content());
    }
}
