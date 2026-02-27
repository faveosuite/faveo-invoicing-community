<?php

namespace Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SocialLoginTest extends TestCase
{
    use DatabaseTransactions;

    public function test_updates_social_login_settings_successfully()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->call('POST', 'update-social-login', [
            'type' => 'Google',
            'client_id' => 'new-client-id',
            'client_secret' => 'new-client-secret',
            'redirect_url' => 'https://new-url.com',
            'optradio' => 1,
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('social_logins', [
            'type' => 'Google',
            'client_id' => 'new-client-id',
            'client_secret' => 'new-client-secret',
            'redirect_url' => 'https://new-url.com',
            'status' => 1,
        ]);
    }
}
