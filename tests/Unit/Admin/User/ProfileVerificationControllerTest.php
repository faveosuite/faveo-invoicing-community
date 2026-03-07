<?php

namespace Tests\Unit\Admin\User;

use App\Http\Controllers\Common\Sms\SmsOtpController;
use App\Http\Controllers\Front\ProfileVerificationController;
use App\Model\User\AccountActivate;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\DBTestCase;

class ProfileVerificationControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected $profileVerificationController;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
        $this->profileVerificationController = Mockery::mock(ProfileVerificationController::class)->makePartial();
        $this->app->instance(ProfileVerificationController::class, $this->profileVerificationController);
    }

    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /**
     * Test user can change email after verification.
     */
    public function test_user_can_change_email_after_verification()
    {
        Mail::fake();

        //Create & authenticate user
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'email_verified' => 0,
        ]);

        $this->actingAs($user);

        $newEmail = 'new@example.com';

        // Request OTP
        $this->postJson('/emailUpdateEditProfile', [
            'email_to_verify' => $newEmail,
        ])->assertStatus(200);

        // OTP should be stored in DB
        $record = AccountActivate::where('email', $newEmail)->first();
        $this->assertNotNull($record, 'OTP record not created');
        $otp = $record->token;

        // Verify OTP
        $this->postJson('/otpVerifyForNewEmail', [
            'email_to_verify' => $newEmail,
            'otp' => $otp,
        ])->assertStatus(200)
            ->assertJson([
                'message' => __('message.email_verification.email_verified'),
            ]);

        // Change email
        $this->postJson('/user/change-email', [
            'newEmail' => $newEmail,
        ])->assertStatus(200)
            ->assertJson([
                'message' => __('message.new_email_updated'),
            ]);

        // Assert DB updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $newEmail,
        ]);
    }

    /**
     * Check wrong OTP case for old email.
     */
    public function testCheckwithWrognOtpCodeForNewEmailVerification()
    {
        Mail::fake();

        // Create & authenticate user
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'email_verified' => 0,
        ]);

        $this->actingAs($user);

        $newEmail = 'new@example.com';

        // Request OTP
        $this->postJson('/emailUpdateEditProfile', [
            'email_to_verify' => $newEmail,
        ])->assertStatus(200);

        // OTP should be stored in DB
        $record = AccountActivate::where('email', $newEmail)->first();
        $this->assertNotNull($record, 'OTP record not created');
        $otp = $record->token;

        // Try verifying with WRONG OTP
        $response = $this->postJson('/otpVerifyForNewEmail', [
            'email_to_verify' => $newEmail,
            'otp' => '192922', // wrong OTP
        ]);

        // Assert the API gives correct error
        $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => __('message.email_verification.invalid_token'),
        ]);
    }

    /**
     * Check wrong OTP case for old email.
     */
    public function testCheckwithWrognOtpCodeForOldEmailVerification()
    {
        Mail::fake();

        // Create & authenticate user
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'email_verified' => 0,
        ]);

        $this->actingAs($user);

        $newEmail = 'new@example.com';

        // Request OTP
        $this->postJson('/emailUpdateEditProfile', [
            'email_to_verify' => $newEmail,
        ])->assertStatus(200);

        // OTP should be stored in DB
        $record = AccountActivate::where('email', $newEmail)->first();
        $this->assertNotNull($record, 'OTP record not created');
        $otp = $record->token;

        // Verify OTP
        $this->postJson('/otpVerifyForNewEmail', [
            'email_to_verify' => $newEmail,
            'otp' => '911111',
        ])->assertStatus(400)
            ->assertJson([
                'message' => __('message.email_verification.invalid_token'),
            ]);

        $response = $this->postJson('/otpVerifyForNewEmail', [
            'email_to_verify' => $newEmail,
            'otp' => '192922', // wrong OTP
        ]);
    }

    /**
     * @return void testChange mobile number after verification
     */
    public function testChangeMobileNoAfterVerifaction()
    {
        Mail::fake();

        $user = $this->createUser([
            'mobile_verified' => true,
            'mobile' => 9123456789,
            'mobile_country_iso' => 'IN',
            'mobile_code' => 91,
        ]);

        $this->actingAs($user);

        // Mock SmsOtpController
        $smsController = Mockery::mock(SmsOtpController::class);
        $smsController->shouldReceive('sendOtp')
            ->once()
            ->andReturn([
                'type' => 'success',
                'message' => 'Request successfully completed',
            ]);
        $smsController->shouldReceive('sendVerifyOTP')
            ->once()
            ->andReturn([
                'type' => 'success',
                'message' => __('message.otp_verified'),
            ]);
        $this->app->instance(SmsOtpController::class, $smsController);

        // Step 1: Send OTP
        $response = $this->postJson('/newMobileNoVerify', [
            'mobile_to_verify' => '8123456789',
            'dial_code' => '91',
            'country_iso' => 'IN',
        ]);
        $response->assertStatus(200);

        // Step 2: Verify OTP
        $verifyResponse = $this->postJson('/verify/newMobileNoOtp', [
            'mobile_to_verify' => '8123456789',
            'otp' => '123456',
        ]);
        $verifyResponse->assertStatus(200);

        // Step 3: Request email OTP
        $this->postJson('/emailUpdateEditProfile', [
            'email_to_verify' => $user->email,
        ])->assertStatus(200);

        $record = AccountActivate::where('email', $user->email)->first();
        $this->assertNotNull($record, 'OTP record not created');
        $otp = $record->token;

        // Step 4: Verify email OTP
        $this->postJson('/otpVerifyForNewEmail', [
            'email_to_verify' => $user->email,
            'otp' => $otp,
        ])->assertStatus(200)
            ->assertJson([
                'message' => __('message.email_verification.email_verified'),
            ]);

        // Step 5: Change mobile number
        $this->postJson('/user/change-mobile-no', [
            'newMobile' => '8123456789',
            'dial_code' => '91',
            'country_iso' => 'IN',
        ])->assertStatus(200)
            ->assertJson([
                'message' => __('message.new_mobile_no_updated'),
            ]);
    }

    /**
     * Check wrong OTP case for new email.
     */
    public function testCheckwithWrongOtpCodeForNewEmailVerification()
    {
        Mail::fake();

        $user = $this->createUser(['email_verified' => 0]);
        $this->actingAs($user);

        $newEmail = 'new@example.com';

        $this->postJson('/emailUpdateEditProfile', [
            'email_to_verify' => $newEmail,
        ])->assertStatus(200);

        $record = AccountActivate::where('email', $newEmail)->first();
        $this->assertNotNull($record, 'OTP record not created');

        $this->postJson('/otpVerifyForNewEmail', [
            'email_to_verify' => $newEmail,
            'otp' => '999999', // wrong OTP
        ])->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => __('message.email_verification.invalid_token'),
            ]);
    }

    /**
     * Check email already exists validation.
     */
    public function testCheckEmailExistOrNot()
    {
        $user = $this->createUser([
            'email' => 'old@example.com',
            'email_verified' => 0,
        ]);

        $response = $this->postJson('/check-email/exist', [
            'email' => $user->email,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => __('message.email_already_used'),
            ]);
    }

    /**
     * Check mobile number already exists validation.
     */
    public function testCheckMobileNoExist()
    {
        $user = $this->createUser([
            'mobile' => '8123456789',
            'mobile_code' => '91',
            'mobile_country_iso' => 'IN',
        ]);

        $response = $this->postJson('/mobileNoexist', [
            'mobile_to_verify' => $user->mobile,
            'dial_code' => $user->mobile_code,
            'country_iso' => $user->mobile_country_iso,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => __('message.mobile_no_already_used'),
            ]);
    }
}
