<?php

namespace Tests\Unit;

use App\Http\Controllers\Auth\LoginController;
use App\Model\Common\StatusSetting;
use App\User;
use App\VerificationAttempt;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Activitylog\ActivityLogger;
use Tests\DBTestCase;
use Illuminate\Http\Request;
class LoginTest extends DBTestCase
{
    use DatabaseTransactions;

    /** @group postLogin */
    public function test_postLogin_forVerifiedUsers()
    {
        $user = User::factory()->create(['password'=>\Hash::make('password')]);
        $setting = StatusSetting::create(['emailverification_status'=>0, 'msg91_status'=>0,'v3_recaptcha_status'=>0,'recaptcha_status'=>0,'v3_v2_recaptcha_status'=>0]);
        $this->withoutMiddleware();
        $response = $this->call('POST', 'login', ['email_username' => $user->email, 'password1' => 'password']);
        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    /** @group postLogin */
    public function test_postLogin_forAdmin()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->withoutMiddleware();
        $response = $this->call('POST', 'login', ['email1' => $user->email, 'password1' => 'password']);
        $this->assertStringContainsSubstring($response->getTargetUrl(), '/');
    }

    /** @group postLogin */
    public function test_postLogin_when_mobile_is_Unverified()
    {
        $user = User::factory()->create(['mobile_verified' => 0]);
        $response = $this->call('POST', 'login', ['email1' => $user->email, 'password1' => 'password']);
        $response->assertStatus(302);
        // $this->assertStringContainsSubstring($response->getTargetUrl(), '/verify');
    }

    /** @group postLogin */
    public function test_postLogin_when_email_is_Unverified()
    {
        $user = User::factory()->create(['active' => 0]);
        $response = $this->call('POST', 'login', ['email1' => $user->email, 'password1' => 'password']);
        $response->assertStatus(302);
    }

    /** @group postLogin */
    public function test_postLogin_when_email_and_mobile_are_Unverified()
    {
        $user = User::factory()->create(['password' => \Hash::make('password'), 'email_verified' => 0, 'mobile_verified' => 0]);
        $this->withoutMiddleware();
        $setting = StatusSetting::first(['emailverification_status', 'msg91_status', 'id']);
        if (!$setting){
            $setting = StatusSetting::create(['id' => 1, 'emailverification_status' => 1, 'msg91_status' => 1, 'v3_recaptcha_status' => 0, 'recaptcha_status' => 0, 'v3_v2_recaptcha_status' => 0]);
        }else{
            $setting->update(['emailverification_status' => 1,'msg91_status' => 1]);
        }
        $attempts=VerificationAttempt::create(['user_id'=>$user->id,'mobile_attempt'=>2,'email_attempt'=>3]);
        $response = $this->call('POST', 'login', ['email_username' => $user->email, 'password1' => 'password']);
        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    public function test_login_should_fail_when_the_user_not_present(){
        $user = User::factory()->create(['password'=>\Hash::make('password')]);
        $setting = StatusSetting::create(['emailverification_status'=>1, 'msg91_status'=>0,'v3_recaptcha_status'=>0,'recaptcha_status'=>0,'v3_v2_recaptcha_status'=>0]);
        $this->withoutMiddleware();
        $response = $this->call('POST', 'login', ['email_username' => 'santhanuchakrapa@gmail.com', 'password1' => 'password']);
        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'login' => 'Please Enter a valid Email',
        ]);
        $this->assertTrue(session()->hasOldInput('email_username'));
    }


    public function test_login_fails_when_password_is_wrong(){
        $user = User::factory()->create(['password'=>\Hash::make('password')]);
        $setting = StatusSetting::create(['emailverification_status'=>0, 'msg91_status'=>0,'v3_recaptcha_status'=>0,'recaptcha_status'=>0,'v3_v2_recaptcha_status'=>0]);
        $this->withoutMiddleware();
        $response = $this->call('POST', 'login', ['email_username' => $user->email, 'password1' => 'passwor']);
        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'password' => 'Please Enter a valid Password',
        ]);
    }

    public function test_when_2fa_is_enabled(){
        $user = User::factory()->create(['password'=>\Hash::make('password'),'is_2fa_enabled'=>1]);
        $setting = StatusSetting::create(['emailverification_status'=>0, 'msg91_status'=>0,'v3_recaptcha_status'=>0,'recaptcha_status'=>0,'v3_v2_recaptcha_status'=>0]);
        $this->withoutMiddleware();
        $request = Request::create('/login', 'POST', [
            'email_username' => $user->email,
            'password1' => 'password',
        ]);
        $request->setLaravelSession(app('session')->driver());
        $controller = new LoginController();
        $response = $controller->login($request);
        $this->assertEquals($user->id, session('2fa:user:id'));
    }

}
