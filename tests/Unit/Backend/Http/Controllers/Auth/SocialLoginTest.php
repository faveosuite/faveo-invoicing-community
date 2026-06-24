<?php

namespace Tests\Unit\Backend\Http\Controllers\Auth;

use App\SocialLogin;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SocialLoginTest extends TestCase
{
    use DatabaseTransactions;

    public function test_updates_social_login_settings_successfully(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $this->withoutMiddleware();

        // Ensure a Google social login record exists
        SocialLogin::firstOrCreate(['type' => 'Google'], [
            'client_id' => '',
            'client_secret' => '',
            'redirect_url' => '',
            'status' => 0,
        ]);

        $response = $this->postJson('update-social-login', [
            'type' => 'Google',
            'client_id' => 'new-client-id',
            'client_secret' => 'new-client-secret',
            'redirect_url' => 'https://new-url.com',
            'optradio' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('social_logins', [
            'type' => 'Google',
            'client_id' => 'new-client-id',
            'client_secret' => 'new-client-secret',
            'redirect_url' => 'https://new-url.com',
            'status' => 1,
        ]);
    }

}
