<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\ApiKey;
use App\Model\Common\StatusSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\DBTestCase;

class PipedriveSettingsTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getLoggedInUser('admin');
        $this->withoutMiddleware();
        Http::preventStrayRequests();

        // The settings code reads/writes row 1.
        ApiKey::query()->updateOrInsert(['id' => 1], ['pipedrive_api_key' => 'secret-pipedrive-key-ab12']);
        StatusSetting::query()->update(['pipedrive_status' => 1]);
    }

    public function test_settings_return_the_saved_api_key(): void
    {
        $response = $this->getJson('/settings/pipedrive')->assertOk();

        $this->assertTrue($response->json('data.has_key'));
        $this->assertSame('secret-pipedrive-key-ab12', $response->json('data.pipedrive_key'));
    }

    public function test_saving_with_a_blank_key_keeps_the_saved_one(): void
    {
        Http::fake(['api.pipedrive.com/v1/users/me*' => Http::response(['success' => true])]);

        $this->patchJson('/settings/pipedrive', [
            'pipedrive_key' => '',
            'require_pipedrive_user_verification' => true,
            'status' => 1,
        ])->assertOk();

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'api_token=secret-pipedrive-key-ab12'));
        $this->assertSame('secret-pipedrive-key-ab12', ApiKey::value('pipedrive_api_key'));
        $this->assertTrue((bool) ApiKey::value('require_pipedrive_user_verification'));
    }

    public function test_saving_without_any_key_is_rejected(): void
    {
        ApiKey::query()->update(['pipedrive_api_key' => null]);

        $this->patchJson('/settings/pipedrive', ['pipedrive_key' => ''])->assertStatus(400);
    }

    public function test_disconnect_forgets_the_key_and_switches_off(): void
    {
        $this->postJson('/settings/pipedrive/disconnect')->assertOk();

        $this->assertNull(ApiKey::value('pipedrive_api_key'));
        $this->assertSame(0, (int) StatusSetting::value('pipedrive_status'));
        $this->assertFalse($this->getJson('/settings/pipedrive')->json('data.has_key'));
    }
}
