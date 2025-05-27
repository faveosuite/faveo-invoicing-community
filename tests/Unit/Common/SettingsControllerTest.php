<?php

namespace Tests\Unit\Common;

use App\ApiKey;
use App\Http\Controllers\Common\SettingsController;
use App\Model\Common\EmailMobileValidationProviders;
use App\Model\Common\StatusSetting;
use App\User;
use Illuminate\Http\Request;
use Mockery;
use Spatie\Html\Html;
use Tests\DBTestCase;

class SettingsControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->classObject = new SettingsController();
        $this->request = app(Request::class);
        $this->html = Mockery::mock(Html::class, [$this->request])->makePartial();
        $this->html->shouldReceive('token')->andReturn('mocked-token');
        $this->app->instance(Html::class, $this->html);
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

    public function test_get_email_data()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        EmailMobileValidationProviders::where('provider', 'reoon')->update(['to_use' => 1, 'api_key' => 'dummy_api_key', 'mode' => 'quick']);
        $response = $this->call('post', 'emailData', ['value' => 'reoon']);
        $response->assertStatus(200);
        $content = $response->original;
        $this->assertEquals(true, $content['success']);
    }

    public function test_get_mobile_data()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        EmailMobileValidationProviders::where('provider', 'vonage')->update(['to_use' => 1, 'api_key' => 'dummy_api_key',
            'mode' => 'standard', 'api_secret' => 'dummy_api_secret']);
        $response = $this->call('post', 'mobileData', ['value' => 'vonage']);
        $response->assertStatus(200);
        $content = $response->original;
        $this->assertEquals(true, $content['success']);
    }

    public function test_when_api_key_is_wrong()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->call('post', 'email-settings-save', ['apikey' => 'dummy_api_key']);
        $content = $response->original;
        $this->assertEquals(false, $content['success']);
        $this->assertEquals('Please enter a valid Reoon Api key.', $content['message']);
    }
    public function test_post_contact_option_successfully_updates_settings()
    {
        $this->getLoggedInUser('admin');
        $this->withoutMiddleware();
        $statusSetting = StatusSetting::first();
        $apiKey = ApiKey::first();

        $payload = [
            'email_enabled' => 1,
            'mobile_enabled' => 0,
            'preferred_verification' => 'email',
        ];

        // Act
        $response = $this->postJson('verificationSettings', $payload);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __('message.contact_setting_update'),
            ]);

        $this->assertDatabaseHas('status_settings', [
            'id' => $statusSetting->id,
            'emailverification_status' => 1,
            'msg91_status' => 0,
        ]);

        $this->assertDatabaseHas('api_keys', [
            'id' => $apiKey->id,
            'verification_preference' => 'email',
        ]);
    }
}
