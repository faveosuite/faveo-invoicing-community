<?php

namespace Tests\Unit;

use App\ApiKey;
use App\Model\Common\StatusSetting;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class LoginTest extends DBTestCase
{
    use DatabaseTransactions;

    /**
     * Helper method to mock captcha requirement
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
        $user = User::factory()->create();
        $response = $this->call('POST', 'login', ['email1' => $user->email, 'password1' => 'password']);
        $response->assertStatus(302);
        // $this->assertStringContainsSubstring($response->getTargetUrl(), 'home');
    }

    #[Group('postLogin')]
    public function test_postLogin_forAdmin()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->call('POST', 'login', ['email1' => $user->email, 'password1' => 'password']);
        $this->assertStringContainsSubstring($response->getTargetUrl(), '/');
    }

    #[Group('postLogin')]
    public function test_postLogin_when_mobile_is_Unverified()
    {
        $user = User::factory()->create(['mobile_verified' => 0]);
        $response = $this->call('POST', 'login', ['email1' => $user->email, 'password1' => 'password']);
        $response->assertStatus(302);
        // $this->assertStringContainsSubstring($response->getTargetUrl(), '/verify');
    }

    #[Group('postLogin')]
    public function test_postLogin_when_email_is_Unverified()
    {
        $user = User::factory()->create(['active' => 0]);
        $response = $this->call('POST', 'login', ['email1' => $user->email, 'password1' => 'password']);
        $response->assertStatus(302);
    }

    #[Group('postLogin')]
    public function test_postLogin_when_email_and_mobile_are_Unverified()
    {
        $user = User::factory()->create(['active' => 0, 'mobile_verified' => 0]);
        $response = $this->call('POST', 'login', ['email1' => $user->email, 'password1' => 'password']);
        $response->assertStatus(302);
        // $this->assertStringContainsSubstring($response->getTargetUrl(), '/verify');
    }
    #[Group('postLogin')]
    public function test_it_fails_when_honeypot_field_is_filled()
    {
        $this->withoutMiddleware();

        $response = $this->post('/login', [
            'email_username' => 'user@example.com',
            'password1' => 'password',
            'g-recaptcha-response' => 'dummy-token',
            'login' => 'bot-data', // Honeypot should be empty
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

}
