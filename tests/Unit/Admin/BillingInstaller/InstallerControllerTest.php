<?php

namespace Tests\Unit\Admin\BillingInstaller;

use App\Http\Controllers\BillingInstaller\InstallerController;
use Artisan;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Mockery;
use Session;
use Tests\TestCase;

class InstallerControllerTest extends TestCase
{
    public function testConfigurationCheck()
    {
        // This will only test the wrong connection because we don't know the database credential
        $request = Request::create('/configurationcheck', 'POST', [
            'host' => 'localhost',
            'databasename' => 'test_db',
            'username' => 'test_user',
            'password' => 'password',
            'port' => '3306',
        ]);

        $controller = new InstallerController();
        $response = $controller->configurationcheck($request);
        $response = TestResponse::fromBaseResponse($response); // Wrap the base response
        $response->assertStatus(200);
        $this->assertEquals(false, json_decode($response->getContent())->mysqli_ok);
    }

    public function test_checkPreInstall()
    {
        // Mock Artisan call
        Artisan::shouldReceive('call')
            ->once()
            ->with('key:generate', ['--force' => true]);

        $controller = new InstallerController();
        $response = $controller->checkPreInstall();

        $this->assertEquals(200, $response->status());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Pre migration has been tested successfully', $data['result']['success']);
        $this->assertEquals('Migrating tables in database', $data['result']['next']);
    }

//    public function test_createEnv()
//    {
//        // Arrange
//        Session::shouldReceive('put')
//            ->with('default', 'mysql');
//        // Mocking session, cache, etc.
//
//        // Mocking InstallerController and allowing to mock protected methods
//        $controller = Mockery::mock('App\Http\Controllers\InstallerController')
//            ->shouldAllowMockingProtectedMethods();
//
//        // Mock the protected method 'createEnv'
//        $controller->shouldReceive('createEnv')
//            ->with(true)
//            ->andReturn(response()->json([
//                'result' => [
//                    'success' => 'Environment configuration file has been created successfully',
//                ],
//            ], 200));
//
//        // Act
//        $response = $controller->createEnv(true);
//
//        // Assert
//        $this->assertEquals(200, $response->status());
//        $data = json_decode($response->getContent(), true);
//        $this->assertEquals('Environment configuration file has been created successfully', $data['result']['success']);
//    }
}
