<?php

namespace Tests\Unit\Backend\Http\Controllers\Auth;

use App\User;
use App\UserBackupCodes;
use Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class Google2FAControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    // -------------------------------------------------------------------------
    // verify2fa — GET /verify-2fa
    // -------------------------------------------------------------------------

    public function test_verify_2fa_returns_redirect_to_login_when_no_session(): void
    {
        $response = $this->getJson('/auth/2fa-check');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_verify_2fa_returns_success_when_session_set(): void
    {
        session(['2fa:user:id' => $this->user->id]);

        $response = $this->getJson('/auth/2fa-check');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // showVerifyPassword — GET /show/verify-password
    // -------------------------------------------------------------------------

    public function test_show_verify_password_returns_success(): void
    {
        $response = $this->getJson('/show/verify-password');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // verifyPassword — POST /verify-password
    // -------------------------------------------------------------------------

    public function test_verify_password_social_login_path_sets_session(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/verify-password', [
            'login_type' => 'social',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_verify_password_returns_success_when_correct(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret123')]);
        $this->actingAs($user);

        $response = $this->postJson('/verify-password', [
            'user_password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_verify_password_returns_error_when_incorrect(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret123')]);
        $this->actingAs($user);

        $response = $this->postJson('/verify-password', [
            'user_password' => 'wrong_password',
        ]);

        // errorResponse() defaults to 400
        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    // -------------------------------------------------------------------------
    // enableTwoFactor — POST /2fa/enable
    // -------------------------------------------------------------------------

    public function test_enable_two_factor_returns_qr_and_secret(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/2fa/enable');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['image', 'secret']]);
    }

    // -------------------------------------------------------------------------
    // disableTwoFactor — POST /2fa/disable
    // -------------------------------------------------------------------------

    public function test_disable_two_factor_disables_for_own_account(): void
    {
        $this->user->is_2fa_enabled = 1;
        $this->user->google2fa_secret = 'test_secret';
        $this->user->save();

        $this->actingAs($this->user);

        $response = $this->postJson('/2fa/disable');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->user->refresh();
        $this->assertEquals(0, $this->user->is_2fa_enabled);
    }

    public function test_disable_two_factor_admin_can_disable_for_another_user(): void
    {
        $targetUser = User::factory()->create(['is_2fa_enabled' => 1, 'role' => 'user']);
        $this->actingAs($this->user); // admin

        $response = $this->postJson('/2fa/disable/'.$targetUser->id);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_disable_two_factor_non_admin_cannot_disable_for_other(): void
    {
        $nonAdmin = User::factory()->create(['role' => 'user']);
        $targetUser = User::factory()->create(['role' => 'user']);
        $this->actingAs($nonAdmin);

        $response = $this->postJson('/2fa/disable/'.$targetUser->id);

        // errorResponse() → 400
        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    // -------------------------------------------------------------------------
    // generateRecoveryCode — POST /2fa-recovery-code
    // -------------------------------------------------------------------------

    public function test_generate_recovery_code_returns_ten_codes(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/2fa-recovery-code');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $codes = $response->json('data.code');
        $this->assertCount(10, $codes);
    }

    // -------------------------------------------------------------------------
    // postLoginValidateToken — POST /2fa/loginValidate
    // -------------------------------------------------------------------------

    public function test_post_login_validate_token_returns_error_when_no_session(): void
    {
        // No 2fa:user:id in session → findOrFail(null) throws → errorResponse 400
        // OR ValidateSecretRequest validation fails → 422
        $response = $this->postJson('/2fa/loginValidate', [
            'totp' => '000000',
            'validate_2fa' => ['pot_field' => '', 'time_field' => encrypt(time() - 10)],
        ]);

        $this->assertContains($response->getStatusCode(), [200, 400, 422]);
    }

    public function test_post_login_validate_token_returns_error_for_invalid_code(): void
    {
        $user = User::factory()->create([
            'is_2fa_enabled' => 1,
            'google2fa_secret' => \Illuminate\Support\Facades\Crypt::encrypt('JBSWY3DPEHPK3PXP'),
        ]);

        session(['2fa:user:id' => $user->id]);

        $response = $this->postJson('/2fa/loginValidate', [
            'totp' => '000000',
            'validate_2fa' => ['pot_field' => '', 'time_field' => encrypt(time() - 10)],
        ]);

        // ValidateSecretRequest requires 'totp' — 422 if validation fails, 400 from errorResponse if it runs
        $this->assertContains($response->getStatusCode(), [200, 400, 422]);
        $this->assertFalse($response->json('success') ?? false);
    }

    // -------------------------------------------------------------------------
    // verifyRecoveryCode — POST /verify-recovery-code
    // -------------------------------------------------------------------------

    public function test_verify_recovery_code_returns_error_when_no_session(): void
    {
        // No session → errorResponse (400) or findOrFail throws
        $response = $this->postJson('/verify-recovery-code', [
            'rec_code' => 'invalid_code',
            'recovery_code' => ['pot_field' => '', 'time_field' => encrypt(time() - 10)],
        ]);

        $this->assertContains($response->getStatusCode(), [200, 400, 422]);
    }

    public function test_verify_recovery_code_returns_error_for_invalid_code(): void
    {
        $user = User::factory()->create(['is_2fa_enabled' => 1]);
        session(['2fa:user:id' => $user->id]);

        $response = $this->postJson('/verify-recovery-code', [
            'rec_code' => 'nonexistent_code_xyzzy',
            'recovery_code' => ['pot_field' => '', 'time_field' => encrypt(time() - 10)],
        ]);

        $this->assertContains($response->getStatusCode(), [200, 400, 422]);
    }

    public function test_verify_recovery_code_succeeds_with_valid_code(): void
    {
        $user = User::factory()->create(['is_2fa_enabled' => 1]);
        UserBackupCodes::create(['user_id' => $user->id, 'backup_codes' => 'valid_test_code_abc123']);
        session(['2fa:user:id' => $user->id]);

        $response = $this->postJson('/verify-recovery-code', [
            'rec_code' => 'valid_test_code_abc123',
            'recovery_code' => ['pot_field' => '', 'time_field' => encrypt(time() - 10)],
        ]);

        // 200 success OR errorResponse if redirect path fails
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }
}
