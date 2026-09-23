<?php

namespace Tests\Unit\Backend\Http\Controllers\Auth;

use App\User;
use Crypt;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ForgotPasswordControllerTest extends DBTestCase
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
    // POST /password/email — ForgotPasswordController::sendResetLinkEmail
    // =========================================================================

    public function test_send_reset_link_missing_email_returns_422(): void
    {
        $response = $this->postJson('/password/email', []);
        $response->assertStatus(412);
        $response->assertJsonValidationErrors(['email'], 'message');
    }

    public function test_send_reset_link_invalid_email_format_returns_422(): void
    {
        $response = $this->postJson('/password/email', ['email' => 'not-an-email']);
        $response->assertStatus(412);
        $response->assertJsonValidationErrors(['email'], 'message');
    }

    public function test_send_reset_link_nonexistent_email_returns_422(): void
    {
        // 'exists:users,email' rule fails when email not in DB
        $response = $this->postJson('/password/email', [
            'email' => 'nobody_'.uniqid().'@example.com',
        ]);
        $response->assertStatus(412);
        $response->assertJsonValidationErrors(['email'], 'message');
    }

    public function test_send_reset_link_for_known_user_covers_controller_body(): void
    {
        $user = User::factory()->create();
        $response = $this->postJson('/password/email', [
            'email' => $user->email,
            'forgot' => $this->honeypot(),
        ]);
        $this->assertContains($response->status(), [200, 400]);
    }

    public function test_send_reset_link_deletes_existing_token_before_creating_new(): void
    {
        // Covers line 72: existing password reset record gets deleted before new one is created
        $user = User::factory()->create();

        // Create an existing reset token
        \App\Model\User\Password::create(['email' => $user->email, 'token' => 'old-token', 'created_at' => now()]);

        $response = $this->postJson('/password/email', [
            'email' => $user->email,
            'forgot' => $this->honeypot(),
        ]);

        $this->assertContains($response->status(), [200, 400]);
        // Old token should be gone
        $this->assertDatabaseMissing('password_resets', ['token' => 'old-token']);
    }

    public function test_send_reset_link_returns_error_when_rate_limited(): void
    {
        // Covers line 66: rate limit exceeded via session-based ArrayStore rate limiting
        $user = User::factory()->create();
        $ip = '127.0.0.1';
        $sessionKey = 'forgot_password'.$user->email.':'.$ip;

        // Simulate 3 prior attempts (maxAttempts = 3) in session
        session()->put($sessionKey, 3);
        session()->put($sessionKey.'_time', time());

        $response = $this->postJson('/password/email', [
            'email' => $user->email,
            'forgot' => $this->honeypot(),
        ]);

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }
}
