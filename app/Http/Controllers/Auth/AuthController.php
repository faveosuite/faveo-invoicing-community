<?php

namespace App\Http\Controllers\Auth;

use App\ApiKey;
use App\Events\UserRegisteredEvent;
use App\Http\Controllers\Common\Sms\SmsOtpController;
use App\Model\Common\StatusSetting;
use App\Model\User\AccountActivate;
use App\User;
use App\VerificationAttempt;
use Auth;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;
use RateLimiter;
use Session;

class AuthController extends BaseAuthController
{
    /** Minimum wait between two codes on the same channel (email / mobile) for a user. */
    private const int RESEND_WAIT_SECONDS = 60;

    /** How long a sent code stays valid (MSG91 otp_expiry / email token lifetime). */
    private const int CODE_VALID_MINUTES = 10;

    /**
     * Server-side resend wait: a 429 with `retry_after` (seconds) while it's running,
     * otherwise null and the wait starts. The Verify page's countdown uses `retry_after`,
     * so it survives reloads and can't be skipped by calling the API directly.
     * (Separate from the 3-per-10-minutes limit in BlockFailedVerifications.)
     */
    private function resendWait(string $channel, User $user): ?JsonResponse
    {
        $key = $this->waitKey($channel, $user);
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);

            return errorResponse(__('message.otp_resend_wait', ['seconds' => $seconds]), 429, ['retry_after' => $seconds]);
        }
        RateLimiter::hit($key, self::RESEND_WAIT_SECONDS);

        return null;
    }

    /** Seconds left on the resend wait (0 when over), without starting a new one. */
    private function resendWaitLeft(string $channel, User $user): int
    {
        $key = $this->waitKey($channel, $user);

        return RateLimiter::tooManyAttempts($key, 1) ? RateLimiter::availableIn($key) : 0;
    }

    private function waitKey(string $channel, User $user): string
    {
        return sprintf('otp-wait:%s:%s', $channel, $user->id);
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

        $failure = __('message.otp_verification.send_failure');

        try {
            $user = $this->verificationUser($request->eid);
            if (! $user) {
                return errorResponse($failure);
            }

            if ($user->mobile_verified) {
                return errorResponse(__('message.mobile_already_verified'));
            }

            // Initial send (page load / refresh): the last code is still valid, so don't send another;
            // the page just shows the remaining wait and the user can use Resend.
            if (Cache::has('otp-sent:mobile:'.$user->id)) {
                return successResponse(__('message.otp_verification.already_sent'), ['retry_after' => $this->resendWaitLeft('mobile', $user)]);
            }

            return $this->sendMobileCode($user, __('message.otp_verification.send_success'));
        } catch (Exception $e) {
            report($e);

            return errorResponse($failure);
        }
    }

    public function retryOTP(Request $request): JsonResponse
    {
        // channel: which verification step (email / mobile); method: SMS text or voice call.
        return match ($request->input('channel')) { // @phpstan-ignore match.unhandled
            'email' => $this->sendEmail($request, resend: true),
            'mobile' => $this->resendOTP($request),
        };
    }

    public function resendOTP(Request $request): JsonResponse
    {
        $request->validate([
            'eid' => 'required|string',
            'method' => 'required|string|in:text,voice',
        ], [
            'eid.required' => __('validation.resend_otp.eid_required'),
            'eid.string' => __('validation.resend_otp.eid_string'),
            'method.required' => __('validation.resend_otp.type_required'),
            'method.string' => __('validation.resend_otp.type_string'),
            'method.in' => __('validation.resend_otp.type_in'),
        ]);

        $failure = __('message.otp_verification.resend_send_failure');

        try {
            $user = $this->verificationUser($request->eid);
            if (! $user) {
                return errorResponse($failure);
            }

            $method = $request->input('method');

            return $this->sendMobileCode(
                $user,
                __($method === 'voice' ? 'message.otp_verification.resend_voice_send_success' : 'message.otp_verification.resend_send_success'),
                $method
            );
        } catch (Exception $e) {
            report($e);

            return errorResponse($failure);
        }
    }

    /** $resend: the user clicked Resend (always a fresh code) rather than the page's first send. */
    public function sendEmail(Request $request, bool $resend = false): JsonResponse
    {
        $request->validate([
            'eid' => ['required', 'string'],
        ], [
            'eid.required' => __('validation.eid_required'),
            'eid.string' => __('validation.eid_string'),
        ]);

        $failure = __('message.email_verification.send_failure');

        try {
            $user = $this->verificationUser($request->eid);
            if (! $user) {
                return errorResponse($failure);
            }

            // Initial send (page load / refresh): the last code is still valid, so don't send another
            // and don't restart the wait; the page shows what's left of it.
            $existingToken = AccountActivate::where('email', $user->email)->latest()->first();
            if ($existingToken && ! $resend && ! $existingToken->updated_at?->addMinutes(self::CODE_VALID_MINUTES)->isPast()) {
                return successResponse(__('message.email_verification.already_sent'), ['retry_after' => $this->resendWaitLeft('email', $user)]);
            }

            if ($wait = $this->resendWait('email', $user)) {
                return $wait;
            }

            RateLimiter::hit('email-otp:'.$user->id, 600);

            $this->sendActivation($user->email, $resend ? 'GET' : 'POST');

            $this->updateVerificationAttempts($user, 'email');

            return successResponse(
                $resend
                    ? __('message.email_verification.resend_success')
                    : __('message.email_verification.send_success'),
                ['retry_after' => self::RESEND_WAIT_SECONDS]
            );
        } catch (Exception $e) {
            report($e);

            return errorResponse($failure);
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

        $failure = __('message.error_occurred_while_verify');

        try {
            $user = $this->verificationUser($request->eid);
            if (! $user) {
                return errorResponse($failure);
            }

            $otp = (string) $request->input('otp');
            if (! is_numeric($otp)) {
                RateLimiter::hit('mobile-verify:'.$user->id, 600);

                return errorResponse(__('message.otp_invalid_format'));
            }

            $response = resolve(SmsOtpController::class)->sendVerifyOTP($otp, $user->mobile_code.$user->mobile, $user->id, 'registration-verify');
            if ($response['type'] === 'error') {
                RateLimiter::hit('mobile-verify:'.$user->id, 600);

                return errorResponse($response['message']);
            }

            $this->markVerified($user, 'mobile_verified');

            return successResponse(__('message.otp_verified'));
        } catch (Exception $e) {
            report($e);

            return errorResponse($failure);
        }
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'eid' => ['required', 'string'],
            'otp' => ['required', 'string', 'digits:6'],
        ],
            [
                'eid.required' => __('validation.verify_otp.eid_required'),  // Translating for eid field
                'eid.string' => __('validation.verify_otp.eid_string'),
                'otp.required' => __('validation.verify_otp.otp_required'),
                'otp.digits' => __('message.otp_must_be_6_digits'),
            ]);

        $invalid = __('message.email_verification.invalid_token');

        try {
            $user = $this->verificationUser($request->eid);
            $account = $user ? AccountActivate::where('email', $user->email)->latest()->first(['token', 'updated_at']) : null;
            if (! $user || ! $account) {
                return errorResponse($invalid);
            }

            if (! $account->matches($request->input('otp'))) {
                RateLimiter::hit('email-verify:'.$user->id, 600);

                return errorResponse($invalid);
            }

            if ($account->updated_at?->addMinutes(self::CODE_VALID_MINUTES) < Date::now()) {
                RateLimiter::hit('email-verify:'.$user->id, 600);

                return errorResponse(__('message.email_verification.token_expired'));
            }

            AccountActivate::where('email', $user->email)->delete();

            $this->markVerified($user, 'email_verified');

            return successResponse(__('message.email_verification.email_verified'));
        } catch (Exception $e) {
            report($e);

            return errorResponse($invalid);
        }
    }

    /**
     * The user this verification session belongs to, or null when the eid is tampered with
     * or belongs to someone else (attempt limits are counted for the session's user).
     */
    private function verificationUser(string $eid): ?User
    {
        try {
            $email = Crypt::decrypt($eid);
        } catch (DecryptException) {
            return null;
        }

        return is_string($email)
            ? User::where('email', $email)->where('id', Session::get('verification_user_id'))->first()
            : null;
    }

    /** Send an SMS code ($method null = first send, else 'text' / 'voice' resend) behind the resend wait. */
    private function sendMobileCode(User $user, string $successMessage, ?string $method = null): JsonResponse
    {
        if ($wait = $this->resendWait('mobile', $user)) {
            return $wait;
        }

        RateLimiter::hit('mobile-otp:'.$user->id, 600);

        $sms = resolve(SmsOtpController::class);
        $mobile = $user->mobile_code.$user->mobile;
        $response = $method === null
            ? $sms->sendOtp($mobile, $user->id, 'registration-verify')
            : $sms->sendForReOtp($mobile, $method, $user->id, 'registration-verify');

        if ($response['type'] === 'error') {
            return errorResponse($response['message']);
        }

        Cache::put('otp-sent:mobile:'.$user->id, true, now()->addMinutes(self::CODE_VALID_MINUTES));

        $this->updateVerificationAttempts($user, 'mobile');

        return successResponse($successMessage, ['retry_after' => self::RESEND_WAIT_SECONDS]);
    }

    /**
     * @param  User  $user
     * @param  'mobile_verified'|'email_verified' $field
     */
    private function markVerified(User $user, string $field): void
    {
        $user->{$field} = 1;
        $user->save();

        if (! Auth::check() && $this->userNeedVerified($user)) {
            // dispatch the job to add user to external services
            UserRegisteredEvent::dispatchSafely($user, 'verify');

            Session::flash('success', __('message.registration_complete'));
        }
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
        $setting = StatusSetting::select(['emailverification_status', 'msg91_status', 'mobileverification_status'])->first();

        $mobileRequired = $setting->mobileVerificationEnabled();
        $emailRequired = $setting->emailverification_status == 1;
        $isMobileVerified = ! $mobileRequired || $user->mobile_verified == 1;
        $isEmailVerified = ! $emailRequired || $user->email_verified == 1;

        $verification_preference = ApiKey::value('verification_preference') ?? ($isEmailVerified ? 'email' : 'mobile');

        return successResponse('verify-config', [
            'eid' => $eid,
            'mobile' => $user->mobile_code.$user->mobile,
            'email' => $user->email,
            'isMobileVerified' => $isMobileVerified,
            'isEmailVerified' => $isEmailVerified,
            // Which steps exist at all, so the page can show one already done as done.
            'mobileRequired' => $mobileRequired,
            'emailRequired' => $emailRequired,
            'verification_preference' => $verification_preference,
        ]);
    }

    /**
     * @param  User  $user
     * @param  'email'|'mobile' $type
     */
    private function updateVerificationAttempts(User $user, string $type): void
    {
        $verificationAttempt = VerificationAttempt::firstOrCreate(['user_id' => $user->id]);
        $verificationAttempt->{$type.'_attempt'}++;
        $verificationAttempt->save();
    }
}
