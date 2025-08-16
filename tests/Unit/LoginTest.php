<?php

namespace Tests\Unit;

use App\ApiKey;
use App\Http\Controllers\Auth\LoginController;
use App\Model\Common\StatusSetting;
use App\User;
use App\VerificationAttempt;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\DBTestCase;

class LoginTest extends DBTestCase
{
    use DatabaseTransactions;

    /**
     * Helper method to mock captcha requirement.
     */
    private function mockCaptchaRequired()
    {
        StatusSetting::updateOrCreate(
            ['id' => 1],
            [
                'v3_v2_recaptcha_status' => 1,
                'v3_recaptcha_status' => 1,
                'recaptcha_status' => 0,
            ]
        );

        ApiKey::updateOrCreate(
            ['id' => 1],
            [
                'nocaptcha_sitekey' => 'test-sitekey',
                'captcha_secretCheck' => 'test-secret',
            ]
        );
    }

    #[Group('postLogin')]
    public function test_postLogin_forVerifiedUsers()
    {
        $user = User::factory()->create(['password' => \Hash::make('password')]);
        $setting = StatusSetting::create(['emailverification_status' => 0, 'msg91_status' => 0, 'v3_recaptcha_status' => 0, 'recaptcha_status' => 0, 'v3_v2_recaptcha_status' => 0]);
        $this->withoutMiddleware();
        $response = $this->call('POST', 'login', ['email_username' => $user->email, 'password1' => 'password', 'login' => [
            'pot_field' => '',     // valid
            'time_field' => encrypt(time() - 10), // valid
        ]]);
        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    #[\PHPUnit\Framework\Attributes\Group('postLogin')]
    public function test_postLogin_forAdmin()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->withoutMiddleware();
        $response = $this->call('POST', 'login', ['email1' => $user->email, 'password1' => 'password', 'login' => [
            'pot_field' => '',     // valid
            'time_field' => encrypt(time() - 10), // valid
        ]]);
        $this->assertStringContainsSubstring($response->getTargetUrl(), '/');
    }

    #[\PHPUnit\Framework\Attributes\Group('postLogin')]
    public function test_postLogin_when_mobile_is_Unverified()
    {
        $user = User::factory()->create(['mobile_verified' => 0]);
        $response = $this->call('POST', 'login', ['email1' => $user->email, 'password1' => 'password', 'login' => [
            'pot_field' => '',     // valid
            'time_field' => encrypt(time() - 10), // valid
        ]]);
        $response->assertStatus(302);
        // $this->assertStringContainsSubstring($response->getTargetUrl(), '/verify');
    }

    #[\PHPUnit\Framework\Attributes\Group('postLogin')]
    public function test_postLogin_when_email_is_Unverified()
    {
        $user = User::factory()->create(['active' => 0]);
        $response = $this->call('POST', 'login', ['email1' => $user->email, 'password1' => 'password', 'login' => [
            'pot_field' => '',     // valid
            'time_field' => encrypt(time() - 10), // valid
        ]]);
        $response->assertStatus(302);
    }

