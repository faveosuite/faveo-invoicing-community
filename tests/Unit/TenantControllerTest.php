<?php

namespace Tests\Unit;

use App\Http\Controllers\Tenancy\TenantController;
use App\Model\Common\FaveoCloud;
use App\ThirdPartyApp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class TenantControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function test_get_tenants_success()
    {
        // Mock ThirdPartyApp data
        ThirdPartyApp::create([
            'app_name' => 'faveo_app_key',
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
        ]);

        // Mock Guzzle Client response
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

        // Mock cloud central domain
        $cloud = FaveoCloud::create([
            'cloud_central_domain' => 'https://cloud.example.com',
            'cloud_cname' => 'test.example.com',
        ]);

        // Instantiate controller and call method
        $controller = new TenantController($client, $cloud);
        $request = new Request();
        $response = $controller->getTenants($request);

        // Assert response
        $responseData = json_decode($response->getContent(), true);

        $this->assertCount(2, $responseData['data']);
        $this->assertEquals('test_db', $responseData['data'][0]['db_name']);
        $this->assertEquals('test_user', $responseData['data'][0]['db_username']);
    }

    public function test_get_tenants_invalid_app_key()
    {
        // Mock ThirdPartyApp with invalid app key
        ThirdPartyApp::create([
            'app_name' => 'faveo_app_key',
            'app_key' => null, // Invalid key
            'app_secret' => 'test_secret',
        ]);

        // Mock Guzzle Client (not actually used due to exception)
        $mock = new MockHandler([]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Mock cloud central domain
        $cloud = FaveoCloud::create([
            'cloud_central_domain' => 'https://cloud.example.com',
        ]);

        // Instantiate controller and call method
        $controller = new TenantController($client, $cloud);
        $request = new Request();
        $response = $controller->getTenants($request);

        // Assert redirect and error message
        $this->assertEquals('Invalid App key provided. Please contact admin.', session('fails'));
    }

    public function test_get_tenants_guzzle_exception()
    {
        // Mock ThirdPartyApp data
        ThirdPartyApp::create([
            'app_name' => 'faveo_app_key',
            'app_key' => 'test_key',
            'app_secret' => 'test_secret',
        ]);

        // Mock Guzzle Client to throw exception
        $mock = new MockHandler([
            new ConnectException('Connection error', new \GuzzleHttp\Psr7\Request('GET', 'test')),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Mock cloud central domain
        $cloud = FaveoCloud::create([
            'cloud_central_domain' => 'https://cloud.example.com',
        ]);

        // Instantiate controller and call method
        $controller = new TenantController($client, $cloud);
        $request = new Request();
        $response = $controller->getTenants($request);

        // Assert redirect and error message
        $this->assertEquals('Connection error', session('fails'));

    }
}
