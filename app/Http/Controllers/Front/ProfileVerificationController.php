<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Auth\BaseAuthController;
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Common\Sms\SmsOtpController;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\User\AccountActivate;
use App\User;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use Log;
use RateLimiter;

class ProfileVerificationController extends BaseAuthController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('blockFailedVerifications:verify,mobile-verify,email-verify,email-verify-new,email-verify-old,email-verify-mobile')->only(['verifyEmailOtp', 'verifyMobileOtp']);
    }

    /**
     * Send verification code for email change.
     * Handles: initial submission (with new_email), sending OTP to old/new email, and resend.
     */
    public function sendEmailOtp(Request $request, mixed $method = 'POST'): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email_to_verify' => ['required', 'email'],
        ], [
            'email_to_verify.required' => __('message.login_validation.email_required'),
            'email_to_verify.email' => __('message.login_validation.email_regex'),
        ]);

        try {
            $email = $request->email_to_verify;
            /** @var \App\User $user */
            $user = auth()->user();

            // Initial email change submission — check existence, then update or send OTP
            if ($request->has('new_email') && $method === 'POST') {
                $request->validate(['new_email' => ['required', 'email']]);

                return $this->handleInitialEmailChange($user, $request->input('new_email'));
            }

            // Send OTP to current email (old email verification or mobile confirmation)
            if ($email === $user->email) {
                $mode = $request->is_mobile ? 'mobile' : 'old_email';
                RateLimiter::hit('email-otp-old:'.$user->id, 600);
                $this->sendActivationOtp($email, $method, $mode);

                return successResponse(__('message.otp_code_sent_exist'));
            }

            // Send OTP to new email (after old email verified)
            return $this->dispatchNewEmailOtp($user, $email, $method);
        } catch (Exception) {
            return errorResponse(__('message.email_verification.send_failure'));
        }
    }

    /**
     * Verify email OTP and apply the profile update (email or mobile).
     */
    public function verifyEmailOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email_to_verify' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
            'verify_type' => ['sometimes', 'string', 'in:new_email,mobile_email,old_email'],
        ], [
            'email_to_verify.required' => __('message.login_validation.email_required'),
            'email_to_verify.email' => __('message.login_validation.email_regex'),
            'otp.required' => __('validation.verify_otp.otp_required'),
            'otp.size' => __('validation.verify_otp.otp_size'),
        ]);

        try {
            $otp = $request->input('otp');
            $email = $request->input('email_to_verify');
            $verifyType = $request->input('verify_type', 'new_email');

            $account = AccountActivate::where('email', $email)->latest()->first(['token', 'updated_at']);

            if (! $account || ! hash_equals((string) $account->token, (string) $otp)) {
                $this->hitVerifyRateLimit($verifyType);

                return errorResponse(__('message.email_verification.invalid_token'));
            }

            if ($account->updated_at?->addMinutes(10)->isPast()) {
                $this->hitVerifyRateLimit($verifyType);

                return errorResponse(__('message.email_verification.token_expired'));
            }

            AccountActivate::where('email', $email)->delete();

            if ($verifyType === 'new_email') {
                return $this->updateUserEmail($email);
            }

            if ($verifyType === 'mobile_email') {
                $verifiedMobile = session('verified_mobile');

                if (! $verifiedMobile || now()->timestamp - $verifiedMobile['verified_at'] > 600) {
                    session()->forget('verified_mobile');

                    return errorResponse(__('message.mobile_verification_required'));
                }

                session()->forget('verified_mobile');

                return $this->updateUserMobile(
                    $verifiedMobile['mobile'],
                    $verifiedMobile['dial_code'],
                    $verifiedMobile['country_iso']
                );
            }

            return successResponse(__('message.email_verification.email_verified'));
        } catch (Exception) {
            return errorResponse(__('message.email_verification.invalid_token'));
        }
    }

    /**
     * Send or resend OTP to new mobile number.
     */
    public function sendMobileOtp(Request $request, mixed $method = 'POST'): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'mobile_to_verify' => ['required', 'string'],
            'dial_code' => ['required', 'string'],
            'country_iso' => ['required', 'string'],
        ], [
            'mobile_to_verify.required' => __('validation.profile_form.mobile.required'),
            'mobile_to_verify.string' => __('validation.profile_form.mobile.regex'),
            'dial_code.required' => __('message.dialcode_required'),
            'country_iso.required' => __('message.isocode_required'),
        ]);

        try {
            $dialCode = (string) $request->dial_code;
            $mobileNo = (string) $request->mobile_to_verify;
            $countryIso = strtoupper((string) $request->country_iso);
            $cleanMobile = preg_replace('/\D/', '', $mobileNo);
            $fullMobile = (string) preg_replace('/\D/', '', $dialCode.$mobileNo);
            $cleanMobile = (string) $cleanMobile;
            $emailVerificationRequired = false;

            // Initial submission — check existence and verification settings
            if ($method === 'POST') {
                if (User::where('mobile', $cleanMobile)->exists()) {
                    return errorResponse(__('message.mobile_no_already_used'));
                }

                $settings = $this->getVerificationSettings();
                $emailVerificationRequired = $settings['email'];

                if (! $settings['mobile']) {
                    return $this->updateUserMobile($cleanMobile, $dialCode, $countryIso);
                }
            }

            // Send or resend OTP via MSG91
            $mobileInfo = ['country_iso' => $countryIso, 'mobile' => $mobileNo, 'mobile_code' => $dialCode];
            $sms = resolve(SmsOtpController::class);

            RateLimiter::hit('mobile-otp:'.auth()->id(), 600);

            $otpResponse = $method === 'GET'
                ? $sms->sendForReOtp($fullMobile, $request->input('retry_type', 'text'), (int) auth()->id(), 'profile-update', $mobileInfo)
                : $sms->sendOtp($fullMobile, (int) auth()->id(), 'profile-update', $mobileInfo);

            if ($otpResponse['type'] === 'error') {
                return errorResponse($otpResponse['message']);
            }

            $message = $method === 'GET'
                ? __('message.verification_code_resent_mobile')
                : __('message.verification_code_sent_mobile');

            return successResponse($message, $method === 'POST'
                ? ['email_verification_required' => $emailVerificationRequired]
                : []
            );
        } catch (Exception $exception) {
            Log::error('OTP sending failed: '.$exception->getMessage());

            return errorResponse(__('message.otp_verification.send_failure'));
        }
    }

    /**
     * Verify mobile OTP and update mobile if no email verification required.
     */
    public function verifyMobileOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'mobile_to_verify' => ['required', 'string'],
            'otp' => ['required', 'numeric', 'digits:6'],
            'new_mobile' => ['required', 'string'],
            'dial_code' => ['required', 'string'],
            'country_iso' => ['required', 'string'],
        ], [
            'mobile_to_verify.required' => __('validation.profile_form.mobile.required'),
            'mobile_to_verify.string' => __('validation.profile_form.mobile.regex'),
            'otp.required' => __('validation.verify_otp.otp_required'),
            'otp.digits' => __('validation.verify_otp.otp_size'),
            'new_mobile.required' => __('validation.profile_form.mobile.required'),
            'dial_code.required' => __('message.dialcode_required'),
            'country_iso.required' => __('message.isocode_required'),
        ]);

        try {
            $response = resolve(SmsOtpController::class)->sendVerifyOTP(
                $request->otp,
                $request->mobile_to_verify,
                auth()->id(),
                'profile-update'
            );

            if ($response['type'] !== 'success') {
                RateLimiter::hit('mobile-verify:'.auth()->id(), 600);

                return errorResponse($response['message']);
            }

            $cleanMobile = (string) preg_replace('/\D/', '', (string) $request->input('new_mobile'));
            $dialCode = (string) $request->input('dial_code');
            $countryIso = (string) $request->input('country_iso');

            $settings = $this->getVerificationSettings();

            if (! $settings['email']) {
                return $this->updateUserMobile($cleanMobile, $dialCode, $countryIso);
            }

            session(['verified_mobile' => [
                'mobile' => $cleanMobile,
                'dial_code' => $dialCode,
                'country_iso' => $countryIso,
                'verified_at' => now()->timestamp,
            ]]);

            return successResponse(__('message.otp_verified'), [
                'email_verification_required' => true,
            ]);
        } catch (Exception) {
            return errorResponse(__('message.error_occurred_while_verify'));
        }
    }

    /**
     * Resend OTP for email or mobile verification.
     */
    public function resendOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        return match ($request->input('type')) {
            'email' => $this->sendEmailOtp($request, 'GET'),
            'mobile' => $this->sendMobileOtp($request, 'GET'),
            default => errorResponse(__('message.something_wrong')),
        };
    }

    // ─── Private Helpers ─────────────────────────────────────────────────

    private function handleInitialEmailChange(mixed $user, string $newEmail): \Illuminate\Http\JsonResponse
    {
        if (User::where('email', $newEmail)->exists()) {
            return errorResponse(__('message.email_already_used'));
        }

        $settings = $this->getVerificationSettings();

        if (! $settings['email']) {
            return $this->updateUserEmail($newEmail);
        }

        RateLimiter::hit('email-otp-old:'.$user->id, 600);
        $this->sendActivationOtp($user->email, 'POST', 'old_email');

        return successResponse(__('message.otp_code_sent_exist'), [
            'email_verification_required' => true,
        ]);
    }

    private function dispatchNewEmailOtp(mixed $user, string $email, string $method): \Illuminate\Http\JsonResponse
    {
        if ($method !== 'GET') {
            $existing = AccountActivate::where('email', $email)->first();

            if ($existing && ! $existing->updated_at?->addMinutes(10)->isPast()) {
                return successResponse(__('message.email_verification.already_sent'), '', 208);
            }

            $existing?->delete();
        }

        RateLimiter::hit('email-otp-new:'.$user->id, 600);
        $this->sendActivationOtp($email, $method, 'new_email');

        return successResponse(
            $method === 'GET'
                ? __('message.verification_code_resent')
                : __('message.otp_code_send_success')
        );
    }

    private function sendActivationOtp(string $email, string $method, string $mode): void
    {
        $token = random_int(100000, 999999);

        if ($method === 'GET') {
            AccountActivate::updateOrCreate(['email' => $email], ['token' => $token]);
        } else {
            AccountActivate::create(['email' => $email, 'token' => $token]);
        }

        $templateName = match ($mode) {
            'new_email' => 'verify_new_email',
            'old_email' => 'confirm_old_email',
            'mobile' => 'confirm_mobile_number_change',
            default => null,
        };

        $template = Template::whereHas('type', fn (Builder $q) => $q->where('name', $templateName))->first();

        if (! $template) {
            throw new Exception(__('message.something_wrong'));
        }

        /** @var \App\Model\Common\Setting $settings */
        $settings = Setting::find(1);
        $contact = getContactData();

        $replace = [
            'name' => $email,
            'otp' => $token,
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'app_name' => $settings->title,
            'contact_url' => url('/contact-us'),
        ];

        $type = TemplateType::where('id', $template->type)->first()->name ?? '';

        new PhpMailController()->SendEmail(
            $settings->email, $email, $template->data, $template->name, (string) $templateName, $replace, $type
        );
    }

    private function hitVerifyRateLimit(string $verifyType): void
    {
        $key = match ($verifyType) {
            'old_email' => 'email-verify-old',
            'mobile_email' => 'email-verify-mobile',
            default => 'email-verify-new',
        };

        RateLimiter::hit($key.':'.auth()->id(), 600);
    }

    private function updateUserEmail(string $email): \Illuminate\Http\JsonResponse
    {
        if (User::where('email', $email)->where('id', '!=', auth()->id())->exists()) {
            return errorResponse(__('message.email_already_used'));
        }

        /** @var \App\User $user */
        $user = auth()->user();
        $user->email = $email;
        $user->user_name = $email;
        $user->save();

        return successResponse(__('message.new_email_updated'), [
            'email_verification_required' => false,
            'email_updated' => true,
            'email' => $user->email,
        ]);
    }

    private function updateUserMobile(string $mobile, string $dialCode, string $countryIso): \Illuminate\Http\JsonResponse
    {
        if (User::where('mobile', $mobile)->where('id', '!=', auth()->id())->exists()) {
            return errorResponse(__('message.mobile_no_already_used'));
        }

        /** @var \App\User $user */
        $user = auth()->user();
        $user->mobile = $mobile;
        $user->mobile_code = $dialCode;
        $user->mobile_country_iso = $countryIso;
        $user->save();

        return successResponse(__('message.new_mobile_no_updated'), [
            'mobile_updated' => true,
            'mobile' => $user->mobile,
            'mobile_code' => $user->mobile_code,
        ]);
    }

    /**
     * @return array<mixed>
     */
    private function getVerificationSettings(): array
    {
        $status = StatusSetting::query()->first();

        return [
            'email' => (bool) ($status->emailverification_status ?? false),
            'mobile' => (bool) ($status->msg91_status ?? false),
        ];
    }
}
