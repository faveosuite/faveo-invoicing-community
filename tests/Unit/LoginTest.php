<?php

namespace Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class LoginTest extends DBTestCase
{
    use DatabaseTransactions;

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
}
