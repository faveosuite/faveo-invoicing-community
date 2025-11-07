<?php

namespace App\Http\Controllers\Front;

use App\ApiKey;
use App\Http\Controllers\Auth\BaseAuthController;
use App\Model\Common\StatusSetting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\User\AccountActivate;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileVerificationController extends BaseAuthController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Create new Auto renewal and update auto-renewal status.
     *
     * @param  Request  $request
     * @return array{type:string,message:string}|JsonResponse
     */

    /**
     * Send verification code to new email or existing email based on the method.
     */
    public function sendNewEmailVerification(Request $request, $method = 'POST')
    {
        $request->validate([
            'email_to_verify' => 'required|email',
        ], [
            'email_to_verify.required' => __('message.login_validation.email_required'),
            'email_to_verify.email' => __('message.login_validation.email_regex'),
        ]);

        try {
            $newEmailOrExisting = $request->email_to_verify;
            $isMobile = $request->is_mobile;
            $user = auth()->user();

            if ($newEmailOrExisting === $user->email && ! $isMobile) {
                $this->sendActivationForEdit($user, $user->email, $method, 'old_email');

                return successResponse(__('message.otp_code_sent_exist'));
            }
            if ($newEmailOrExisting === $user->email && $isMobile) {
                $this->sendActivationForEdit($user, $user->email, $method, 'mobile');

                return successResponse(__('message.otp_code_sent_exist'));
            }

            if (AccountActivate::where('email', $newEmailOrExisting)->first() && $method !== 'GET') {
                return successResponse(__('message.email_verification.already_sent'));
            }

            $this->sendActivationForEdit($user, $newEmailOrExisting, $method, 'new_email');

            return successResponse(
                $method === 'GET'
                    ? __('message.verification_code_resent')
                    : __('message.email_verification.send_success')
            );
        } catch (\Exception $exception) {
            return errorResponse(__('message.email_verification.send_failure'));
        }
    }

    /**
     * Send activation code to the specified email with template.
     */
    public function sendActivationForEdit($user, $email, $method, $mode = null)
    {
        $contact = getContactData();

        try {
            $activate_model = new AccountActivate();

            if ($method == 'GET') {
                $response = $activate_model->where('email', $email)->first();

                if ($response) {
                    $token = mt_rand(100000, 999999);
                    $response->update(['token' => $token]);
                } else {
                    $token = mt_rand(100000, 999999);
                    $activate_model->create(['email' => $email, 'token' => $token]);
                }
            } else {
                // For non-GET methods, always create new record
                $token = mt_rand(100000, 999999);
                $activate_model->create(['email' => $email, 'token' => $token]);
            }

            // Get settings
            $settings = \App\Model\Common\Setting::find(1);
            $templateName = match ($mode) {
                'new_email' => 'verify_new_email',
                'old_email' => 'confirm_old_email',
                'mobile' => 'confirm_mobile_number_change',
                default => null,
            };

            // Get template
            $template = Template::whereHas('type', function ($q) use ($templateName) {
                $q->where('name', $templateName);
            })->first();

            if (! $template) {
                throw new \Exception(__('message.something_wrong'));
            }

            $template_data = $template->data;
            $template_name = $template->name;
            $website_url = url('/contact-us');
            $type = TemplateType::where('id', $template->type)->first()->name ?? '';

            $replace = [
                'name' => $email,
                'otp' => $token,
                'contact' => $contact['contact'],
                'logo' => $contact['logo'],
                'app_name' => $settings->title,
                'contact_url' => $website_url,
            ];

            $mail = new \App\Http\Controllers\Common\PhpMailController();
            $mail->SendEmail($settings->email, $email, $template_data, $template_name, $replace, $type);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * Verify OTP for new and old email address.
     */
    public function verifyOtpForEditEmail(Request $request)
    {
        $verificationType = $request->input('verify_type');

        $keyPrefix = match ($verificationType) {
            'new_email' => 'new_email',
            'old_email' => 'old_email',
            'mobile_confirmation' => 'mobile_email',
            default => null,
        };

        $rateLimit = rateLimitForKeyIp($keyPrefix, 5, 30, $request->ip());
        if ($rateLimit['status']) {
            return errorResponse(__('message.too_many_attempts_for_change_email_mobile', ['time' => $rateLimit['remainingTime']]), 429);
        }

        $request->validate([
            'email_to_verify' => 'required|email',
            'otp' => 'required|string|size:6',
        ], [
            'email_to_verify.required' => __('message.login_validation.email_required'),
            'email_to_verify.email' => __('message.login_validation.email_regex'),
            'otp.required' => __('validation.verify_otp.otp_required'),
            'otp.size' => __('validation.verify_otp.otp_size'),
        ]);

        try {
            $otp = $request->input('otp');
            $email = $request->input('email_to_verify');

            $account = AccountActivate::where('email', $email)->latest()->first(['token', 'updated_at']);

            if (! $account || $account->token !== $otp) {
                return errorResponse(__('message.email_verification.invalid_token'));
            }

            if ($account->updated_at->addMinutes(10)->isPast()) {
                return errorResponse(__('message.email_verification.token_expired'));
            }

            AccountActivate::where('email', $email)->delete();

            return successResponse(__('message.email_verification.email_verified'));
        } catch (\Exception $e) {
            return errorResponse(__('message.email_verification.invalid_token'));
        }
    }

    /**
     * Update old email to new email after OTP verification success.
     */
    public function changeEmailOldToNew(Request $request)
    {
        $request->validate([
            'newEmail' => 'required|email',
        ], [
            'newEmail.required' => __('message.login_validation.email_required'),
            'newEmail.email' => __('message.login_validation.email_regex'),
        ]);
        try {
            $user = auth()->user();

            // Update logged-in user email directly
            $user->email = $request->input('newEmail');
            $user->save();

            return successResponse(__('message.new_email_updated'), ['email' => $user->email]);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    /**
     * Check if the given email already exists in the system.
     */
    public function checkEmailExist(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => __('validation.email_required'),
            'email.email' => __('validation.email_invalid'),
        ]);
        try {
            $email = $request->input('email');
            $exists = User::where('email', $email)->exists();
            $statusSetting = StatusSetting::query()->first();
            $emailVerificationRequired = $statusSetting?->emailverification_status ?? false;

            if ($exists) {
                return errorResponse(__('message.email_already_used'));
            }

            return successResponse(
                __('message.given_email_valid'),
                [
                    'email_verification_required' => (bool) $emailVerificationRequired,
                ]
            );
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    /**
     * Generate otp code to new mobile number.
     */
    public function requestOtpForNewMobileNo(Request $request, $isResend = 'POST')
    {
        $request->validate([
            'mobile_to_verify' => 'required|string',
            'dial_code' => 'required|string',
            'country_iso' => 'required|string',
        ], [
            'mobile_to_verify.required' => __('validation.profile_form.mobile.required'),
            'mobile_to_verify.string' => __('validation.profile_form.mobile.regex'),
            'dial_code.required' => __('message.dialcode_required'),
            'country_iso.required' => __('message.isocode_required'),
        ]);

        try {
            $dialCode = $request->dial_code;
            $mobileNo = $request->mobile_to_verify;
            $countryIso = $request->country_iso;
            $responseNewMobileOtp = $this->sendOtpForNewMobileNo($dialCode, $mobileNo, $countryIso);

            if ($responseNewMobileOtp['type'] === 'error') {
                return errorResponse($responseNewMobileOtp['message']);
            }

            return successResponse(
                $isResend === 'GET'
                    ? __('message.verification_code_resent_mobile')
                    : __('message.verification_code_sent_mobile')
            );
        } catch (\Exception $e) {
            \Log::error('OTP sending failed: '.$e->getMessage());

            return errorResponse(__('message.otp_verification.send_failure'));
        }
    }

    /**
     * Send OTP to a new mobile number with msg91.
     */
    public function sendOtpForNewMobileNo($dialCode, $mobileNo, $countryIso): array
    {
        try {
            $fullMobile = preg_replace('/\D/', '', $dialCode.$mobileNo);
            // Get API Keys
            $msgKey = ApiKey::find(1, ['msg91_auth_key', 'msg91_sender', 'msg91_template_id']);
            if (! $msgKey) {
                \Log::error('MSG91 API keys not found.');

                return false;
            }

            $sender = $msgKey->msg91_sender;
            $templateId = $msgKey->msg91_template_id;

            $queryParams = [
                'template_id' => $templateId,
                'sender' => $sender,
                'mobile' => $fullMobile,
                'otp_length' => 6,
                'otp_expiry' => 10,
            ];

            // Call MSG91 API
            $response = $this->makeRequest('POST', 'https://api.msg91.com/api/v5/otp', $queryParams);

            return $this->responseHandler($response);
        } catch (\Exception $e) {
            \Log::error('sendOtpForNewMobileNo error: '.$e->getMessage());

            return errorResponse($e->getMessage());
        }
    }

    /**
     * check mobile number already exist in the system.
     */
    public function checkMobileNoExist(Request $request)
    {
        try {
            $request->validate([
                'mobile_to_verify' => 'required|string',
                'dial_code' => 'required|string',
                'country_iso' => 'required|string',
            ], [
                'mobile_to_verify.required' => __('validation.profile_form.mobile.required'),
                'mobile_to_verify.string' => __('validation.profile_form.mobile.regex'),
                'dial_code.required' => __('message.dialcode_required'),
                'country_iso.required' => __('message.isocode_required'),
            ]);

            $statusSetting = StatusSetting::query()->first();
            $mobileVerificationRequired = $statusSetting?->msg91_status ?? false;

            // Clean mobile number (only digits)
            $mobile = preg_replace('/\D/', '', $request->mobile_to_verify);

            // Check in DB
            $exists = User::where('mobile', $mobile)
                ->where('mobile_code', $request->dial_code)
                ->where('mobile_country_iso', strtoupper($request->country_iso))
                ->exists();

            if ($exists) {
                return errorResponse(__('message.mobile_no_already_used'));
            }

            return successResponse(
                __('message.given_mobile_no_valid'),
                [
                    'mobile_verification_required' => (bool) $mobileVerificationRequired,
                ]
            );
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    /**
     * Verify OTP for new mobile number.
     */
    public function verifyOtpMobileNew(Request $request)
    {
        $mobileVerificationType = $request->input('verify_mobile');
        $rateLimit = rateLimitForKeyIp($mobileVerificationType, 5, 30, $request->ip());
        if ($rateLimit['status']) {
            return errorResponse(__('message.too_many_attempts_for_change_email_mobile', ['time' => $rateLimit['remainingTime']]), 429);
        }

        $request->validate([
            'mobile_to_verify' => 'required|string',
            'otp' => 'required|string|size:6',
        ], [
            'mobile_to_verify.required' => __('validation.profile_form.mobile.required'),
            'mobile_to_verify.string' => __('validation.profile_form.mobile.regex'),
            'otp.required' => __('validation.verify_otp.otp_required'),
            'otp.size' => __('validation.verify_otp.otp_size'),
        ]);

        try {
            $mobile = $request->mobile_to_verify;
            $otp = $request->otp;

            // Validate OTP
            if (! is_numeric($request->otp)) {
                return errorResponse(__('message.otp_invalid_format'));
            }

            $response = $this->sendVerifyOTP($otp, $mobile);

            if (! isset($response['type']) || $response['type'] !== 'success') {
                return errorResponse($response['message'] ?? __('message.otp_invalid'));
            }

            return successResponse(__('message.otp_verified'));
        } catch (\Exception $e) {
            return errorResponse(__('message.error_occurred_while_verify'));
        }
    }

    /**
     * Update old mobile number to new mobile number after OTP verification success.
     */
    public function changeMobileOldToNew(Request $request)
    {
        $request->validate([
            'newMobile' => 'required|string',
            'dial_code' => 'required|string',
            'country_iso' => 'required|string',
        ], [
            'newMobile.required' => __('validation.profile_form.mobile.required'),
            'newMobile.string' => __('validation.profile_form.mobile.regex'),
            'dial_code.required' => __('message.dialcode_required'),
            'country_iso.required' => __('message.isocode_required'),
        ]);

        try {
            $user = auth()->user();

            $user->mobile = $request->input('newMobile');
            $user->mobile_code = $request->input('dial_code');
            $user->mobile_country_iso = $request->input('country_iso');
            $user->save();

            return successResponse(__('message.new_mobile_no_updated'),
                [
                    'mobile' => $user->mobile,
                    'mobile_code' => $user->mobile_code,
                ]);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    /**
     * Make HTTP request to the given URL with parameters.
     */
    public function resentOtpProfile(Request $request)
    {
        $default_type = $request->input('type');

        return match ($default_type) {
            'email' => $this->sendNewEmailVerification($request, 'GET'),
            'mobile' => $this->requestOtpForNewMobileNo($request, 'GET'),
        };
    }
}
