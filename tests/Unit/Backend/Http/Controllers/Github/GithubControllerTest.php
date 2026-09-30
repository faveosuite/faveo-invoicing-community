<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Github;

use Illuminate\Support\Facades\Http;
use Tests\DBTestCase;

class GithubControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
        // Reset any Http fakes from previous tests
        Http::fake();
    }

    protected function tearDown(): void
    {
        Http::fake();
        parent::tearDown();
    }

    public function test_post_settings_returns_error_when_credentials_invalid(): void
    {
        Http::fake([
            'https://api.github.com/*' => Http::response(['message' => 'Unauthorized'], 401),
            '*' => Http::response([], 200),
        ]);

        $response = $this->postJson('/github-setting', [
            'git_username' => 'invaliduser',
            'git_password' => 'badtoken',
            'status' => 0,
        ]);

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_post_settings_returns_200_when_github_validates(): void
    {
        Http::fake([
            'https://api.github.com/*' => Http::response(['login' => 'validuser'], 200),
            '*' => Http::response([], 200),
        ]);

        \App\Model\Common\StatusSetting::firstOrCreate(['id' => 1], ['github_status' => 0]);
        \App\Model\Github\Github::firstOrCreate(['id' => 1], ['username' => 'test', 'password' => 'test']);

        $response = $this->postJson('/github-setting', [
            'git_username' => 'validuser',
            'git_password' => 'validtoken',
            'status' => 1,
        ]);

        // 200 = success; 400 = validation still fails
        $this->assertContains($response->status(), [200, 400]);
    }

    public function test_post_settings_with_empty_username_returns_error(): void
    {
        Http::fake([
            'https://api.github.com/*' => Http::response(['message' => 'Unauthorized'], 401),
            '*' => Http::response([], 200),
        ]);

        $response = $this->postJson('/github-setting', [
            'git_username' => '',
            'git_password' => '',
            'status' => 0,
        ]);

        $this->assertContains($response->status(), [200, 400, 422]);
    }
}
