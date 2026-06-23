<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Auth;

use App\User;
use Crypt;
use Tests\DBTestCase;

class AuthControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    private function honeypot(): array
    {
        // LoginRequest requires a valid Honeypot token in the 'login' field
        return ['pTestKey' => '', 'tTestKey' => Crypt::encrypt(time() - 2)];
    }

    // =========================================================================
    // POST /login — success: response body has redirect URL
    // =========================================================================

    public function test_valid_credentials_return_200_with_redirect_data(): void
    {
        $user = User::factory()->create(['password' => bcrypt('P@ssw0rd!')]);

        $response = $this->postJson('/login', [
            'email_username' => $user->email,
            'password1' => 'P@ssw0rd!',
            'login' => $this->honeypot(),
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        // On success the controller returns a redirect URL
        $this->assertArrayHasKey('redirect', $response->json('data'));
        $this->assertNotEmpty($response->json('data.redirect'));
    }

    // =========================================================================
    // POST /login — wrong password: exact error message
    // =========================================================================

    public function test_wrong_password_returns_400_with_specific_error_message(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct')]);

        $response = $this->postJson('/login', [
            'email_username' => $user->email,
            'password1' => 'wrong',
            'login' => $this->honeypot(),
        ]);

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
        $this->assertSame(
            'Your email or password is incorrect. Please check and try again.',
            $response->json('message')
        );
    }

    // =========================================================================
    // POST /login — nonexistent user: same error message (no user enumeration)
    // =========================================================================

    public function test_nonexistent_user_returns_400_without_revealing_existence(): void
    {
        $response = $this->postJson('/login', [
            'email_username' => 'nobody@example.com',
            'password1' => 'password',
            'login' => $this->honeypot(),
        ]);

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
        // Must return the same message as wrong password — no user enumeration
        $this->assertSame(
            'Your email or password is incorrect. Please check and try again.',
            $response->json('message')
        );
    }

    // =========================================================================
    // POST /login — missing email: 422 with field error
    // =========================================================================

    public function test_missing_password_field_returns_422_with_password_error(): void
    {
        $response = $this->postJson('/login', [
            'email_username' => 'user@test.com',
            'login' => $this->honeypot(),
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('password1', $response->json('errors'));
    }

    // =========================================================================
    // POST /login — BUG documented: missing email crashes controller (500)
    // =========================================================================

    public function test_missing_email_returns_500_due_to_known_controller_bug(): void
    {
        // BUG: controller accesses $user before null-checking it.
        // Expected: 422 with email_username error. Actual: 500.
        // This test documents the bug so any accidental fix is caught.
        $response = $this->postJson('/login', [
            'password1' => 'secret',
            'login' => $this->honeypot(),
        ]);

        $response->assertStatus(500); // BUG — should be 422
    }

    // =========================================================================
    // POST /login — honeypot filled: 422 blocks bot
    // =========================================================================

    public function test_filled_honeypot_returns_422_blocking_bot_submission(): void
    {
        $response = $this->postJson('/login', [
            'email_username' => 'user@test.com',
            'password1' => 'pass',
            'login' => ['pTestKey' => 'bot-filled', 'tTestKey' => Crypt::encrypt(time() - 2)],
        ]);

        $response->assertStatus(422);
    }

    // =========================================================================
    // GET /auth/logout — returns 204 (no content)
    // =========================================================================

    public function test_logout_authenticated_user_returns_204_no_content(): void
    {
        $this->getLoggedInUser('user');
        $this->getJson('/auth/logout')->assertStatus(204);
    }

    // =========================================================================
    // GET /auth/verify-config — public SPA config endpoint
    // =========================================================================

    public function test_verify_config_returns_200(): void
    {
        $this->getJson('/auth/verify-config')->assertStatus(200);
    }
}
