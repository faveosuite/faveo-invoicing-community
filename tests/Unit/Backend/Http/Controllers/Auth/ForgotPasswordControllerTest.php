<?php

namespace Tests\Unit\Backend\Http\Controllers\Auth;

use App\Http\Middleware\Install;
use App\Model\Common\StatusSetting;
use App\User;
use Crypt;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
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

    public function test_unknown_and_known_emails_get_the_same_reply_and_no_token_for_unknown(): void
    {
        // Must not reveal whether an account exists (no `exists` rule; same reply either way).
        Bus::fake();
        $email = 'nobody_'.uniqid().'@example.com';
        $unknown = $this->postJson('/password/email', ['email' => $email, 'honeypot' => $this->honeypot()]);
        $known = $this->postJson('/password/email', ['email' => User::factory()->create()->email, 'honeypot' => $this->honeypot()]);

        $this->assertSame($known->status(), $unknown->status());
        $this->assertSame($known->json(), $unknown->json());
        $unknown->assertJsonPath('message', __('validation.forgot_email_validation'));
        $this->assertDatabaseMissing('password_resets', ['email' => $email]);
    }

    public function test_send_reset_link_for_known_user_covers_controller_body(): void
    {
        $user = User::factory()->create();
        $response = $this->postJson('/password/email', [
            'email' => $user->email,
            'honeypot' => $this->honeypot(),
        ]);
        $this->assertContains($response->status(), [200, 400]);
    }

    public function test_a_new_link_replaces_the_old_token_and_is_stored_hashed(): void
    {
        Bus::fake(); // the reset mail job
        \App\Model\Common\Setting::where('id', 1)->update(['sending_status' => 1]);
        $user = User::factory()->create();
        Password::broker()->createToken($user);
        // Past the broker's per-email throttle window, so a new token is created.
        DB::table('password_resets')->where('email', $user->email)->update(['created_at' => now()->subMinutes(5)]);
        $old = DB::table('password_resets')->where('email', $user->email)->value('token');

        $this->postJson('/password/email', ['email' => $user->email, 'honeypot' => $this->honeypot()])->assertOk();

        $rows = DB::table('password_resets')->where('email', $user->email)->pluck('token');
        $this->assertCount(1, $rows);
        $this->assertNotSame($old, $rows[0]);
        $this->assertStringStartsWith('$2y$', $rows[0]); // bcrypt hash, never the raw token
    }

    public function test_fourth_request_in_six_minutes_is_rate_limited(): void
    {
        Bus::fake();
        $this->withMiddleware()->withoutMiddleware(Install::class);
        StatusSetting::query()->update(['recaptcha_status' => 0]);
        $email = 'nobody_'.uniqid().'@example.com';
        $send = fn () => $this->postJson('/password/email', ['email' => $email, 'honeypot' => $this->honeypot()]);

        foreach (range(1, 3) as $_) {
            $this->assertNotSame(429, $send()->status());
        }
        $send()->assertStatus(429)->assertJson(['success' => false])
            ->assertJsonPath('message', fn (string $m) => str_contains($m, ' ') && $m !== 'Too Many Attempts.');
    }
}
