<?php

namespace Tests\Unit;

use App\Http\Middleware\Install;
use App\Model\Common\StatusSetting;
use App\User;
use App\VerificationAttempt;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class LoginTest extends DBTestCase
{
    use DatabaseTransactions;

    #[Group('postLogin')]
    public function test_postLogin_forVerifiedUsers()
    {
        $user = User::factory()->create(['password' => \Hash::make('password')]);
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
                'redirect' => url('/client-dashboard'),
            ],
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Group('postLogin')]
    public function test_postLogin_forAdmin()
    {
        $user = User::factory()->create(['role' => 'admin', 'password' => \Hash::make('password')]);
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
    public function test_postLogin_when_mobile_is_Unverified()
    {
        $user = User::factory()->create(['mobile_verified' => 0, 'password' => \Hash::make('password')]);
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
    public function test_postLogin_when_email_is_Unverified()
    {
        $user = User::factory()->create(['email_verified' => 0, 'password' => \Hash::make('password')]);
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
    public function test_postLogin_when_email_and_mobile_are_Unverified()
    {
        $user = User::factory()->create(['password' => \Hash::make('password'), 'email_verified' => 0, 'mobile_verified' => 0]);
        $this->withoutMiddleware();
        StatusSetting::updateOrCreate(
            ['id' => 1],
            ['emailverification_status' => 1, 'msg91_status' => 1]
        );
        $attempts = VerificationAttempt::create(['user_id' => $user->id, 'mobile_attempt' => 2, 'email_attempt' => 3]);
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

    public function test_login_should_fail_when_the_user_not_present()
    {
        User::factory()->create(['password' => \Hash::make('password')]);
        StatusSetting::create(['emailverification_status' => 1, 'msg91_status' => 0, 'v3_recaptcha_status' => 0, 'recaptcha_status' => 0, 'v3_v2_recaptcha_status' => 0]);
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

    public function test_login_fails_when_password_is_wrong()
    {
        $user = User::factory()->create(['password' => \Hash::make('password')]);
        StatusSetting::create(['emailverification_status' => 0, 'msg91_status' => 0, 'v3_recaptcha_status' => 0, 'recaptcha_status' => 0, 'v3_v2_recaptcha_status' => 0]);
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

    public function test_when_2fa_is_enabled()
    {
        $user = User::factory()->create(['password' => \Hash::make('password'), 'is_2fa_enabled' => 1]);
        StatusSetting::create(['emailverification_status' => 0, 'msg91_status' => 0, 'v3_recaptcha_status' => 0, 'recaptcha_status' => 0, 'v3_v2_recaptcha_status' => 0]);
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
    public function test_it_fails_when_honeypot_field_is_filled()
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
    public function test_it_fails_when_recaptcha_is_missing()
    {
        StatusSetting::updateOrCreate(
            ['id' => 1],
            [
                'recaptcha_status' => 1,
            ]
        );

        $this->withoutMiddleware([Install::class]);

        $response = $this->postJson('/login', [
            'email_username' => 'user@example.com',
            'password1' => 'password',
            //this is a honeypot field, it should be empty
            'login' => [
                'pot_field' => '',     // valid
                'time_field' => encrypt(time() - 10), // valid
            ],
            // missing g-recaptcha-response
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => __('message.captcha_message'),
        ]);
    }

    #[Group('postLogin')]
    public function test_it_succeeds_with_valid_input_and_no_honeypot()
    {
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
                'redirect' => url('/client-dashboard'),
            ],
        ]);
    }
}
