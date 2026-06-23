<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\Http\Controllers\WhatsappController;
use App\Model\Order\Order;
use App\User;
use App\WhatsappIntegration;
use App\WhatsappIntegrationUser;
use Tests\DBTestCase;

class WhatsappControllerTest extends DBTestCase
{
    protected $class;

    protected function setUp(): void
    {
        parent::setUp();
        $this->class = new WhatsappController;
    }

    public function test_whatsapp_index(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $cont = WhatsappIntegration::create(['app_id' => 'fsfdsf', 'config_id' => 'fsfdsf', 'app_secret' => 'fsfdsf', 'verify_token' => 'fsfdsf']);
        $response = $this->call('GET', 'whatsapp-integration-info');
        $response->assertStatus(200);
        $data = $response->json()['data'];
        $this->assertEquals($cont->app_id, $data['app_id']);
    }

    public function test_url_save(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $data = 'https://api.examplde.com/send/?text=test';
        $response = $this->post('url-save', [$data]);
        $content = $response->json();
        $response->assertStatus(200);
        $this->assertEquals('success', $content['message']);
    }

    public function test_whatsapp_table(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $order = Order::factory()->create();
        WhatsappIntegrationUser::create(['waba_id' => 'testing',
            'phone_number_id' => 'fsfdsf', 'business_id' => 'fsfdsf',
            'user_id' => $user->id, 'access_token' => 'fsfdsf',
            'user_callback_url' => 'https://api.examplde.com/send/', 'order_id' => $order->id]);
        $response = $this->call('GET', 'whatsapp-users-api');
        $json = $response->json();
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => ['waba_id', 'phone_number_id', 'business_id'],
                ],
            ],
        ]);

        $this->assertTrue(
            collect($json['data']['data'])->pluck('waba_id')->contains('testing')
        );
    }

    public function test_whatsapp_client_table(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $order = Order::factory()->create();
        WhatsappIntegrationUser::create(['waba_id' => 'orderTesting',
            'phone_number_id' => 'fsfdsf', 'business_id' => 'fsfdsf',
            'user_id' => $user->id, 'access_token' => 'fsfdsf',
            'user_callback_url' => 'https://api.examplde.com/send/', 'order_id' => $order->id]);
        $response = $this->call('GET', 'whatsapp-client-numbers/'.$order->id);
        $json = $response->json();
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => ['waba_id', 'phone_number_id', 'business_id'],
                ],
            ],
        ]);

        $this->assertTrue(
            collect($json['data']['data'])->pluck('waba_id')->contains('orderTesting')
        );
    }

    public function test_whatsapp_integration_info(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $cont = WhatsappIntegration::create(['app_id' => 'fsfdsf', 'config_id' => 'fsfdsf', 'app_secret' => 'fsfdsf', 'verify_token' => 'fsfdsf']);
        $response = $this->call('GET', 'whatsapp-integration-info');
        $json = $response->json();
        $content = $json['data'];
        $response->assertStatus(200);
        $this->assertEquals($cont->app_id, $content['app_id']);
        $this->assertEquals($cont->config_id, $content['config_id']);
        $this->assertEquals($cont->app_secret, $content['app_secret']);
        $this->assertEquals($cont->verify_token, $content['verify_token']);
    }

    public function test_whatsapp_integration_save(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->call('POST', 'whatsapp-integration-save', ['app_id' => 'fsfdsf', 'config_id' => 'fsfdsf', 'app_secret' => 'fsfdsf', 'verify_token' => 'fsfdsf']);
        $response->assertStatus(200);

        $content = $response->json();
        $this->assertNotEmpty($content['message']);
    }

    public function test_get_whatsapp_webhook(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $cont = WhatsappIntegration::create(['app_id' => 'fsfdsf', 'config_id' => 'fsfdsf', 'app_secret' => 'fsfdsf', 'verify_token' => 'fsfdsf']);
        $response = $this->call('GET', 'faveo-whatsapp', ['hub_mode' => 'subscribe', 'hub_verify_token' => $cont->verify_token, 'hub_challenge' => 'fsfdsf']);
        $response->assertStatus(200);
        $this->assertEquals('fsfdsf', $response->getContent());
    }

    public function test_get_webhook_fail(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        WhatsappIntegration::create(['app_id' => 'fsfdsf', 'config_id' => 'fsfdsf', 'app_secret' => 'fsfdsf', 'verify_token' => 'fsfdsf']);
        $response = $this->call('GET', 'faveo-whatsapp', ['hub_mode' => 'subscribe', 'hub_verify_token' => 'sdfewef', 'hub_challenge' => 'fsfdsf']);
        $response->assertStatus(403);
        $this->assertEquals('Forbidden', $response->getContent());
    }
}
