<?php

namespace Tests\Unit\Backend\Http\Controllers\Auth;

use App\DefaultPage;
use App\Http\Controllers\Auth\LoginController;
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
        DefaultPage::query()->delete(); // ensure url('/') fallback, not DB page_url
        $user = User::factory()->create(['password' => Hash::make('password')]);
        StatusSetting::create(['emailverification_status' => 0, 'msg91_status' => 0, 'recaptcha_status' => 0]);
        $this->withoutMiddleware();
        $response = $this->postJson('/login', ['login' => $user->email, 'password' => 'password', 'honeypot' => [
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
        $response = $this->postJson('/login', ['login' => $user->email, 'password' => 'password', 'honeypot' => [
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
            ['msg91_status' => 1, 'mobileverification_status' => 1, 'emailverification_status' => 0]
        );
        $this->withoutMiddleware();
        $response = $this->postJson('/login', ['login' => $user->email, 'password' => 'password', 'honeypot' => [
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
        $response = $this->postJson('/login', ['login' => $user->email, 'password' => 'password', 'honeypot' => [
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
            ['emailverification_status' => 1, 'msg91_status' => 1, 'mobileverification_status' => 1]
        );
        VerificationAttempt::create(['user_id' => $user->id, 'mobile_attempt' => 2, 'email_attempt' => 3]);
        $response = $this->postJson('/login', ['login' => $user->email, 'password' => 'password',  'honeypot' => [
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
        $response = $this->postJson('/login', ['login' => 'santhanuchakrapa@gmail.com', 'password' => 'password', 'honeypot' => [
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
        $response = $this->postJson('/login', ['login' => $user->email, 'password' => 'passwor', 'honeypot' => [
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
            'login' => $user->email,
            'password' => 'password',
            'honeypot' => [
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
            'login' => 'user@example.com',
            'password' => 'password',
            'g-recaptcha-response' => 'dummy-token',
            'honeypot' => [
                'pot_field' => 'asdfghjk',     // valid
                'time_field' => encrypt(time() - 10), // valid
            ],
        ]);

        $response->assertStatus(412);
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
            'login' => 'user@example.com',
            'password' => 'password',
            // this is a honeypot field, it should be empty
            'honeypot' => [
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
        DefaultPage::query()->delete(); // ensure url('/') fallback
        $user = User::factory()->create(['active' => 1, 'email' => 'user@example.com', 'password' => bcrypt('password')]);
        StatusSetting::create(['emailverification_status' => 0, 'msg91_status' => 0, 'recaptcha_status' => 0]);
        $this->withoutMiddleware();

        $response = $this->postJson('/login', [
            'login' => 'user@example.com',
            'password' => 'password',
            'g-recaptcha-response' => 'test-bypass',
            'honeypot' => [
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
        // Missing the `login` honeypot field fails LoginRequest validation first
        // (RequestJsonValidation always returns JSON, even for a plain non-JSON POST)
        // before the controller ever checks the credentials.
        $response = $this->post('/login', [
            'login' => 'invaliduser',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(412);
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
            'login' => $user->email,
            'password' => 'password123',
            'honeypot' => [
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
        DefaultPage::query()->delete(); // ensure url('/') fallback
        $this->withoutMiddleware();
        $user = User::factory()->create([
            'user_name' => 'testuser',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);
        // Attempt login with username
        $response = $this->post('/login', [
            'login' => $user->user_name,
            'password' => 'password123',
            'honeypot' => [
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

    #[Group('postLogin')]
    public function test_login_with_email_shaped_username_after_email_changed(): void
    {
        DefaultPage::query()->delete(); // ensure url('/') fallback
        $this->withoutMiddleware();
        // Username was copied from the original email; the email was changed later.
        User::factory()->create([
            'user_name' => 'old-address@example.com',
            'email' => 'new-address@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        $response = $this->post('/login', [
            'login' => 'old-address@example.com',
            'password' => 'password123',
            'honeypot' => [
                'pot_field' => '',
                'time_field' => encrypt(time() - 10),
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'data' => ['redirect' => url('/')]]);
    }

    public function test_login_page(): void
    {
        $this->withoutMiddleware();
        $response = $this->getJson('/auth/login-config');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data' => ['status', 'apiKeys', 'social']]);
    }

    public function test_login_config_has_no_location_lookup(): void
    {
        $this->withoutMiddleware();
        $this->getJson('/auth/login-config')->assertOk()->assertJsonMissingPath('data.location');
    }

    // -------------------------------------------------------------------------
    // getLoginRateLimitKey — MD5 of ip:email
    // -------------------------------------------------------------------------

    public function test_get_login_rate_limit_key_returns_string(): void
    {
        $this->withoutMiddleware();
        $controller = new LoginController;
        $key = $controller->getLoginRateLimitKey('test@example.com');
        $this->assertIsString($key);
        $this->assertEquals(32, strlen($key)); // MD5 is 32 hex chars
    }

    // -------------------------------------------------------------------------
    // Login event: activity log + no early login before verify/2FA
    // -------------------------------------------------------------------------

    public function test_completed_login_is_logged_through_the_login_event(): void
    {
        $user = User::factory()->create();
        $before = \DB::table('activity_log')->where('event', 'login')->count();

        auth()->login($user); // any login path: password, 2FA, social

        $this->assertSame($before + 1, \DB::table('activity_log')->where('event', 'login')->count());
    }

    public function test_2fa_user_is_not_signed_in_before_the_2fa_step(): void
    {
        $this->withoutMiddleware();
        \Illuminate\Support\Facades\Event::fake([\Illuminate\Auth\Events\Login::class]);
        StatusSetting::query()->update(['emailverification_status' => 0, 'msg91_status' => 0]);
        $user = User::factory()->create(['is_2fa_enabled' => 1, 'active' => 1, 'password' => \Hash::make('Secret@123')]);

        $this->postJson('/login', ['login' => $user->email, 'password' => 'Secret@123', 'honeypot' => ['pot_field' => '', 'time_field' => encrypt(time() - 10)]])
            ->assertJsonPath('data.redirect', url('verify-2fa'));

        $this->assertGuest();
        \Illuminate\Support\Facades\Event::assertNotDispatched(\Illuminate\Auth\Events\Login::class); // no cart merge / log yet
    }

    public function test_user_locked_out_of_verification_is_stopped_at_login(): void
    {
        $this->withoutMiddleware();
        StatusSetting::updateOrCreate(['id' => 1], ['msg91_status' => 1, 'mobileverification_status' => 1, 'emailverification_status' => 0]);
        $user = User::factory()->create(['mobile_verified' => 0, 'password' => Hash::make('password')]);
        foreach (range(1, 3) as $_) {
            \RateLimiter::hit('mobile-verify:'.$user->id, 600); // 3 wrong codes = locked
        }

        $this->postJson('/login', ['login' => $user->email, 'password' => 'password', 'honeypot' => ['pot_field' => '', 'time_field' => encrypt(time() - 10)]])
            ->assertStatus(429)
            ->assertJsonPath('success', false);

        // Not sent on to /verify: no verification session was started.
        $this->assertNull(session('verification_user_id'));
    }

    // -------------------------------------------------------------------------
    // social redirect — unknown / disabled provider → clean error
    // -------------------------------------------------------------------------

    public function test_redirect_to_github_returns_error_when_provider_not_configured(): void
    {
        $this->withoutMiddleware();
        // Route: GET /auth/redirect/{provider}
        $response = $this->getJson('/auth/redirect/nonexistent_provider_xyzzy');

        $response->assertStatus(400);
    }

    // -------------------------------------------------------------------------
    // redirectPath — admin vs user redirect
    // -------------------------------------------------------------------------

    public function test_redirect_path_returns_admin_url_for_admin_user(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $controller = new LoginController;
        $path = $controller->redirectPath();

        $this->assertEquals(url('/admin'), $path);
    }

    public function test_redirect_path_returns_client_url_for_regular_user(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $controller = new LoginController;
        $path = $controller->redirectPath();

        $this->assertIsString($path);
    }

    // -------------------------------------------------------------------------
    // Account-wide failed-login limit (any IP)
    // -------------------------------------------------------------------------

    private function loginFrom(string $ip, string $login, string $password)
    {
        return $this->withoutMiddleware(\App\Http\Middleware\Install::class)
            ->withServerVariables(['REMOTE_ADDR' => $ip])->postJson('/login', [
            'login' => $login, 'password' => $password,
            'honeypot' => ['pot_field' => '', 'time_field' => encrypt(time() - 10)],
        ]);
    }

    public function test_failures_spread_over_many_ips_lock_the_account(): void
    {
        StatusSetting::query()->update(['emailverification_status' => 0, 'msg91_status' => 0]);
        $user = User::factory()->create(['active' => 1, 'is_2fa_enabled' => 0, 'password' => \Hash::make('Right@Pass1')]);

        foreach (range(1, 10) as $i) {
            $this->loginFrom("10.0.0.$i", $user->email, 'wrong')->assertStatus(400);
        }

        // Even the right password, from a fresh IP, is refused while the account is locked.
        $this->loginFrom('10.0.1.1', $user->email, 'Right@Pass1')
            ->assertStatus(429)
            ->assertJsonPath('data.retry_after', fn ($s) => $s > 0 && $s <= 3600);
        $this->assertGuest();
    }

    public function test_a_successful_login_clears_the_account_counter(): void
    {
        StatusSetting::query()->update(['emailverification_status' => 0, 'msg91_status' => 0]);
        $user = User::factory()->create(['active' => 1, 'is_2fa_enabled' => 0, 'role' => 'user', 'password' => \Hash::make('Right@Pass1')]);

        foreach (range(1, 4) as $i) {
            $this->loginFrom("10.0.2.$i", $user->email, 'wrong');
        }
        $this->loginFrom('10.0.2.9', $user->email, 'Right@Pass1')->assertOk();

        $this->assertSame(0, \RateLimiter::attempts('login-account:'.md5(strtolower($user->email))));
    }
}

