<?php

namespace Tests\Unit\Backend\Http\Controllers\Auth;

use App\Http\Middleware\Install;
use App\Model\Common\StatusSetting;
use App\User;
use App\UserBackupCodes;
use Crypt;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
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
        $token = $this->issueToken($user);

        $response = $this->getJson("/auth/reset-validate/{$token}?email=".urlencode($user->email));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'data']);
    }

    // =========================================================================
    // POST /password/reset — ResetPasswordController::reset
    // =========================================================================

    public function test_reset_missing_token_returns_422(): void
    {
        $response = $this->postJson('/password/reset', ['honeypot' => $this->honeypot()]);
        $response->assertStatus(412);
        $response->assertJsonValidationErrors(['token'], 'message');
    }

    public function test_reset_missing_email_returns_422(): void
    {
        $response = $this->postJson('/password/reset', [
            'token' => 'abc',
            'honeypot' => $this->honeypot(),
        ]);
        $response->assertStatus(412);
        $response->assertJsonValidationErrors(['email'], 'message');
    }

    public function test_reset_missing_password_returns_422(): void
    {
        $response = $this->postJson('/password/reset', [
            'token' => 'abc',
            'email' => 'test@example.com',
            'honeypot' => $this->honeypot(),
        ]);
        $response->assertStatus(412);
        $response->assertJsonValidationErrors(['password'], 'message');
    }

    public function test_reset_with_invalid_token_returns_400(): void
    {
        $user = User::factory()->create();
        $response = $this->postJson('/password/reset', [
            'token' => 'wrong-token',
            'email' => $user->email,
            'password' => 'NewPass@1234',
            'password_confirmation' => 'NewPass@1234',
            'honeypot' => $this->honeypot(),
        ]);
        // No matching token in Password table → errorResponse 400
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_show_reset_form_redirects_to_2fa_when_user_has_2fa_enabled(): void
    {
        // Covers lines 60-65: user with 2FA enabled gets redirected to verify-2fa
        $user = User::factory()->create(['is_2fa_enabled' => 1]);
        $token = $this->issueToken($user);

        $response = $this->getJson('/auth/reset-validate/'.$token.'?email='.urlencode($user->email));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        // Response should contain redirect to 2fa verification
        $this->assertStringContainsString('verify-2fa', json_encode($response->json()));
    }

    public function test_reset_page_itself_starts_the_2fa_session(): void
    {
        // The SPA's reset-validate XHR races other shell XHRs; the page request must set this.
        $user = User::factory()->create(['is_2fa_enabled' => 1]);
        $token = $this->issueToken($user);

        $this->get('/password/reset/'.$token.'?email='.urlencode($user->email))
            ->assertOk()
            ->assertSessionHas('2fa:user:id', $user->id)
            ->assertSessionHas('verification_user_id', $user->id)
            ->assertSessionHas('reset_token', $token);
    }

    public function test_reset_returns_400_when_email_has_no_matching_user(): void
    {
        $email = 'ghost_'.uniqid().'@example.com';
        $token = \Str::random(64);

        $response = $this->postJson('/password/reset', [
            'token' => $token,
            'email' => $email,
            'password' => 'NewPass@1234',
            'password_confirmation' => 'NewPass@1234',
            'honeypot' => $this->honeypot(),
        ]);

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_reset_with_valid_token_changes_password_and_returns_200(): void
    {
        $user = User::factory()->create();
        $token = $this->issueToken($user);

        $response = $this->postJson('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPass@1234',
            'password_confirmation' => 'NewPass@1234',
            'honeypot' => $this->honeypot(),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('data.redirect', url('login'));
        $this->assertDatabaseMissing('password_resets', ['email' => $user->email]); // one-time token
    }

    public function test_tokens_are_stored_hashed_and_need_the_matching_email(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $token = $this->issueToken($user);

        $this->assertNotSame($token, DB::table('password_resets')->where('email', $user->email)->value('token'));
        $this->getJson('/auth/reset-validate/'.$token)->assertStatus(400); // no email
        $this->getJson('/auth/reset-validate/'.$token.'?email='.urlencode($other->email))->assertStatus(400);
    }

    // ── Direct POSTs must not skip the checks showResetForm() does ──────────

    /** A real (hashed) reset token from Laravel's broker; optionally backdated. */
    private function issueToken(User $user, $createdAt = null): string
    {
        $token = Password::broker()->createToken($user);
        if ($createdAt) {
            DB::table('password_resets')->where('email', $user->email)->update(['created_at' => $createdAt]);
        }

        return $token;
    }

    private function postReset(User $user, string $token)
    {
        return $this->postJson('/password/reset', [
            'token' => $token, 'email' => $user->email,
            'password' => 'N3w!Passw0rd#x', 'password_confirmation' => 'N3w!Passw0rd#x',
            'honeypot' => $this->honeypot(),
        ]);
    }

    public function test_reset_rejects_an_expired_token(): void
    {
        $user = User::factory()->create(['is_2fa_enabled' => 0]);
        $token = $this->issueToken($user, now()->subDays(30));

        $this->postReset($user, $token)->assertStatus(400);
        $this->assertFalse(\Hash::check('N3w!Passw0rd#x', $user->fresh()->password));
    }

    public function test_reset_requires_2fa_for_2fa_users(): void
    {
        $user = User::factory()->create(['is_2fa_enabled' => 1]);
        $token = $this->issueToken($user);

        $this->postReset($user, $token)->assertStatus(400);
        $this->assertFalse(\Hash::check('N3w!Passw0rd#x', $user->fresh()->password));
    }

    public function test_2fa_passed_for_another_link_does_not_count(): void
    {
        $user = User::factory()->create(['is_2fa_enabled' => 1]);
        $token = $this->issueToken($user);

        $this->withSession(['2fa_verified' => 'tok-someone-else'])->postReset($user, $token)->assertStatus(400);
        $this->withSession(['2fa_verified' => true])->postReset($user, $token)->assertStatus(400);
        $this->assertFalse(\Hash::check('N3w!Passw0rd#x', $user->fresh()->password));
    }

    public function test_reset_succeeds_once_2fa_passed_for_this_link(): void
    {
        $user = User::factory()->create(['is_2fa_enabled' => 1]);
        $token = $this->issueToken($user);

        $this->withSession(['2fa_verified' => $token])->postReset($user, $token)->assertStatus(200);
        $this->assertTrue(\Hash::check('N3w!Passw0rd#x', $user->fresh()->password));
    }

    // ── 2FA for a reset link, through the real requests (session included) ──

    private function twoFaUserWithRecoveryCode(string $code): User
    {
        $user = User::factory()->create([
            'role' => 'user', 'is_2fa_enabled' => 1, 'active' => 1,
            'email_verified' => 1, 'mobile_verified' => 1, 'password' => \Hash::make('password'),
        ]);
        UserBackupCodes::create(['user_id' => $user->id, 'backup_codes' => $code]);

        return $user;
    }

    private function passRecoveryCode(string $code)
    {
        return $this->postJson('/verify-recovery-code', ['recovery_code' => $code, 'honeypot' => $this->honeypot()]);
    }

    public function test_another_accounts_2fa_does_not_unlock_a_reset_link(): void
    {
        $this->withMiddleware()->withoutMiddleware(Install::class);
        StatusSetting::create(['emailverification_status' => 0, 'msg91_status' => 0, 'recaptcha_status' => 0]);
        $victim = $this->twoFaUserWithRecoveryCode('victim-code');
        $attacker = $this->twoFaUserWithRecoveryCode('attacker-code');
        $token = $this->issueToken($victim);

        // Holding the victim's link, the attacker passes 2FA for their *own* account in the same session.
        $this->getJson('/auth/reset-validate/'.$token.'?email='.urlencode($victim->email))->assertJsonPath('data.redirect', url('verify-2fa'));
        $this->postJson('/login', ['login' => $attacker->email, 'password' => 'password', 'honeypot' => $this->honeypot()])
            ->assertJsonPath('data.redirect', url('verify-2fa'));
        $this->passRecoveryCode('attacker-code')
            ->assertOk()
            ->assertJsonMissing(['redirect' => route('password.reset', ['token' => $token, 'email' => $victim->email])]);

        $this->assertAuthenticatedAs($attacker); // a plain login of their own account, nothing more
        $this->assertNotSame($token, session('2fa_verified'));

        auth()->logout(); // session (and anything 2FA left in it) stays, as in the browser
        $this->postReset($victim, $token)->assertStatus(400);
        $this->assertFalse(\Hash::check('N3w!Passw0rd#x', $victim->fresh()->password));
    }

    public function test_reset_link_owner_passes_2fa_and_resets(): void
    {
        $this->withMiddleware()->withoutMiddleware(Install::class);
        $user = $this->twoFaUserWithRecoveryCode('owner-code');
        $token = $this->issueToken($user);

        // The link's email may differ in case from the account's.
        $this->getJson('/auth/reset-validate/'.$token.'?email='.urlencode(strtoupper($user->email)))->assertJsonPath('data.redirect', url('verify-2fa'));
        $this->passRecoveryCode('owner-code')
            ->assertOk()
            ->assertJsonPath('data.redirect', route('password.reset', ['token' => $token, 'email' => $user->email]));
        $this->assertGuest(); // 2FA for a reset unlocks the reset only, not a login

        $this->postReset($user, $token)->assertOk();
        $this->assertTrue(\Hash::check('N3w!Passw0rd#x', $user->fresh()->password));
    }
}
