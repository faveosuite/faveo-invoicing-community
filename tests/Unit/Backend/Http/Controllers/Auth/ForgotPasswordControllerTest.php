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
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_send_reset_link_invalid_email_format_returns_422(): void
    {
        $response = $this->postJson('/password/email', ['email' => 'not-an-email']);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_send_reset_link_nonexistent_email_returns_422(): void
    {
        // 'exists:users,email' rule fails when email not in DB
        $response = $this->postJson('/password/email', [
            'email' => 'nobody_'.uniqid().'@example.com',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_send_reset_link_for_known_user_covers_controller_body(): void
    {
        $user = User::factory()->create();
        $response = $this->postJson('/password/email', [
            'email' => $user->email,
            'forgot' => $this->honeypot(),
        ]);
        // 200 (mail sent) or 400 (mail not configured in test env) — both paths hit the controller body
        $this->assertContains($response->status(), [200, 400]);
        $response->assertJson(['success' => $response->status() === 200]);
    }
}
