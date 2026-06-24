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
