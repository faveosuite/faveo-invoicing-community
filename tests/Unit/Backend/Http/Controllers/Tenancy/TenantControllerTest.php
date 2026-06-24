<?php

namespace Tests\Unit\Backend\Http\Controllers\Tenancy;

use App\Http\Controllers\Tenancy\TenantController;
use App\Model\Common\FaveoCloud;
use App\ThirdPartyApp;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class TenantControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function test_get_tenants_success(): void
    {
        ThirdPartyApp::create([
            'app_name' => 'faveo_app_key',
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
        ]);

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'message' => [
                    [
                        'id' => 1,
                        'domain' => 'test.example.com',
                        'database_name' => 'test_db',
                        'database_user_name' => 'test_user',
                        'mobile' => '1234567890',
                        'country' => 'US',
                    ],
                    [
                        'id' => 2,
                        'domain' => 'test2.example.com',
                        'database_name' => 'test_db2',
                        'database_user_name' => 'test_user2',
                        'mobile' => '1234567890',
                        'country' => 'US',
                    ],
                ],
            ])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $cloud = FaveoCloud::create([
            'cloud_central_domain' => 'https://cloud.example.com',
            'cloud_cname' => 'test.example.com',
        ]);

        $controller = new TenantController($client, $cloud);
        $request = new Request;
        $response = $controller->getTenants($request);

        $responseData = json_decode((string) $response->getContent(), associative: true);

        // Response: {success, message, data: {current_page, data: [...], ...}}
        $this->assertTrue($responseData['success']);
        $this->assertCount(2, $responseData['data']['data']);
        $this->assertEquals('test_db', $responseData['data']['data'][0]['database']['name']);
        $this->assertEquals('test_user', $responseData['data']['data'][0]['database']['username']);
    }

    public function test_get_tenants_invalid_app_key(): void
    {
        ThirdPartyApp::create([
            'app_name' => 'faveo_app_key',
            'app_key' => null,
            'app_secret' => 'test_secret',
        ]);

        $mock = new MockHandler([]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $cloud = FaveoCloud::create([
            'cloud_central_domain' => 'https://cloud.example.com',
        ]);

        $controller = new TenantController($client, $cloud);
        $request = new Request;
        $response = $controller->getTenants($request);

        $body = json_decode((string) $response->getContent(), associative: true);
        $this->assertFalse($body['success']);
        $this->assertNotEmpty($body['message']);
    }

    // =========================================================================
    // POST /enable/cloud — enableCloud
    // =========================================================================

    public function test_enable_cloud_when_status_setting_exists_returns_200(): void
    {
        \App\Model\Common\StatusSetting::create([
            'emailverification_status' => 0,
            'msg91_status'             => 0,
            'recaptcha_status'         => 0,
        ]);
        $mock         = new MockHandler([new Response(200, [], json_encode(['success' => true]))]);
        $handlerStack = HandlerStack::create($mock);
        $client       = new Client(['handler' => $handlerStack]);
        $cloud        = FaveoCloud::create(['cloud_central_domain' => 'https://cloud.example.com', 'cloud_cname' => 'test.example.com']);
        $controller   = new TenantController($client, $cloud);
        $request      = new Request(['debug' => 'true']);
        $response     = $controller->enableCloud($request);
        $data         = json_decode((string) $response->getContent(), true);
        $this->assertTrue($data['success']);
    }

    // =========================================================================
    // POST /cloud-pop-up — cloudPopUp (direct controller call)
    // =========================================================================

    public function test_cloud_popup_missing_fields_throws_validation_exception(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $mock         = new MockHandler([]);
        $client       = new Client(['handler' => HandlerStack::create($mock)]);
        $cloud        = FaveoCloud::create(['cloud_central_domain' => 'https://cloud.example.com', 'cloud_cname' => 'test.example.com']);
        $controller   = new TenantController($client, $cloud);
        $request      = new Request([]);
        $controller->cloudPopUp($request);
    }

    public function test_cloud_popup_with_valid_data_returns_200(): void
    {
        $mock         = new MockHandler([]);
        $client       = new Client(['handler' => HandlerStack::create($mock)]);
        $cloud        = FaveoCloud::create(['cloud_central_domain' => 'https://cloud.example.com', 'cloud_cname' => 'test.example.com']);
        $controller   = new TenantController($client, $cloud);
        $request      = new Request([
            'cloud_top_message' => 'Try our cloud!',
            'cloud_label_field' => 'Domain',
            'cloud_label_radio' => 'Region',
        ]);
        $response = $controller->cloudPopUp($request);
        $data = json_decode((string) $response->getContent(), true);
        $this->assertTrue($data['success']);
    }

    // =========================================================================
    // POST /cloud-product-store — cloudProductStore (direct controller call)
    // =========================================================================

    public function test_cloud_product_store_missing_fields_throws_validation_exception(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $mock       = new MockHandler([]);
        $client     = new Client(['handler' => HandlerStack::create($mock)]);
        $cloud      = FaveoCloud::create(['cloud_central_domain' => 'https://cloud.example.com', 'cloud_cname' => 'test.example.com']);
        $controller = new TenantController($client, $cloud);
        $request    = new Request([]);
        $controller->cloudProductStore($request);
    }

    public function test_cloud_product_store_with_valid_data_returns_200(): void
    {
        $mock       = new MockHandler([]);
        $client     = new Client(['handler' => HandlerStack::create($mock)]);
        $cloud      = FaveoCloud::create(['cloud_central_domain' => 'https://cloud.example.com', 'cloud_cname' => 'test.example.com']);
        $controller = new TenantController($client, $cloud);
        $product    = \App\Model\Product\Product::factory()->create();
        $plan       = \App\Model\Payment\Plan::factory()->create(['product' => $product->id]);
        $request    = new Request([
            'cloud_product'     => $product->id,
            'cloud_free_plan'   => $plan->id,
            'cloud_product_key' => 'HELPDESK_KEY',
        ]);
        $response = $controller->cloudProductStore($request);
        $data     = json_decode((string) $response->getContent(), true);
        $this->assertTrue($data['success']);
    }

    public function test_get_tenants_guzzle_exception(): void
    {
        ThirdPartyApp::create([
            'app_name' => 'faveo_app_key',
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
        ]);

        $mock = new MockHandler([
            new ConnectException('Connection error', new \GuzzleHttp\Psr7\Request('GET', 'test')),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $cloud = FaveoCloud::create([
            'cloud_central_domain' => 'https://cloud.example.com',
        ]);

        $controller = new TenantController($client, $cloud);
        $request = new Request;
        $response = $controller->getTenants($request);

        $body = json_decode((string) $response->getContent(), associative: true);
        $this->assertFalse($body['success']);
        $this->assertNotEmpty($body['message']);
    }
}
