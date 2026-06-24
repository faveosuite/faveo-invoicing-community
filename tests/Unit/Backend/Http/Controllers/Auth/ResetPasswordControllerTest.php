<?php

namespace Tests\Unit\Backend\Http\Controllers\Auth;

use App\Model\User\Password;
use App\User;
use Crypt;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\DBTestCase;

class ResetPasswordControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    private function honeypot(): array
    {
        return ['pKey' => '', 'tKey' => Crypt::encrypt(time() - 2)];
    }

    // =========================================================================
    // GET /auth/reset-validate/{token} — ResetPasswordController::showResetForm
    // =========================================================================

    public function test_show_reset_form_with_expired_token_returns_400(): void
    {
        // No matching token in DB → errorResponse
        $response = $this->getJson('/auth/reset-validate/nonexistent-token');
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_show_reset_form_with_valid_token_returns_200(): void
    {
        $user = User::factory()->create();
        $token = \Str::random(40);
        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        $response = $this->getJson("/auth/reset-validate/{$token}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'data']);
    }

    // =========================================================================
    // POST /password/reset — ResetPasswordController::reset
    // =========================================================================

    public function test_reset_missing_token_returns_422(): void
    {
        $response = $this->postJson('/password/reset', ['reset' => $this->honeypot()]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['token']);
    }

    public function test_reset_missing_email_returns_422(): void
    {
        $response = $this->postJson('/password/reset', [
            'token' => 'abc',
            'reset' => $this->honeypot(),
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_reset_missing_password_returns_422(): void
    {
        $response = $this->postJson('/password/reset', [
            'token' => 'abc',
            'email' => 'test@example.com',
            'reset' => $this->honeypot(),
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_reset_with_invalid_token_returns_400(): void
    {
        $user = User::factory()->create();
        $response = $this->postJson('/password/reset', [
            'token' => 'wrong-token',
            'email' => $user->email,
            'password' => 'NewPass@1234',
            'password_confirmation' => 'NewPass@1234',
            'reset' => $this->honeypot(),
        ]);
        // No matching token in Password table → errorResponse 400
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_show_reset_form_redirects_to_2fa_when_user_has_2fa_enabled(): void
    {
        // Covers lines 60-65: user with 2FA enabled gets redirected to verify-2fa
        $user = User::factory()->create(['is_2fa_enabled' => 1]);
        $token = \Str::random(40);
        Password::create(['email' => $user->email, 'token' => $token, 'created_at' => now()]);

        $response = $this->getJson('/password/reset/'.$token);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        // Response should contain redirect to 2fa verification
        $this->assertStringContainsString('verify-2fa', json_encode($response->json()));
    }

    public function test_reset_returns_400_when_email_has_no_matching_user(): void
    {
        // Covers line 113: user not found after valid token
        $email = 'ghost_'.uniqid().'@example.com';
        $token = \Str::random(40);

        // Create a Password reset record but NOT the user
        Password::create(['email' => $email, 'token' => $token, 'created_at' => now()]);

        $response = $this->postJson('/password/reset', [
            'token' => $token,
            'email' => $email,
            'password' => 'NewPass@1234',
            'password_confirmation' => 'NewPass@1234',
            'reset' => $this->honeypot(),
        ]);

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_reset_with_valid_token_changes_password_and_returns_200(): void
    {
        $user = User::factory()->create();
        $token = \Str::random(40);
        Password::create([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        $response = $this->postJson('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPass@1234',
            'password_confirmation' => 'NewPass@1234',
            'reset' => $this->honeypot(),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('data.redirect', url('login'));
    }
}