    #[\PHPUnit\Framework\Attributes\Group('postLogin')]
    public function test_postLogin_when_email_and_mobile_are_Unverified()
    {
        $user = User::factory()->create(['password' => \Hash::make('password'), 'email_verified' => 0, 'mobile_verified' => 0]);
        $this->withoutMiddleware();
        $setting = StatusSetting::first(['emailverification_status', 'msg91_status', 'id']);
        if (! $setting) {
            $setting = StatusSetting::create(['id' => 1, 'emailverification_status' => 1, 'msg91_status' => 1, 'v3_recaptcha_status' => 0, 'recaptcha_status' => 0, 'v3_v2_recaptcha_status' => 0]);
        } else {
            $setting->update(['emailverification_status' => 1, 'msg91_status' => 1]);
        }
        $attempts = VerificationAttempt::create(['user_id' => $user->id, 'mobile_attempt' => 2, 'email_attempt' => 3]);
        $response = $this->call('POST', 'login', ['email_username' => $user->email, 'password1' => 'password']);
        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    public function test_login_should_fail_when_the_user_not_present()
    {
        $user = User::factory()->create(['password' => \Hash::make('password')]);
        $setting = StatusSetting::create(['emailverification_status' => 1, 'msg91_status' => 0, 'v3_recaptcha_status' => 0, 'recaptcha_status' => 0, 'v3_v2_recaptcha_status' => 0]);
        $this->withoutMiddleware();
        $response = $this->call('POST', 'login', ['email_username' => 'santhanuchakrapa@gmail.com', 'password1' => 'password', 'login' => [
            'pot_field' => '',     // valid
            'time_field' => encrypt(time() - 10), // valid
        ]]);
        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'login' => 'Login failed, please check email/username and password you entered are correct.',
        ]);
        $this->assertTrue(session()->hasOldInput('email_username'));
    }

    public function test_login_fails_when_password_is_wrong()
    {
        $user = User::factory()->create(['password' => \Hash::make('password')]);
        $setting = StatusSetting::create(['emailverification_status' => 0, 'msg91_status' => 0, 'v3_recaptcha_status' => 0, 'recaptcha_status' => 0, 'v3_v2_recaptcha_status' => 0]);
        $this->withoutMiddleware();
        $response = $this->call('POST', 'login', ['email_username' => $user->email, 'password1' => 'passwor', 'login' => [
            'pot_field' => '',     // valid
            'time_field' => encrypt(time() - 10), // valid
        ]]);
        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'password' => 'Login failed, please check email/username and password you entered are correct.',
        ]);
    }

    public function test_when_2fa_is_enabled()
    {
        $user = User::factory()->create(['password' => \Hash::make('password'), 'is_2fa_enabled' => 1]);
        $setting = StatusSetting::create(['emailverification_status' => 0, 'msg91_status' => 0, 'v3_recaptcha_status' => 0, 'recaptcha_status' => 0, 'v3_v2_recaptcha_status' => 0]);
        $this->withoutMiddleware();
        $request = Request::create('/login', 'POST', [
            'email_username' => $user->email,
            'password1' => 'password',
            'login' => [
                'pot_field' => '',     // valid
                'time_field' => encrypt(time() - 10), // valid
            ],
        ]);
        $request->setLaravelSession(app('session')->driver());
        $controller = new LoginController();
        $response = $controller->login($request);
        $this->assertEquals($user->id, session('2fa:user:id'));
    }

    #[Group('postLogin')]
    public function test_it_fails_when_honeypot_field_is_filled()
    {
        $this->withoutMiddleware();

        $response = $this->post('/login', [
            'email_username' => 'user@example.com',
            'password1' => 'password',
            'g-recaptcha-response' => 'dummy-token',
            'login' => [
                'pot_field' => 'asdfghjk',     // valid
                'time_field' => encrypt(time() - 10), // valid
            ],
        ]);

        $response->assertRedirect(); // Should redirect back
        $response->assertSessionHasErrors('login');
    }

    #[Group('postLogin')]
    public function test_it_fails_when_recaptcha_is_missing()
    {
        $this->mockCaptchaRequired();

        $this->withoutMiddleware();

        $response = $this->post('/login', [
            'email_username' => 'user@example.com',
            'password1' => 'password',
            //this is a honeypot field, it should be empty
            'login' => [
                'pot_field' => '',     // valid
                'time_field' => encrypt(time() - 10), // valid
            ],
            // missing g-recaptcha-response
        ]);

        $response->assertRedirect();

        $response->assertSessionHasErrors('g-recaptcha-response');
    }

    #[Group('postLogin')]
    public function test_it_succeeds_with_valid_input_and_no_honeypot()
    {
        User::factory()->create(['active' => 1, 'email' => 'user@example.com', 'password' => bcrypt('password')]);
        $this->withoutMiddleware();

        $response = $this->post('/login', [
            'email_username' => 'user@example.com',
            'password1' => 'password',
            'g-recaptcha-response' => 'test-bypass',
            'login' => [
                'pot_field' => '',     // valid
                'time_field' => encrypt(time() - 10), // valid
            ],
        ]);

        $response->assertRedirect();

        $this->assertAuthenticated();
    }

    #[Group('postLogin')]
    public function test_login_fails_with_invalid_credentials()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create([
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

    public function test_register_rate_limit_exceeded_redirects()
    {
        $this->withoutMiddleware();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);

        StatusSetting::create([
            'emailverification_status' => 1,
            'msg91_status' => 0,
            'v3_recaptcha_status' => 0,
            'recaptcha_status' => 0,
            'v3_v2_recaptcha_status' => 0
        ]);

        // Make 5 failed login attempts (assuming rate limit is 5)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email_username' => $user->email,
                'password1' => 'wrong-password',
                'login' => [
                    'pot_field' => '',
                    'time_field' => encrypt(time() - 10),
                ],
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->post('/login', [
            'email_username' => $user->email,
            'password1' => 'wrong-password',
            'login' => [
                'pot_field' => '',
                'time_field' => encrypt(time() - 10),
            ],
        ]);
        $response->assertRedirect();
        $response->assertSessionHasErrors();

        // Check if error message contains rate limiting message
        $this->assertTrue(session()->has('errors'));
    }

    public function test_rate_limiting_blocks_correct_password()
    {
        $this->withoutMiddleware();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);

        StatusSetting::create([
            'emailverification_status' => 1,
            'msg91_status' => 0,
            'v3_recaptcha_status' => 0,
            'recaptcha_status' => 0,
            'v3_v2_recaptcha_status' => 0
        ]);

        // Trigger rate limiting with wrong password
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email_username' => $user->email,
                'password1' => 'wrong-password',
                'login' => [
                    'pot_field' => '',
                    'time_field' => encrypt(time() - 10),
                ],
            ]);
        }

        // Try with correct password - should still be blocked
        $response = $this->post('/login', [
            'email_username' => $user->email,
            'password1' => 'password123', // Correct password
            'login' => [
                'pot_field' => '',
                'time_field' => encrypt(time() - 10),
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertGuest(); // User should not be authenticated
    }


    #[Group('postLogin')]
    public function test_login_with_email()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
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

        $response->assertRedirect('/');
        $response->assertSessionHasNoErrors();
    }

    #[Group('postLogin')]
    public function test_login_with_username()
    {
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

        $response->assertRedirect('/client-dashboard');
        $response->assertSessionHasNoErrors();
    }

    public function test_login_rate_limiting_with_username()
    {
        $this->withoutMiddleware();

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'user_name' => 'testuser',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);

        StatusSetting::create([
            'emailverification_status' => 1,
            'msg91_status' => 0,
            'v3_recaptcha_status' => 0,
            'recaptcha_status' => 0,
            'v3_v2_recaptcha_status' => 0
        ]);

        // Make failed attempts with username
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email_username' => $user->user_name,
                'password1' => 'wrong-password',
                'login' => [
                    'pot_field' => '',
                    'time_field' => encrypt(time() - 10),
                ],
            ]);
        }

        // Next attempt should be rate limited
        $response = $this->post('/login', [
            'email_username' => $user->user_name,
            'password1' => 'password123',
            'login' => [
                'pot_field' => '',
                'time_field' => encrypt(time() - 10),
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }
}
