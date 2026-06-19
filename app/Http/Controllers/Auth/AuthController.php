<?php

namespace App\Http\Controllers\Auth;

use App\ApiKey;
use App\Events\UserRegisteredEvent;
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Common\Sms\SmsOtpController;
use App\Http\Controllers\Common\TemplateController;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\User\AccountActivate;
use App\User;
use App\VerificationAttempt;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;
use RateLimiter;
use Session;

class AuthController extends BaseAuthController
{
    /*
      |--------------------------------------------------------------------------
      | Registration & Login Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles the registration of new users, as well as the
      | authentication of existing users. By default, this controller uses
      | a simple trait to add these behaviors. Why don't you explore it?
      |
     */

    // protected $loginPath = 'login';

    protected mixed $pipedrive = null;

    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
        $this->middleware('blockFailedVerifications:verify,mobile-verify,email-verify,email-verify-new,email-verify-old,email-verify-mobile')->only(['verifyOtp', 'verifyEmail']);
        $this->middleware('recaptcha:mobile_verify')->only('verifyOtp');
        $this->middleware('recaptcha:email_verify')->only('verifyEmail');
    }

    public function requestOtp(Request $request): JsonResponse
    {
        $request->validate([
            'eid' => ['required', 'string'],
        ],
            [
                'eid.required' => __('validation.eid_required'),
                'eid.string' => __('validation.eid_string'),
            ]);

        try {
            // Decrypt the email
            $email = Crypt::decrypt($request->eid);

            // Find the user by email
            $user = User::where('email', $email)->firstOrFail();

            if ($user->mobile_verified) {
                return errorResponse(__('message.mobile_already_verified'));
            }

            RateLimiter::hit('mobile-otp:'.$user->id, 600);

            $response = resolve(SmsOtpController::class)->sendOtp($user->mobile_code.$user->mobile, $user->id, 'registration-verify');

            if ($response['type'] === 'error') {
                return errorResponse($response['message']);
            }

            $this->updateVerificationAttempts($user, 'mobile');

            return successResponse(__('message.otp_verification.send_success'));
        } catch (Exception) {
            return errorResponse(__('message.otp_verification.send_failure'));
        }
    }

    public function retryOTP(Request $request): mixed
    {
        $default_type = $request->input('default_type');

        return match ($default_type) { // @phpstan-ignore match.unhandled
            'email' => $this->sendEmail($request, 'GET'),
            'mobile' => $this->resendOTP($request),
        };
    }

    public function resendOTP(mixed $request): JsonResponse
    {
        $request->validate([
            'eid' => 'required|string',
            'type' => 'required|string|in:text,voice',
        ], [
            'eid.required' => __('validation.resend_otp.eid_required'),
            'eid.string' => __('validation.resend_otp.eid_string'),
            'type.required' => __('validation.resend_otp.type_required'),
            'type.string' => __('validation.resend_otp.type_string'),
            'type.in' => __('validation.resend_otp.type_in'),
        ]);
        try {
            $email = Crypt::decrypt($request->eid);
            $type = $request->input('type');

            $user = User::where('email', $email)->firstOrFail();

            RateLimiter::hit('mobile-otp:'.$user->id, 600);

            $response = resolve(SmsOtpController::class)->sendForReOtp($user->mobile_code.$user->mobile, $type, $user->id, 'registration-verify');

            if ($response['type'] === 'error') {
                return errorResponse($response['message']);
            }

            $this->updateVerificationAttempts($user, 'mobile');

            if ($type === 'voice') {
                return successResponse(__('message.otp_verification.resend_voice_send_success'));
            }

            return successResponse(__('message.otp_verification.resend_send_success'));
        } catch (Exception) {
            return errorResponse(__('message.otp_verification.resend_send_failure'));
        }
    }

    public function sendEmail(Request $request, mixed $method = 'POST'): JsonResponse
    {
        $request->validate([
            'eid' => ['required', 'string'],
        ], [
            'eid.required' => __('validation.eid_required'),
            'eid.string' => __('validation.eid_string'),
        ]);
        try {
            $email = Crypt::decrypt($request->eid);

            $user = User::where('email', $email)->firstOrFail();

            $existingToken = AccountActivate::where('email', $email)->latest()->first();
            if ($existingToken && $method !== 'GET' && ! $existingToken->updated_at?->addMinutes(10)->isPast()) {
                return successResponse(__('message.email_verification.already_sent'));
            }

            RateLimiter::hit('email-otp:'.$user->id, 600);

            $this->sendActivation($email, $method);

            $this->updateVerificationAttempts($user, 'email');

            return successResponse(
                $method === 'GET'
                    ? __('message.email_verification.resend_success')
                    : __('message.email_verification.send_success')
            );
        } catch (Exception) {
            return errorResponse(__('message.email_verification.send_failure'));
        }
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'eid' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:6'],
        ],
            [
                'eid.required' => __('validation.verify_otp.eid_required'),  // Translating for eid field
                'eid.string' => __('validation.verify_otp.eid_string'),
                'otp.required' => __('validation.verify_otp.otp_required'),
                'otp.size' => __('validation.verify_otp.otp_size'),
            ]);
        try {
            // Decrypt the email
            $email = Crypt::decrypt($request->eid);
            $otp = $request->otp;

            // Find the user by email
            $user = User::where('email', $email)->firstOrFail();

            // Validate OTP
            if (! is_numeric($request->otp)) {
                RateLimiter::hit('mobile-verify:'.$user->id, 600);

                return errorResponse(__('message.otp_invalid_format'));
            }

            $response = resolve(SmsOtpController::class)->sendVerifyOTP($otp, $user->mobile_code.$user->mobile, $user->id, 'registration-verify');
            if ($response['type'] === 'error') {
                RateLimiter::hit('mobile-verify:'.$user->id, 600);

                return errorResponse($response['message']);
            }

            $user->mobile_verified = 1;

            $user->save();

            if (! Auth::check() && $this->userNeedVerified($user)) {
                // dispatch the job to add user to external services
                event(new UserRegisteredEvent($user, 'verify'));

                Session::flash('success', __('message.registration_complete'));
            }

            return successResponse(__('message.otp_verified'));
        } catch (Exception) {
            return errorResponse(__('message.error_occurred_while_verify'));
        }
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'eid' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:6'],
        ],
            [
                'eid.required' => __('validation.verify_otp.eid_required'),  // Translating for eid field
                'eid.string' => __('validation.verify_otp.eid_string'),
                'otp.required' => __('validation.verify_otp.otp_required'),
                'otp.size' => __('validation.verify_otp.otp_size'),
            ]);

        try {
            $otp = $request->input('otp');
            // Decrypt the email
            $email = Crypt::decrypt($request->eid);

            $user = User::where('email', $email)->firstOrFail();

            $account = AccountActivate::where('email', $email)->latest()->firstOrFail(['token', 'updated_at']);

            if (! hash_equals((string) $account->token, (string) $otp)) {
                RateLimiter::hit('email-verify:'.$user->id, 600);

                return errorResponse(__('message.email_verification.invalid_token'));
            }

            if ($account->updated_at?->addMinutes(10) < Date::now()) {
                RateLimiter::hit('email-verify:'.$user->id, 600);

                return errorResponse(__('message.email_verification.token_expired'));
            }

            AccountActivate::where('email', $email)->delete();

            $user->email_verified = 1;
            $user->save();

            if (! Auth::check() && $this->userNeedVerified($user)) {
                // dispatch the job to add user to external services
                event(new UserRegisteredEvent($user, 'verify'));

                Session::flash('success', __('message.registration_complete'));
            }

            return successResponse(__('message.email_verification.email_verified'));
        } catch (Exception) {
            return errorResponse(__('message.email_verification.invalid_token'));
        }
    }

    /**
     * @param  array<mixed>  $bcc
     */
    public function salesManagerMail(mixed $user, array $bcc = []): void
    {
        $contact = getContactData();
        $manager = $user->manager()

            ->where('position', 'manager')
            ->select('first_name', 'last_name', 'email', 'mobile_code', 'mobile', 'skype')
            ->first();
        $settings = new Setting;
        /** @var Setting $setting */
        $setting = $settings->first();
        $from = $setting->email;
        $to = $user->email;
        $templates = new Template;
        /** @var Template $template */
        $template = $templates
            ->join('template_types', 'templates.type', '=', 'template_types.id')
            ->where('template_types.name', '=', 'sales_manager_email')
            ->select('templates.data', 'templates.name', 'type')
            ->first();
        $template_data = $template->data;
        $template_name = $template->name;
        new TemplateController;
        $replace = [
            'name' => $user->first_name.' '.$user->last_name,
            'manager_first_name' => $manager->first_name,
            'manager_last_name' => $manager->last_name,
            'manager_email' => $manager->email,
            'manager_code' => '+'.$manager->mobile_code,
            'manager_mobile' => $manager->mobile,
            'manager_skype' => $manager->skype,
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'reply_email' => $setting->company_email,
        ];
        $mail = new PhpMailController;
        $mail->SendEmail($from, $to, $template_data, $template_name, 'sales-manager-mail', $replace, TemplateType::where('id', $template->type)->value('name'), $bcc);
    }

    /**
     * @param  array<mixed>  $bcc
     */
    public function accountManagerMail(mixed $user, array $bcc = []): void
    {
        $contact = getContactData();
        $manager = $user->accountManager()

            ->where('position', 'account_manager')
            ->select('first_name', 'last_name', 'email', 'mobile_code', 'mobile', 'skype')
            ->first();
        $settings = new Setting;
        /** @var Setting $setting */
        $setting = $settings->first();
        $from = $setting->email;
        $to = $user->email;
        $templates = new Template;
        /** @var Template $template */
        $template = $templates
            ->join('template_types', 'templates.type', '=', 'template_types.id')
            ->where('template_types.name', '=', 'account_manager_email')
            ->select('templates.data', 'templates.name', 'type')
            ->first();
        $template_data = $template->data;
        $template_name = $template->name;
        new TemplateController;
        $replace = [
            'name' => $user->first_name.' '.$user->last_name,
            'manager_first_name' => $manager->first_name,
            'manager_last_name' => $manager->last_name,
            'manager_email' => $manager->email,
            'manager_code' => '+'.$manager->mobile_code,
            'manager_mobile' => $manager->mobile,
            'manager_skype' => $manager->skype,
            'contact' => $contact['contact'],
            'logo' => $contact['logo'],
            'reply_email' => $setting->company_email,
        ];
        $mail = new PhpMailController;
        $mail->SendEmail($from, $to, $template_data, $template_name, 'account-manager-mail', $replace, TemplateType::where('id', $template->type)->value('name'), $bcc);
    }

    /**
     * JSON config consumed by the Vue guest OTP-verify SPA page.
     * Mirrors the data that verify() passed to the blade view.
     */
    public function verifyConfig(): JsonResponse
    {
        $userId = Session::get('verification_user_id') ?? Session::get('user')?->id;
        if (! $userId) {
            return successResponse('', ['redirect' => url('login')]);
        }

        $user = User::find($userId);
        if (! $user) {
            return successResponse('', ['redirect' => url('login')]);
        }

        /** @var User $user */
        $eid = Crypt::encrypt($user->email);

        /** @var StatusSetting $setting */
        $setting = StatusSetting::select('emailverification_status', 'msg91_status')->first();

        $isMobileVerified = ! ($setting->msg91_status == 1 && $user->mobile_verified != 1);
        $isEmailVerified = ! ($setting->emailverification_status == 1 && $user->email_verified != 1);

        $verification_preference = ApiKey::value('verification_preference') ?? ($isEmailVerified ? 'email' : 'mobile');

        return successResponse('verify-config', [
            'eid' => $eid,
            'mobile' => $user->mobile_code.$user->mobile,
            'email' => $user->email,
            'setting' => $setting,
            'isMobileVerified' => $isMobileVerified,
            'isEmailVerified' => $isEmailVerified,
            'verification_preference' => $verification_preference,
        ]);
    }

    private function updateVerificationAttempts(mixed $user, string $type = 'email'): void
    {
        if (! in_array($type, ['email', 'mobile'], strict: true)) {
            return;
        }

        $verificationAttempt = VerificationAttempt::firstOrCreate(['user_id' => $user->id]);

        $field = $type.'_attempt';
        $verificationAttempt->{$field}++;

        $verificationAttempt->save();
    }
}
