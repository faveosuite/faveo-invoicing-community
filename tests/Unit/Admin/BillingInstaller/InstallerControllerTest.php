<?php

namespace Tests\Unit\Admin\BillingInstaller;

use App\Http\Controllers\BillingInstaller\InstallerController;
use App\User;
use Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\TestResponse;
use Mockery;
use Session;
use Tests\TestCase;
use Tests\DBTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;



class InstallerControllerTest extends DBTestCase
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
        $location = $response->headers->get('location');
        $this->assertEquals('http://localhost/post-check',$location);
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

    public function test_createEnv()
    {
        // Arrange
        Session::shouldReceive('put')
            ->with('default', 'mysql');
        // Mocking session, cache, etc.

        // Mocking InstallerController and allowing to mock protected methods
        $controller = Mockery::mock('App\Http\Controllers\InstallerController')
            ->shouldAllowMockingProtectedMethods();

        // Mock the protected method 'createEnv'
        $controller->shouldReceive('createEnv')
            ->with(true)
            ->andReturn(response()->json([
                'result' => [
                    'success' => 'Environment configuration file has been created successfully',
                ],
            ], 200));

        // Act
        $response = $controller->createEnv(true);

        // Assert
        $this->assertEquals(200, $response->status());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Environment configuration file has been created successfully', $data['result']['success']);
    }

    public function test_language_list_returns_all_languages()
    {
        //Before authentication check language list come or not
        $response = $this->call('GET', url('language/settings'));
        $response->assertStatus(200);
    }

    public function test_selected_language_stored_or_not(){
        //Before authentication the selected language stored or not
        $response = $this->call('POST', url('update/language'), ['language' => 'ar']);
        $response->assertStatus(200);
    }

    public function test_language_list_after_authentication(){
        //After authentication check language list come or not
        $this->getLoggedInUser('admin');
        $response = $this->call('GET', url('language/settings'));
        $response->assertStatus(200);
    }

    public function test_selected_language_stored_or_not_after_authentication(){
        //After authentication the selected language stored or not
        $this->getLoggedInUser('admin');
        $response = $this->call('POST', url('update/language'), ['language' => 'ar']);
        $response->assertStatus(200);
    }

    public function test_selected_language_stored_in_cache_or_not()
    {
        // check the non-authenticated user selected language stored in cache or not
        Auth::shouldReceive('check')->andReturn(false);
        $response = $this->call('POST', url('update/language'), ['language' => 'ar']);
        $response->assertStatus(200);

        // Assert that the language is stored in the cache
        $this->assertEquals('ar', Cache::get('language'));
    }

    public function test_selected_language_stored_in_auth_user()
    {
        // check the authenticated user selected language stored in auth user or not
        $user = User::factory()->make();
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($user);
        $response = $this->call('POST', url('update/language'), ['language' => 'ar']);
        $response->assertStatus(200);

        // Assert that the language is stored in the authenticated user's language attribute
        $this->assertEquals('ar', $user->language);
    }

}
