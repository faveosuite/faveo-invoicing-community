<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\ApiKey;
use App\Http\Controllers\Common\SettingsController;
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
        $this->classObject = new SettingsController;
        $this->request = resolve(Request::class);
        $this->html = Mockery::mock(Html::class, [$this->request])->makePartial();
        $this->html->shouldReceive('token')->andReturn('mocked-token');
        $this->app->instance(Html::class, $this->html);
    }

    public function test_validation_when_company_not_given(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->patchJson('settings/system-data', [
            'company' => '',
            'company_email' => 'demo@gmail.com',
            'website' => 'https://lws.com',
            'phone' => '9789909887',
            'address' => 'bangalore',
            'state' => 'karnataka',
            'default_currency' => 'USD',
            'country' => 'IN',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['company']);
    }

    public function test_returns_mobile_verification_details(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        ApiKey::factory()->create([
            'msg91_auth_key' => 'dummy_auth_key',
            'msg91_sender' => 'dummy_sender',
            'msg91_template_id' => 'dummy_template',
        ]);
        $response = $this->getJson('/settings/msg91');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_returns_terms_url_from_apikeys(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        ApiKey::factory()->create(['terms_url' => 'https://example.com/terms']);
        $response = $this->getJson('/settings/terms');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_returns_pipedrive_api_key(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        ApiKey::factory()->create(['pipedrive_api_key' => 'fake-pipedrive-key-123']);
        $response = $this->getJson('/settings/pipedrive');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_get_email_data(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->getJson('settings/email-validation');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_mobile_data(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->getJson('settings/mobile-validation');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_when_api_key_is_wrong(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        \Illuminate\Support\Facades\Http::fake([
            'emailverifier.reoon.com/*' => \Illuminate\Support\Facades\Http::response(['status' => 'error'], 200),
        ]);
        $response = $this->postJson('email-settings-save', ['apikey' => 'dummy_api_key']);
        $response->assertJson(['success' => false]);
    }

    public function test_post_contact_option_successfully_updates_settings(): void
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

    public function test_free_trial_status_updating(): void
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
        $this->assertEquals(expected: true, actual: $content['success']);
        $cloud1 = CloudProducts::where('id', $cloud->id)->first();
        $this->assertEquals(1, $cloud1->trial_status);
    }

    public function test_free_product_receiving(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::factory()->create(['name' => 'good', 'hidden' => 0]);
        $plan = Plan::factory()->create();
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id, 'cloud_product_key' => 12345, 'trial_status' => 1]);
        $response = $this->getJson('store/cloud-products');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['data' => ['products']]);
    }
}
