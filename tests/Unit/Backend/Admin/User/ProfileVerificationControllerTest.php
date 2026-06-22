<?php

namespace Tests\Unit\Backend\Admin\User;

use App\Http\Controllers\Common\Sms\SmsOtpController;
use App\Http\Controllers\Front\ProfileVerificationController;
use App\Model\Common\StatusSetting;
use App\Model\User\AccountActivate;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\DBTestCase;

class ProfileVerificationControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected $profileVerificationController;

    protected function setUp(): void
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
    public function test_user_can_change_email_after_verification(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'old@example.com',
            'email_verified' => 0,
        ]);

        $this->actingAs($user);

        $newEmail = 'new@example.com';

        // Send OTP to new email
        $this->postJson('/profile/email/send-otp', [
            'email_to_verify' => $newEmail,
        ])->assertStatus(200);

        // OTP should be stored in DB
        $record = AccountActivate::where('email', $newEmail)->first();
        $this->assertNotNull($record, 'OTP record not created');
        $otp = $record->token;

        // Verify OTP — should auto-update email
        $this->postJson('/profile/email/verify-otp', [
            'email_to_verify' => $newEmail,
            'otp' => $otp,
            'verify_type' => 'new_email',
        ])->assertStatus(200)
            ->assertJson([
                'message' => __('message.new_email_updated'),
            ])
            ->assertJsonPath('data.email_updated', expect: true)
            ->assertJsonPath('data.email', $newEmail);

        // Assert DB updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $newEmail,
        ]);
    }

    /**
     * Test wrong OTP for new email verification.
     */
    public function test_wrong_otp_for_new_email_verification(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'old@example.com',
            'email_verified' => 0,
        ]);

        $this->actingAs($user);

        $newEmail = 'new@example.com';

        // Send OTP
        $this->postJson('/profile/email/send-otp', [
            'email_to_verify' => $newEmail,
        ])->assertStatus(200);

        $record = AccountActivate::where('email', $newEmail)->first();
        $this->assertNotNull($record, 'OTP record not created');

        // Verify with wrong OTP
        $this->postJson('/profile/email/verify-otp', [
            'email_to_verify' => $newEmail,
            'otp' => '192922',
        ])->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => __('message.email_verification.invalid_token'),
            ]);
    }

    /**
     * Test wrong OTP for old email verification.
     */
    public function test_wrong_otp_for_old_email_verification(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'old@example.com',
            'email_verified' => 0,
        ]);

        $this->actingAs($user);

        $newEmail = 'new@example.com';

        // Send OTP
        $this->postJson('/profile/email/send-otp', [
            'email_to_verify' => $newEmail,
        ])->assertStatus(200);

        $record = AccountActivate::where('email', $newEmail)->first();
        $this->assertNotNull($record, 'OTP record not created');

        // First wrong OTP attempt
        $this->postJson('/profile/email/verify-otp', [
            'email_to_verify' => $newEmail,
            'otp' => '911111',
        ])->assertStatus(400)
            ->assertJson([
                'message' => __('message.email_verification.invalid_token'),
            ]);

        // Second wrong OTP attempt
        $this->postJson('/profile/email/verify-otp', [
            'email_to_verify' => $newEmail,
            'otp' => '192922',
        ])->assertStatus(400);
    }

    /**
     * Test mobile number change after verification.
     */
    public function test_user_can_change_mobile_after_verification(): void
    {
        Mail::fake();

        StatusSetting::updateOrCreate(
            ['id' => 1],
            [
                'emailverification_status' => 1,
                'msg91_status' => 1,
                'recaptcha_status' => 0,
            ]
        );

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

        $newMobile = '8123456789';
        $dialCode = '91';
        $countryIso = 'IN';

        // Step 1: Send mobile OTP
        $this->postJson('/profile/mobile/send-otp', [
            'mobile_to_verify' => $newMobile,
            'dial_code' => $dialCode,
            'country_iso' => $countryIso,
        ])->assertStatus(200);

        // Step 2: Verify mobile OTP — stores verified mobile in session
        $this->postJson('/profile/mobile/verify-otp', [
            'mobile_to_verify' => $newMobile,
            'otp' => '123456',
            'new_mobile' => $newMobile,
            'dial_code' => $dialCode,
            'country_iso' => $countryIso,
        ])->assertStatus(200)
            ->assertJsonPath('data.email_verification_required', expect: true);

        // Step 3: Send email OTP for confirmation
        $this->postJson('/profile/email/send-otp', [
            'email_to_verify' => $user->email,
        ])->assertStatus(200);

        $record = AccountActivate::where('email', $user->email)->first();
        $this->assertNotNull($record, 'OTP record not created');
        $otp = $record->token;

        // Step 4: Verify email OTP — mobile data comes from session, not request
        $this->postJson('/profile/email/verify-otp', [
            'email_to_verify' => $user->email,
            'otp' => $otp,
            'verify_type' => 'mobile_email',
        ])->assertStatus(200)
            ->assertJson([
                'message' => __('message.new_mobile_no_updated'),
            ])
            ->assertJsonPath('data.mobile_updated', expect: true);
    }

    /**
     * Test mobile_email verify fails without prior mobile OTP verification.
     */
    public function test_mobile_email_verify_fails_without_mobile_otp(): void
    {
        Mail::fake();

        $user = $this->createUser([
            'mobile_verified' => true,
            'mobile' => 9123456789,
            'mobile_country_iso' => 'IN',
            'mobile_code' => 91,
        ]);

        $this->actingAs($user);

        // Send email OTP
        $this->postJson('/profile/email/send-otp', [
            'email_to_verify' => $user->email,
        ])->assertStatus(200);

        $record = AccountActivate::where('email', $user->email)->first();
        $this->assertNotNull($record, 'OTP record not created');
        $otp = $record->token;

        // Try to verify with mobile_email type without prior mobile OTP — should fail
        $this->postJson('/profile/email/verify-otp', [
            'email_to_verify' => $user->email,
            'otp' => $otp,
            'verify_type' => 'mobile_email',
        ])->assertStatus(400)
            ->assertJson([
                'message' => __('message.mobile_verification_required'),
            ]);
    }

    /**
     * Test wrong OTP for new email (alternate scenario).
     */
    public function test_wrong_otp_rejected_for_new_email(): void
    {
        Mail::fake();

        $user = $this->createUser(['email_verified' => 0]);
        $this->actingAs($user);

        $newEmail = 'new@example.com';

        $this->postJson('/profile/email/send-otp', [
            'email_to_verify' => $newEmail,
        ])->assertStatus(200);

        $record = AccountActivate::where('email', $newEmail)->first();
        $this->assertNotNull($record, 'OTP record not created');

        $this->postJson('/profile/email/verify-otp', [
            'email_to_verify' => $newEmail,
            'otp' => '999999',
        ])->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => __('message.email_verification.invalid_token'),
            ]);
    }

    /**
     * Test duplicate email is rejected during send OTP.
     */
    public function test_duplicate_email_rejected_on_send_otp(): void
    {
        $user = $this->createUser([
            'email' => 'old@example.com',
            'email_verified' => 0,
        ]);

        $this->actingAs($user);

        $this->postJson('/profile/email/send-otp', [
            'email_to_verify' => $user->email,
            'new_email' => $user->email,
        ])->assertStatus(400)
            ->assertJson([
                'message' => __('message.email_already_used'),
            ]);
    }

    /**
     * Test duplicate mobile number is rejected during send OTP.
     */
    public function test_duplicate_mobile_rejected_on_send_otp(): void
    {
        $user = $this->createUser([
            'mobile' => '8123456789',
            'mobile_code' => '91',
            'mobile_country_iso' => 'IN',
        ]);

        $this->actingAs($user);

        $this->postJson('/profile/mobile/send-otp', [
            'mobile_to_verify' => $user->mobile,
            'dial_code' => $user->mobile_code,
            'country_iso' => $user->mobile_country_iso,
        ])->assertStatus(400)
            ->assertJson([
                'message' => __('message.mobile_no_already_used'),
            ]);
    }

    /**
     * Test invalid verify_type is rejected.
     */
    public function test_invalid_verify_type_rejected(): void
    {
        Mail::fake();

        $user = $this->createUser(['email_verified' => 0]);
        $this->actingAs($user);

        $newEmail = 'new@example.com';

        $this->postJson('/profile/email/send-otp', [
            'email_to_verify' => $newEmail,
        ])->assertStatus(200);

        $record = AccountActivate::where('email', $newEmail)->first();
        $this->assertNotNull($record, 'OTP record not created');

        // Send invalid verify_type — should be rejected by validation
        $this->postJson('/profile/email/verify-otp', [
            'email_to_verify' => $newEmail,
            'otp' => $record->token,
            'verify_type' => 'hacked_type',
        ])->assertStatus(422);
    }
}
