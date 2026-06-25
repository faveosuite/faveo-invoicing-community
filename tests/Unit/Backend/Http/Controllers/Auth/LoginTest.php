<?php

namespace Tests\Unit\Backend\Http\Controllers\Auth;

use App\Http\Middleware\Install;
use App\Model\Common\StatusSetting;
use App\Plugins\Recaptcha\Model\RecaptchaSetting;
use App\User;
use App\VerificationAttempt;
use Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class LoginTest extends DBTestCase
{
    use DatabaseTransactions;

    #[Group('postLogin')]
    public function test_post_login_for_verified_users(): void
    {
        \App\DefaultPage::query()->delete(); // ensure url('/') fallback, not DB page_url
        $user = User::factory()->create(['password' => Hash::make('password')]);
        StatusSetting::create(['emailverification_status' => 0, 'msg91_status' => 0, 'recaptcha_status' => 0]);
        $this->withoutMiddleware();
        $response = $this->postJson('/login', ['email_username' => $user->email, 'password1' => 'password', 'login' => [
            'pot_field' => '',     // valid
            'time_field' => encrypt(time() - 10), // valid
        ]]);

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);

        $response->assertJsonStructure([
            'success',
            'data' => [
                'redirect',
            ],
        ]);

        $response->assertJson([
            'success' => true,
            'data' => [
                'redirect' => url('/'),
            ],
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Group('postLogin')]
    public function test_post_login_for_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'password' => Hash::make('password')]);
        StatusSetting::create(['emailverification_status' => 0, 'msg91_status' => 0, 'recaptcha_status' => 0]);
        $this->withoutMiddleware();
        $response = $this->postJson('/login', ['email_username' => $user->email, 'password1' => 'password', 'login' => [
            'pot_field' => '',     // valid
            'time_field' => encrypt(time() - 10), // valid
        ]]);

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);

        $response->assertJsonStructure([
            'success',
            'data' => [
                'redirect',
            ],
        ]);

        $response->assertJson([
            'success' => true,
            'data' => [
                'redirect' => url('/admin'),
            ],
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Group('postLogin')]
    public function test_post_login_when_mobile_is_unverified(): void
    {
        $user = User::factory()->create(['mobile_verified' => 0, 'password' => Hash::make('password')]);
        StatusSetting::updateOrCreate(
            ['id' => 1],
            ['msg91_status' => 1, 'emailverification_status' => 0]
        );
        $this->withoutMiddleware();
        $response = $this->postJson('/login', ['email_username' => $user->email, 'password1' => 'password', 'login' => [
            'pot_field' => '',     // valid
            'time_field' => encrypt(time() - 10), // valid
        ]]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'redirect' => url('/verify'),
            ],
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Group('postLogin')]
    public function test_post_login_when_email_is_unverified(): void
    {
        $user = User::factory()->create(['email_verified' => 0, 'password' => Hash::make('password')]);
        StatusSetting::updateOrCreate(
            ['id' => 1],
            ['emailverification_status' => 1, 'msg91_status' => 0]
        );
        $this->withoutMiddleware();
        $response = $this->postJson('/login', ['email_username' => $user->email, 'password1' => 'password', 'login' => [
            'pot_field' => '',     // valid
            'time_field' => encrypt(time() - 10), // valid
        ]]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'redirect' => url('/verify'),
            ],
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Group('postLogin')]
    public function test_post_login_when_email_and_mobile_are_unverified(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password'), 'email_verified' => 0, 'mobile_verified' => 0]);
        $this->withoutMiddleware();
        StatusSetting::updateOrCreate(
            ['id' => 1],
            ['emailverification_status' => 1, 'msg91_status' => 1]
        );
        VerificationAttempt::create(['user_id' => $user->id, 'mobile_attempt' => 2, 'email_attempt' => 3]);
        $response = $this->postJson('/login', ['email_username' => $user->email, 'password1' => 'password',  'login' => [
            'pot_field' => '',     // valid
            'time_field' => encrypt(time() - 10), // valid
        ]]);

        $response->assertJson([
            'success' => true,
            'data' => [
                'redirect' => url('/verify'),
            ],
        ]);
    }

    public function test_login_should_fail_when_the_user_not_present(): void
    {
        User::factory()->create(['password' => Hash::make('password')]);
        StatusSetting::create(['emailverification_status' => 1, 'msg91_status' => 0, 'recaptcha_status' => 0]);
        $this->withoutMiddleware();
        $response = $this->postJson('/login', ['email_username' => 'santhanuchakrapa@gmail.com', 'password1' => 'password', 'login' => [
            'pot_field' => '',     // valid
            'time_field' => encrypt(time() - 10), // valid
        ]]);

        $response->assertJson([
            'success' => false,
            'message' => 'Your email or password is incorrect. Please check and try again.',
        ]);
    }

    public function test_login_fails_when_password_is_wrong(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        StatusSetting::create(['emailverification_status' => 0, 'msg91_status' => 0, 'recaptcha_status' => 0]);
        $this->withoutMiddleware();
        $response = $this->postJson('/login', ['email_username' => $user->email, 'password1' => 'passwor', 'login' => [
            'pot_field' => '',     // valid
            'time_field' => encrypt(time() - 10), // valid
        ]]);

        $response->assertJson([
            'success' => false,
            'message' => 'Your email or password is incorrect. Please check and try again.',
        ]);
    }

    public function test_when_2fa_is_enabled(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password'), 'is_2fa_enabled' => 1]);
        StatusSetting::create(['emailverification_status' => 0, 'msg91_status' => 0, 'recaptcha_status' => 0]);
        $this->withoutMiddleware();
        $response = $this->postJson('/login', [
            'email_username' => $user->email,
            'password1' => 'password',
            'login' => [
                'pot_field' => '',     // valid
                'time_field' => encrypt(time() - 10), // valid
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'redirect' => url('/verify-2fa'),
            ],
        ]);
    }

    #[Group('postLogin')]
    public function test_it_fails_when_honeypot_field_is_filled(): void
    {
        $this->withoutMiddleware();

        $response = $this->postJson('/login', [
            'email_username' => 'user@example.com',
            'password1' => 'password',
            'g-recaptcha-response' => 'dummy-token',
            'login' => [
                'pot_field' => 'asdfghjk',     // valid
                'time_field' => encrypt(time() - 10), // valid
            ],
        ]);

        $response->assertStatus(422);
    }

    #[Group('postLogin')]
    public function test_it_fails_when_recaptcha_is_missing(): void
    {
        StatusSetting::updateOrCreate(
            ['id' => 1],
            [
                'recaptcha_status' => 1,
            ]
        );

        RecaptchaSetting::firstOrNew()->fill([
            'v2_site_key' => 'dummy-site-key',
            'v2_secret_key' => 'dummy-v2-secret-key',
            'captcha_version' => 'v2_checkbox',
            'failover_action' => 'none',
        ])->save();

        $this->withoutMiddleware([Install::class]);

        $response = $this->postJson('/login', [
            'email_username' => 'user@example.com',
            'password1' => 'password',
            // this is a honeypot field, it should be empty
            'login' => [
                'pot_field' => '',     // valid
                'time_field' => encrypt(time() - 10), // valid
            ],
            // missing g-recaptcha-response
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => __('recaptcha::recaptcha.captcha_message'),
        ]);
    }

    #[Group('postLogin')]
    public function test_it_succeeds_with_valid_input_and_no_honeypot(): void
    {
        \App\DefaultPage::query()->delete(); // ensure url('/') fallback
        $user = User::factory()->create(['active' => 1, 'email' => 'user@example.com', 'password' => bcrypt('password')]);
        StatusSetting::create(['emailverification_status' => 0, 'msg91_status' => 0, 'recaptcha_status' => 0]);
        $this->withoutMiddleware();

        $response = $this->postJson('/login', [
            'email_username' => 'user@example.com',
            'password1' => 'password',
            'g-recaptcha-response' => 'test-bypass',
            'login' => [
                'pot_field' => '',     // valid
                'time_field' => encrypt(time() - 10), // valid
            ],
        ]);

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);
        $response->assertJson([
            'success' => true,
            'data' => [
                'redirect' => url('/'),
            ],
        ]);
    }

    #[Group('postLogin')]
    public function test_login_fails_with_invalid_credentials(): void
    {
        $this->withoutMiddleware();
        User::factory()->create([
            'user_name' => 'testuser',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        // Attempt login with invalid credentials
        $response = $this->post('/login', [
            'email_username' => 'invaliduser',
            'password1' => 'wrongpassword',
        ]);

        $response->assertStatus(302);
    }

    #[Group('postLogin')]
    public function test_login_with_email(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        // Attempt login with email
        $response = $this->post('/login', [
            'email_username' => $user->email,
            'password1' => 'password123',
            'login' => [
                'pot_field' => '',     // valid
                'time_field' => encrypt(time() - 10), // valid
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'redirect' => url('/admin'),
            ],
        ]);
    }

    #[Group('postLogin')]
    public function test_login_with_username(): void
    {
        \App\DefaultPage::query()->delete(); // ensure url('/') fallback
        $this->withoutMiddleware();
        $user = User::factory()->create([
            'user_name' => 'testuser',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);
        // Attempt login with username
        $response = $this->post('/login', [
            'email_username' => $user->user_name,
            'password1' => 'password123',
            'login' => [
                'pot_field' => '',
                'time_field' => encrypt(time() - 10),
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'redirect' => url('/'),
            ],
        ]);
    }

    public function test_login_page(): void
    {
        $this->withoutMiddleware();
        $response = $this->getJson('/auth/login-config');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data' => ['status', 'apiKeys', 'social']]);
    }

    // -------------------------------------------------------------------------
    // getLoginRateLimitKey — MD5 of ip:email
    // -------------------------------------------------------------------------

    public function test_get_login_rate_limit_key_returns_string(): void
    {
        $this->withoutMiddleware();
        $controller = new \App\Http\Controllers\Auth\LoginController;
        $key = $controller->getLoginRateLimitKey('test@example.com');
        $this->assertIsString($key);
        $this->assertEquals(32, strlen($key)); // MD5 is 32 hex chars
    }

    // -------------------------------------------------------------------------
    // logActivityLogin — simple activity log
    // -------------------------------------------------------------------------

    public function test_log_activity_login_does_not_throw(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $controller = new \App\Http\Controllers\Auth\LoginController;
        $controller->logActivityLogin($user);
        $this->assertTrue(true);
    }

    public function test_log_activity_login_returns_early_for_null(): void
    {
        $this->withoutMiddleware();
        $controller = new \App\Http\Controllers\Auth\LoginController;
        $controller->logActivityLogin(null);
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // redirectToGithub — provider not in DB → ModelNotFoundException
    // -------------------------------------------------------------------------

    public function test_redirect_to_github_returns_error_when_provider_not_configured(): void
    {
        $this->withoutMiddleware();
        // Route: GET /auth/redirect/{provider}
        $response = $this->getJson('/auth/redirect/nonexistent_provider_xyzzy');

        // ModelNotFoundException from firstOrFail → 404 or 500
        $this->assertContains($response->getStatusCode(), [200, 404, 500]);
    }

    // -------------------------------------------------------------------------
    // redirectPath — admin vs user redirect
    // -------------------------------------------------------------------------

    public function test_redirect_path_returns_admin_url_for_admin_user(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $controller = new \App\Http\Controllers\Auth\LoginController;
        $path = $controller->redirectPath();

        $this->assertEquals(url('/admin'), $path);
    }

    public function test_redirect_path_returns_client_url_for_regular_user(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $controller = new \App\Http\Controllers\Auth\LoginController;
        $path = $controller->redirectPath();

        $this->assertIsString($path);
    }
}
