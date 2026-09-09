<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\ThirdPartyApp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ThirdPartyAppControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getLoggedInUser('admin');

        $this->withoutMiddleware();
    }

    public function test_fetches_third_party_apps_with_masked_secret(): void
    {
        $app = ThirdPartyApp::create([
            'app_name' => 'MailApp',
            'app_key' => str_repeat('A', 32),
            'app_secret' => 'real-secret',
        ]);

        $response = $this->getJson('/get-third-party-app');

        $response->assertStatus(200)
            ->assertJsonFragment(['app_secret' => '*****'])
            ->assertJsonFragment(['app_name' => $app->app_name]);
    }

    public function test_applies_search_filter(): void
    {
        ThirdPartyApp::create([
            'app_name' => 'MailGun',
            'app_key' => str_repeat('A', 32),
            'app_secret' => 'xxx',
        ]);

        ThirdPartyApp::create([
            'app_name' => 'StripeAPI',
            'app_key' => str_repeat('B', 32),
            'app_secret' => 'yyy',
        ]);

        $response = $this->getJson('/get-third-party-app?search-query=Mail');

        $response->assertStatus(200)
            ->assertJsonFragment(['app_name' => 'MailGun'])
            ->assertJsonMissing(['app_name' => 'StripeAPI']);
    }

    public function test_creates_third_party_app_successfully(): void
    {
        $payload = [
            'app_name' => 'TestApp',
            'app_key' => str_repeat('C', 32),
            'app_secret' => 'secret123',
        ];

        $response = $this->postJson('/third-party-app-create', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.saved-successfully')]);

        $this->assertDatabaseHas('third_party_apps', [
            'app_name' => 'TestApp',
        ]);
    }

    public function test_fails_validation_when_creating_invalid_request(): void
    {
        $payload = [
            'app_name' => '',
            'app_key' => '123',
            'app_secret' => '',
        ];

        $response = $this->postJson('/third-party-app-create', $payload);

        $response->assertStatus(412)
            ->assertJsonValidationErrors(['app_name', 'app_key', 'app_secret'], 'message');
    }

    public function test_updates_third_party_app_successfully(): void
    {
        $app = ThirdPartyApp::create([
            'app_name' => 'OldApp',
            'app_key' => str_repeat('X', 32),
            'app_secret' => 'old-sec',
        ]);

        $payload = [
            'app_name' => 'UpdatedApp',
            'app_key' => str_repeat('Y', 32),
            'app_secret' => 'new-secret',
        ];

        $response = $this->putJson('/third-party-app-update/'.$app->id, $payload);
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.updated-successfully')]);

        $this->assertDatabaseHas('third_party_apps', [
            'id' => $app->id,
            'app_name' => 'UpdatedApp',
        ]);
    }

    public function test_returns_error_when_deleting_with_empty_ids(): void
    {
        $response = $this->deleteJson('/third-party-delete', ['select' => []]);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => __('message.select-a-row')]);
    }

    public function test_deletes_selected_third_party_apps(): void
    {
        $app1 = ThirdPartyApp::create([
            'app_name' => 'App1',
            'app_key' => str_repeat('M', 32),
            'app_secret' => 'sec1',
        ]);

        $app2 = ThirdPartyApp::create([
            'app_name' => 'App2',
            'app_key' => str_repeat('N', 32),
            'app_secret' => 'sec2',
        ]);

        $payload = ['select' => [$app1->id, $app2->id]];

        $response = $this->deleteJson('/third-party-delete', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.deleted-successfully')]);

        $this->assertDatabaseMissing('third_party_apps', ['id' => $app1->id]);
        $this->assertDatabaseMissing('third_party_apps', ['id' => $app2->id]);
    }

    public function test_returns_error_when_deleting_non_existing_app(): void
    {
        $response = $this->deleteJson('/third-party-delete', ['select' => [999]]);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => __('message.no-record')]);
    }
}
