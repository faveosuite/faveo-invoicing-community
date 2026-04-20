<?php

namespace Tests\Unit\Common;

use App\ApiKey;
use App\Http\Controllers\Common\SettingsController;
use App\Model\Common\CommonSettings;
use App\Model\Common\EmailMobileValidationProviders;
use App\Model\Common\StatusSetting;
use App\Model\Payment\Plan;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
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

    public function test_free_trial_status_updating()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $status = 1;
        $product = Product::factory()->create([]);
        $plan = Plan::factory()->create();
        $cloud = CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id, 'cloud_product_key' => 12345]);
        $response = $this->post('update-trial-status', ['id' => $cloud->id, 'status' => $status]);
        $content = $response->json();
        $this->assertEquals(true, $content['success']);
        $cloud1 = CloudProducts::where('id', $cloud->id)->first();
        $this->assertEquals(1, $cloud1->trial_status);
    }

    public function test_free_product_receiving()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $status = 1;
        $product = Product::factory()->create(['name' => 'good']);
        $plan = Plan::factory()->create();
        $cloud = CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id, 'cloud_product_key' => 12345, 'trial_status' => $status]);
        $response = $this->post('trial-cloud-products');
        $content = $response->getContent();
        $this->assertEquals('{"success":true,"message":"Products","data":{"12345":"good"}}', $content);
    }

    // ── Sentry / Debugging Settings ───────────────────────────────────────────

    public function test_debug_settings_view_loads_successfully()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();

        $response = $this->get('debugg');

        $response->assertStatus(200);
        $response->assertViewIs('themes.default1.common.setting.debugging');
    }

    public function test_post_debug_settings_enables_sentry_crash_reporting()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        \Cache::forget('debugging_settings');

        $response = $this->post('save/debugg', [
            'debug' => 'false',
            'pulse_enabled' => 'false',
            'clockwork_enable' => 'false',
            'sentry_reporting' => 'true',
            'sentry_performance' => 'false',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('common_settings', [
            'option_name' => 'sentry',
            'optional_field' => 'crash_reporting',
            'option_value' => '1',
        ]);
    }

    public function test_post_debug_settings_disables_sentry_crash_reporting()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        \Cache::forget('debugging_settings');

        $response = $this->post('save/debugg', [
            'debug' => 'false',
            'pulse_enabled' => 'false',
            'clockwork_enable' => 'false',
            'sentry_reporting' => 'false',
            'sentry_performance' => 'false',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('common_settings', [
            'option_name' => 'sentry',
            'optional_field' => 'crash_reporting',
            'option_value' => '0',
        ]);
    }

    public function test_post_debug_settings_enables_sentry_performance_monitoring()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        \Cache::forget('debugging_settings');

        $response = $this->post('save/debugg', [
            'debug' => 'false',
            'pulse_enabled' => 'false',
            'clockwork_enable' => 'false',
            'sentry_reporting' => 'false',
            'sentry_performance' => 'true',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('common_settings', [
            'option_name' => 'sentry',
            'optional_field' => 'performance_monitoring',
            'option_value' => '1',
        ]);
    }

    public function test_post_debug_settings_disables_sentry_performance_monitoring()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        \Cache::forget('debugging_settings');

        $response = $this->post('save/debugg', [
            'debug' => 'false',
            'pulse_enabled' => 'false',
            'clockwork_enable' => 'false',
            'sentry_reporting' => 'false',
            'sentry_performance' => 'false',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('common_settings', [
            'option_name' => 'sentry',
            'optional_field' => 'performance_monitoring',
            'option_value' => '0',
        ]);
    }

    public function test_post_debug_settings_enables_both_sentry_options()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        \Cache::forget('debugging_settings');

        $response = $this->post('save/debugg', [
            'debug' => 'false',
            'pulse_enabled' => 'false',
            'clockwork_enable' => 'false',
            'sentry_reporting' => 'true',
            'sentry_performance' => 'true',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('common_settings', [
            'option_name' => 'sentry',
            'optional_field' => 'crash_reporting',
            'option_value' => '1',
        ]);

        $this->assertDatabaseHas('common_settings', [
            'option_name' => 'sentry',
            'optional_field' => 'performance_monitoring',
            'option_value' => '1',
        ]);
    }

    public function test_post_debug_settings_clears_debugging_cache()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();

        \Cache::put('debugging_settings', ['app.debug' => true], 60);

        $this->post('save/debugg', [
            'debug' => 'false',
            'pulse_enabled' => 'false',
            'clockwork_enable' => 'false',
            'sentry_reporting' => 'false',
            'sentry_performance' => 'false',
        ]);

        $this->assertNull(\Cache::get('debugging_settings'));
    }

    public function test_post_debug_settings_saves_all_options_together()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        \Cache::forget('debugging_settings');

        $response = $this->post('save/debugg', [
            'debug' => 'true',
            'pulse_enabled' => 'true',
            'clockwork_enable' => 'true',
            'sentry_reporting' => 'true',
            'sentry_performance' => 'true',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        foreach ([
            ['debugging', 'app_debug',             '1'],
            ['debugging', 'pulse_enabled',         '1'],
            ['debugging', 'clockwork_enable',      '1'],
            ['sentry',    'crash_reporting',        '1'],
            ['sentry',    'performance_monitoring', '1'],
        ] as [$name, $field, $value]) {
            $this->assertDatabaseHas('common_settings', [
                'option_name' => $name,
                'optional_field' => $field,
                'option_value' => $value,
            ]);
        }
    }

    public function test_debug_settings_view_reflects_saved_sentry_state()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        \Cache::forget('debugging_settings');

        CommonSettings::updateOrCreate(
            ['option_name' => 'sentry', 'optional_field' => 'crash_reporting'],
            ['option_value' => '1', 'status' => '1']
        );
        CommonSettings::updateOrCreate(
            ['option_name' => 'sentry', 'optional_field' => 'performance_monitoring'],
            ['option_value' => '1', 'status' => '1']
        );

        $response = $this->get('debugg');

        $response->assertStatus(200);
        $response->assertSee('sentry_reporting');
        $response->assertSee('sentry_performance');
    }
}
