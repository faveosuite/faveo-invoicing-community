<?php

namespace Tests\Unit\Auth;

use App\ApiKey;
use App\Http\Controllers\Auth\AuthController;
use App\Model\User\AccountActivate;
use App\User;
use App\VerificationAttempt;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Mockery;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;
    protected $authController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->authController = Mockery::mock(AuthController::class)->makePartial();
        Http::fake([
            'api.msg91.com/*' => Http::response(['type' => 'success', 'message' => 'success']),
        ]);
    }

    /**
     * Set up a mock API key.
     */
    protected function mockApiKeys(): void
    {
        ApiKey::updateOrCreate(
            ['id' => 1],
            [
                'msg91_auth_key' => 'test_auth',
                'msg91_sender' => 'test_sender',
                'msg91_template_id' => 'test_template',
            ]
        );
    }

    /**
     * Create a user with optional attributes.
     */
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    #[Test]
    public function it_requests_otp_for_verified_user()
    {
        $user = $this->createUser(['mobile_verified' => 0]);

        $this->mockApiKeys();

        $request = new Request(['eid' => Crypt::encrypt($user->email)]);

        $response = json_decode($this->authController->requestOtp($request)->getContent());

        $this->assertEquals(__('message.otp_verification.send_success'), $response->message);
    }

    #[Test]
    public function it_verifies_otp_successfully()
    {
        $user = $this->createUser(['mobile_verified' => false]);
        Session::flash('user', $user);

        $request = new Request([
            'eid' => Crypt::encrypt($user->email),
            'otp' => '123456',
        ]);

        // Assume OTP verification succeeds
        $this->authController->shouldReceive('sendVerifyOTP')
            ->once()
            ->andReturn(true);

        $response = json_decode($this->authController->verifyOtp($request)->getContent());

        $this->assertEquals(__('message.otp_verified'), $response->message);
    }

    #[Test]
    public function it_fails_to_verify_invalid_otp()
    {
        $user = $this->createUser(['mobile_verified' => false]);
        Session::flash('user', $user);

        $request = new Request([
            'eid' => Crypt::encrypt($user->email),
            'otp' => '123456',
        ]);

        $this->authController->shouldReceive('sendVerifyOTP')
            ->once()
            ->andReturn(false);

        $response = json_decode($this->authController->verifyOtp($request)->getContent());

        $this->assertEquals(__('message.otp_invalid'), $response->message);
    }

    #[Test]
    public function it_sends_email_successfully()
    {
        $user = User::factory()->create();
        $request = new Request(['eid' => Crypt::encrypt($user->email)]);

        // Mock sendActivation method
        $this->authController->shouldReceive('sendActivation')
            ->once()
            ->with($user->email, 'POST')
            ->andReturn(true);

        $response = json_decode($this->authController->sendEmail($request)->getContent());

        $this->assertEquals(__('message.email_verification.send_success'), $response->message);
    }

    #[Test]
    public function it_handles_maximum_email_verification_attempts()
    {
        $user = User::factory()->create();
        VerificationAttempt::create([
            'user_id' => $user->id,
            'email_attempt' => 3,
            'updated_at' => Carbon::now(),
        ]);

        $request = new Request(['eid' => Crypt::encrypt($user->email)]);

        $response = json_decode($this->authController->sendEmail($request)->getContent());

        $expectedMessage = __('message.email_verification.max_attempts_exceeded', ['time' => '5 hours 59 minutes']);
        $this->assertEquals($expectedMessage, $response->message);
    }

    #[Test]
    public function it_does_not_send_email_for_nonexistent_user()
    {
        $request = new Request(['eid' => Crypt::encrypt('nonexistent@example.com')]);

        $response = json_decode($this->authController->sendEmail($request)->getContent());

        $this->assertEquals(__('message.email_verification.send_failure'), $response->message);
    }

    #[Test]
    public function it_does_not_send_email_if_already_sent()
    {
        $user = User::factory()->create();
        AccountActivate::create(['email' => $user->email]);

        $request = new Request(['eid' => Crypt::encrypt($user->email)]);

        $this->authController->shouldReceive('sendActivation')->never();

        $response = json_decode($this->authController->sendEmail($request)->getContent());

        $this->assertEquals(__('message.email_verification.already_sent'), $response->message);
    }

    #[Test]
    public function it_sends_resend_success_message_for_get_requests()
    {
        $user = User::factory()->create();
        $request = new Request(['eid' => Crypt::encrypt($user->email)]);

        $this->authController->shouldReceive('sendActivation')
            ->once()
            ->with($user->email, 'GET')
            ->andReturn(true);

        $response = json_decode($this->authController->sendEmail($request, 'GET')->getContent());

        $this->assertEquals(__('message.email_verification.resend_success'), $response->message);
    }

    #[Test]
    public function it_handles_exceptions_gracefully()
    {
        $request = new Request(['eid' => 'invalid_encrypted_value']);

        $this->authController->shouldReceive('sendActivation')->never();

        $response = json_decode($this->authController->sendEmail($request)->getContent());

        $this->assertEquals(__('message.email_verification.send_failure'), $response->message);
    }
}
