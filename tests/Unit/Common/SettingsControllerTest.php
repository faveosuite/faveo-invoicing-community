<?php

namespace Tests\Unit\Common;

use App\User;
use Tests\TestCase;
use App\Model\Common\StatusSetting;
use App\ApiKey;

class SettingsControllerTest extends TestCase
{
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
        // Create dummy ApiKey record using factory
        ApiKey::factory()->create();

        // Call the endpoint
        $response = $this->getJson('/licensekeys');

        // Assert 200 OK
        $response->assertStatus(200);

        // Assert JSON structure
        $response->assertJsonStructure([
            'licenseGrantType',
            'licenseSecret',
            'licenseClientId',
            'licenseClientSecret',
            'licenseUrl',
        ]);
    }

    public function test_google_captcha_response()
    {
        // Arrange: Create dummy data
        $apiKey = ApiKey::factory()->create([
            'nocaptcha_sitekey' => 'test-site-key',
            'captcha_secretCheck' => 'test-secret-key',
        ]);

        StatusSetting::factory()->create([
            'recaptcha_status' => '1',
            'v3_recaptcha_status' => '1',
        ]);

        // Act: Make the request
        $response = $this->postJson('googleCaptcha');

        // Assert: Check the expected JSON response
        $response->assertStatus(200)
            ->assertJson([
                'captchaStatus' => '1',
                'v3CaptchaStatus' => '1',
                'siteKey' => 'test-site-key',
                'secretKey' => 'test-secret-key',
            ]);
    }

    public function test_returns_mobile_verification_details()
    {
        // Arrange: create dummy ApiKey record
        $apikey = ApiKey::factory()->create([
            'msg91_auth_key' => 'dummy_auth_key',
            'msg91_sender' => 'dummy_sender',
            'msg91_template_id' => 'dummy_template',
        ]);

        // Act: call the controller method via route
        $response = $this->postJson('mobileVerification');

        // Assert: check if response contains expected keys and values
        $response->assertStatus(200)
            ->assertJson([
                'mobileauthkey' => 'dummy_auth_key',
                'msg91Sender' => 'dummy_sender',
                'msg91TemplateId' => 'dummy_template',
            ]);
    }

    public function test_returns_terms_url_from_apikeys()
    {
        // Arrange: create a dummy ApiKey record
        $apiKey = ApiKey::factory()->create([
            'terms_url' => 'https://example.com/terms',
        ]);

        // Act: call the route or controller method
        $response = $this->postJson('termsUrl'); // adjust this route as per your app

        // Assert: check that the correct terms URL is returned
        $response->assertStatus(200);
        $response->assertJson([
            'termsUrl' => 'https://example.com/terms',
        ]);
    }

    public function test_returns_pipedrive_api_key()
    {
        $apiKey = ApiKey::factory()->create([
            'pipedrive_api_key' => 'fake-pipedrive-key-123',
        ]);

        $response = $this->postJson('pipedrivekeys'); // adjust the route if needed

        $response->assertStatus(200);
        $response->assertJson([
            'pipedriveKey' => 'fake-pipedrive-key-123',
        ]);
    }


    public function test_get_data_table_data_returns_valid_json()
    {
        // Seed dummy data
        StatusSetting::factory()->create([
            'license_status' => 1,
            'msg91_status' => 1,
            'recaptcha_status' => 1,
            'v3_recaptcha_status' => 1,
            'twitter_status' => 0,
            'zoho_status' => 0,
            'pipedrive_status' => 0,
            'github_status' => 1,
            'mailchimp_status' => 1,
            'terms' => 0,
            'v3_v2_recaptcha_status' => 0,
        ]);

        // Hit the route (adjust URI if prefixed)
        $response = $this->postJson('datatable.data');

        // Basic structure check
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['options', 'description', 'status', 'action']
                ]
            ]);
    }
    
}
