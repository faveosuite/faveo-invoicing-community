<?php

namespace Tests\Unit;

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
        $this->class = new WhatsappController();
    }

    public function testWhatsappIndex()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $cont = WhatsappIntegration::create(['app_id' => 'fsfdsf', 'config_id' => 'fsfdsf', 'app_secret' => 'fsfdsf', 'verify_token' => 'fsfdsf']);
        $response = $this->call('GET', 'whatsapp-integration');
        $data = $response->viewData('app_id');
        $this->assertEquals($cont->app_id, $data);
        $response->assertStatus(200);
        $response->assertViewHas('app_id');
    }

    public function testUrlSave()
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

    public function testWhatsappTable()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $order = Order::factory()->create();
        $data = WhatsappIntegrationUser::create(['waba_id' => 'testing',
            'phone_number_id' => 'fsfdsf', 'business_id' => 'fsfdsf',
            'user_id' => $user->id, 'access_token' => 'fsfdsf',
            'user_callback_url' => 'https://api.examplde.com/send/', 'order_id' => $order->id]);
        $response = $this->call('GET', 'whatsapp-users-table');
        $json = $response->decodeResponseJson();
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['waba_id', 'phone_number_id', 'business_id', 'access_token'],
            ],
        ]);

        $this->assertTrue(
            collect($json['data'])->pluck('waba_id')->contains('testing')
        );
    }

    public function testWhatsappClientTable()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $order = Order::factory()->create();
        $data = WhatsappIntegrationUser::create(['waba_id' => 'orderTesting',
            'phone_number_id' => 'fsfdsf', 'business_id' => 'fsfdsf',
            'user_id' => $user->id, 'access_token' => 'fsfdsf',
            'user_callback_url' => 'https://api.examplde.com/send/', 'order_id' => $order->id]);
        $response = $this->call('GET', 'whatsapp-client-table/'.$order->id);
        $json = $response->decodeResponseJson();
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['waba_id', 'phone_number_id', 'business_id', 'access_token'],
            ],
        ]);

        $this->assertTrue(
            collect($json['data'])->pluck('waba_id')->contains('orderTesting')
        );
    }

    public function testWhatsappIntegrationInfo()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $cont = WhatsappIntegration::create(['app_id' => 'fsfdsf', 'config_id' => 'fsfdsf', 'app_secret' => 'fsfdsf', 'verify_token' => 'fsfdsf']);
        $response = $this->call('GET', 'whatsapp-integration-info');
        $json = $response->decodeResponseJson();
        $content = $json['data'];
        $response->assertStatus(200);
        $this->assertEquals($cont->app_id, $content['app_id']);
        $this->assertEquals($cont->config_id, $content['config_id']);
        $this->assertEquals($cont->app_secret, $content['app_secret']);
        $this->assertEquals($cont->verify_token, $content['verify_token']);
    }

    public function testWhatsappIntegrationSave()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->call('POST', 'whatsapp-integration-save', ['app_id' => 'fsfdsf', 'config_id' => 'fsfdsf', 'app_secret' => 'fsfdsf', 'verify_token' => 'fsfdsf']);
        $response->assertStatus(200);
        $content = $response->json();
        $this->assertEquals('Updated Successfully', $content['message']);
    }

    public function testGetWhatsappWebhook()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $cont = WhatsappIntegration::create(['app_id' => 'fsfdsf', 'config_id' => 'fsfdsf', 'app_secret' => 'fsfdsf', 'verify_token' => 'fsfdsf']);
        $response = $this->call('GET', 'faveo-whatsapp', ['hub_mode' => 'subscribe', 'hub_verify_token' => $cont->verify_token, 'hub_challenge' => 'fsfdsf']);
        $response->assertStatus(200);
        $this->assertEquals('fsfdsf', $response->getContent());
    }

    public function testGetWebhookFail()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $cont = WhatsappIntegration::create(['app_id' => 'fsfdsf', 'config_id' => 'fsfdsf', 'app_secret' => 'fsfdsf', 'verify_token' => 'fsfdsf']);
        $response = $this->call('GET', 'faveo-whatsapp', ['hub_mode' => 'subscribe', 'hub_verify_token' => 'sdfewef', 'hub_challenge' => 'fsfdsf']);
        $response->assertStatus(403);
        $this->assertEquals('Forbidden', $response->getContent());
    }

//    public function testPostWhatsappWebhook()
//    {
//        $payload = [
//            "object" => "whatsapp_business_account",
//            "entry" => [
//                [
//                    "id" => "102290129340398",
//                    "changes" => [
//                        [
//                            "value" => [
//                                "messaging_product" => "whatsapp",
//                                "metadata" => [
//                                    "display_phone_number" => "917013925435",
//                                    "phone_number_id" => "106540352242922"
//                                ],
//                                "contacts" => [
//                                    [
//                                        "profile" => [
//                                            "name" => "Sheena Nelson"
//                                        ],
//                                        "wa_id" => "16505551234"
//                                    ]
//                                ],
//                                "messages" => [
//                                    [
//                                        "from" => "16505551234",
//                                        "id" => "wamid.HBgLMTY1MDM4Nzk0MzkVAgASGBQzQTRBNjU5OUFFRTAzODEwMTQ0RgA=",
//                                        "timestamp" => "1749416383",
//                                        "type" => "text",
//                                        "text" => [
//                                            "body" => "Does it come in another color?"
//                                        ]
//                                    ]
//                                ]
//                            ],
//                            "field" => "messages"
//                        ]
//                    ]
//                ]
//            ]
//        ];
//
//
//        $user = User::factory()->create();
//        $this->actingAs($user);
//        $this->withoutMiddleware();
//        $response = $this->call(
//            'POST',
//            'faveo-whatsapp',
//            [],
//            [],
//            [],
//            ['CONTENT_TYPE' => 'application/json'],
//            json_encode($payload)
//        );
//        dd(FailedWhatsappMessage::all());
    ////        $response->assertStatus(200);
//        $this->assertEquals('EVENT_RECEIVED', $response->getContent());
//    }
}
