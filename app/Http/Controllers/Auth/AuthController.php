<?php

namespace App\Http\Controllers\Auth;

use App\ApiKey;
use App\Http\Controllers\Common\PipedriveController;
use App\Http\Controllers\Common\Sms\SmsOtpController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\License\LicenseController;
use App\Jobs\AddUserToExternalService;
use App\Model\Common\StatusSetting;
use App\Model\Common\TemplateType;
use App\Model\User\AccountActivate;
use App\User;
use App\VerificationAttempt;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use RateLimiter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Validator;

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

    // use AuthenticatesAndRegistersUsers;

    /* to redirect after login */

    //protected $redirectTo = 'home';

    /* Direct After Logout */
    protected $redirectAfterLogout = 'home';

    protected $loginPath = 'login';

    //protected $loginPath = 'login';

    protected $pipedrive;

    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
        $this->middleware('recaptcha:mobile_verify')->only('verifyOtp');
        $this->middleware('recaptcha:email_verify')->only('verifyEmail');
        $license = new LicenseController();
        $this->licensing = $license;
    }

    public function requestOtp(Request $request)
    {
        $request->validate([
            'eid' => 'required|string',
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

            RateLimiter::hit("mobile-otp:{$user->id}", 600);

            $response = app(SmsOtpController::class)->sendOtp($user->mobile_code.$user->mobile, $user->id, 'registration-verify');

            if ($response['type'] === 'error') {
                return errorResponse($response['message']);
            }

            $this->updateVerificationAttempts($user, 'mobile');

            return successResponse(__('message.otp_verification.send_success'));
        } catch (\Exception $e) {
            return errorResponse(__('message.otp_verification.send_failure'));
        }
    }

    public function retryOTP(Request $request)
    {
        $default_type = $request->input('default_type');

        return match ($default_type) {
            'email' => $this->sendEmail($request, 'GET'),
            'mobile' => $this->resendOTP($request),
        };
    }

    public function resendOTP($request)
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

            RateLimiter::hit("mobile-otp:{$user->id}", 600);

            $response = app(SmsOtpController::class)->sendForReOtp($user->mobile_code.$user->mobile, $type, $user->id, 'registration-verify');

            if ($response['type'] === 'error') {
                return errorResponse($response['message']);
            }

            $this->updateVerificationAttempts($user, 'mobile');

            if ($type === 'voice') {
                return successResponse(__('message.otp_verification.resend_voice_send_success'));
            }

            return successResponse(__('message.otp_verification.resend_send_success'));
        } catch (\Exception $exception) {
            return errorResponse(__('message.otp_verification.resend_send_failure'));
        }
    }

    public function sendEmail(Request $request, $method = 'POST')
    {
        $request->validate([
            'eid' => 'required|string',
        ], [
            'eid.required' => __('validation.eid_required'),
            'eid.string' => __('validation.eid_string'),
        ]);
        try {
            $email = Crypt::decrypt($request->eid);

            $user = User::where('email', $email)->firstOrFail();

            $existingToken = AccountActivate::where('email', $email)->latest()->first();
            if ($existingToken && $method !== 'GET' && ! $existingToken->updated_at->addMinutes(10)->isPast()) {
                return successResponse(\Lang::get('message.email_verification.already_sent'));
            }

            RateLimiter::hit("email-otp:{$user->id}", 600);

            $this->sendActivation($email, $method);

            $this->updateVerificationAttempts($user, 'email');

            return successResponse(
                $method === 'GET'
                    ? __('message.email_verification.resend_success')
                    : __('message.email_verification.send_success')
            );
        } catch (\Exception $exception) {
            return errorResponse(__('message.email_verification.send_failure'));
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'eid' => 'required|string',
            'otp' => 'required|string|size:6',
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
                RateLimiter::hit("mobile-verify:{$user->id}", 600);

                return errorResponse(__('message.otp_invalid_format'));
            }

            $response = app(SmsOtpController::class)->sendVerifyOTP($otp, $user->mobile_code.$user->mobile, $user->id, 'registration-verify');
            if ($response['type'] === 'error') {
                RateLimiter::hit("mobile-verify:{$user->id}", 600);

                return errorResponse($response['message']);
            }

            $user->mobile_verified = 1;

            $user->save();

            if (! \Auth::check() && $this->userNeedVerified($user)) {
                //dispatch the job to add user to external services
                AddUserToExternalService::dispatch($user, 'verify');

                \Session::flash('success', __('message.registration_complete'));
            }

            return successResponse(__('message.otp_verified'));
        } catch (\Exception $e) {
            return errorResponse(__('message.error_occurred_while_verify'));
        }
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'eid' => 'required|string',
            'otp' => 'required|string|size:6',
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

            $account = AccountActivate::where('email', $email)->latest()->first(['token', 'updated_at']);

            if (! hash_equals((string) $account->token, (string) $otp)) {
                RateLimiter::hit("email-verify:{$user->id}", 600);

                return errorResponse(__('message.email_verification.invalid_token'));
            }

            if ($account->updated_at->addMinutes(10) < Carbon::now()) {
                RateLimiter::hit("email-verify:{$user->id}", 600);

                return errorResponse(__('message.email_verification.token_expired'));
            }

            AccountActivate::where('email', $email)->delete();

            $user->email_verified = 1;
            $user->save();

            if (! \Auth::check() && $this->userNeedVerified($user)) {
                //dispatch the job to add user to external services
                AddUserToExternalService::dispatch($user, 'verify');

                \Session::flash('success', __('message.registration_complete'));
            }

            return successResponse(__('message.email_verification.email_verified'));
        } catch (\Exception $e) {
            return errorResponse(__('message.email_verification.invalid_token'));
        }
    }

    public function checkVerify($user)
    {
        $check = false;
        if ($user->active == '1' && $user->mobile_verified == '1') {
            \Auth::login($user);
            $check = true;
        }

        return $check;
    }

    public function getState(Request $request, $state)
    {
        try {
            $id = $state;
            $states = \App\Model\Common\State::where('country_code_char2', $id)
            ->orderBy('state_subdivision_name', 'asc')->get();

            if (count($states) > 0) {
                echo '<option value="">'.__('message.choose').'</option>';
                foreach ($states as $stateList) {
                    echo '<option value='.$stateList->iso2.'>'
                .$stateList->state_subdivision_name.'</option>';
                }
            } else {
                echo "<option value=''>".__('message.no_states_available').'</option>';
            }
        } catch (\Exception $ex) {
            echo "<option value=''>".__('message.problem_while_loading').'</option>';

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function salesManagerMail($user, $bcc = [])
    {
        $contact = getContactData();
        $manager = $user->manager()

            ->where('position', 'manager')
            ->select('first_name', 'last_name', 'email', 'mobile_code', 'mobile', 'skype')
            ->first();
        $settings = new \App\Model\Common\Setting();
        $setting = $settings->first();
        $from = $setting->email;
        $to = $user->email;
        $templates = new \App\Model\Common\Template();
        $template = $templates
                ->join('template_types', 'templates.type', '=', 'template_types.id')
                ->where('template_types.name', '=', 'sales_manager_email')
                ->select('templates.data', 'templates.name', 'type')
                ->first();
        $template_data = $template->data;
        $template_name = $template->name;
        $template_controller = new \App\Http\Controllers\Common\TemplateController();
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
        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $mail->SendEmail($from, $to, $template_data, $template_name, 'sales-manager-mail', $replace, TemplateType::where('id', $template->type)->value('name'), $bcc);
    }

    public function accountManagerMail($user, $bcc = [])
    {
        $contact = getContactData();
        $manager = $user->accountManager()

            ->where('position', 'account_manager')
            ->select('first_name', 'last_name', 'email', 'mobile_code', 'mobile', 'skype')
            ->first();
        $settings = new \App\Model\Common\Setting();
        $setting = $settings->first();
        $from = $setting->email;
        $to = $user->email;
        $templates = new \App\Model\Common\Template();
        $template = $templates
                ->join('template_types', 'templates.type', '=', 'template_types.id')
                ->where('template_types.name', '=', 'account_manager_email')
                ->select('templates.data', 'templates.name', 'type')
                ->first();
        $template_data = $template->data;
        $template_name = $template->name;
        $template_controller = new \App\Http\Controllers\Common\TemplateController();
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
        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $mail->SendEmail($from, $to, $template_data, $template_name, 'account-manager-mail', $replace, TemplateType::where('id', $template->type)->value('name'), $bcc);
    }

    public function verify()
    {
        $sessionUser = \Session::get('user');
        if (! $sessionUser) {
            return redirect('login');
        }

        $user = User::find($sessionUser->id);
        $eid = Crypt::encrypt($user->email);

        $setting = StatusSetting::select(
            'emailverification_status',
            'msg91_status',
        )->first();

        $isMobileVerified = ! ($setting->msg91_status == 1 && $user->mobile_verified != 1);
        $isEmailVerified = ! ($setting->emailverification_status == 1 && $user->email_verified != 1);

        $verification_preference = ApiKey::value('verification_preference') ?? ($isEmailVerified ? 'email' : 'mobile');

        return view('themes.default1.user.verify', compact(
            'user', 'eid', 'setting', 'isMobileVerified', 'isEmailVerified', 'verification_preference'
        ));
    }

    public function addUserToExternalServices($user, $options = [])
    {
        try {
            $status = StatusSetting::select('mailchimp_status', 'pipedrive_status', 'zoho_status')->first();

            if (! ($options['skip_pipedrive'] ?? false) && $status->pipedrive_status) {
                (new PipedriveController())->addUserToPipedrive($user);
            }

            if (! ($options['skip_zoho'] ?? false) && $status->zoho_status) {
                $this->addUserToZoho($user, $status->zoho_status);
            }

            if (! ($options['skip_mailchimp'] ?? false) && $status->mailchimp_status) {
                $this->addUserToMailchimp($user, $status->mailchimp_status);
            }
        } catch (\Exception $exception) {
            \Logger::exception($exception);
        }
    }

    public function updateUserWithVerificationStatus($user, $trigger = 'register')
    {
        $pipedriveVerificationRequired = ApiKey::first()->value('require_pipedrive_user_verification');
        $statusSetting = StatusSetting::first([
            'emailverification_status',
            'msg91_status',
            'mailchimp_status',
            'pipedrive_status',
            'zoho_status',
        ]);

        $emailRequired = $statusSetting->emailverification_status;
        $mobileRequired = $statusSetting->msg91_status;
        $isEmailVerified = ! $emailRequired || $user->email_verified;
        $isMobileVerified = ! $mobileRequired || $user->mobile_verified;
        $isFullyVerified = $isEmailVerified && $isMobileVerified;

        // Determine when to sync each service
        $shouldSync = $this->shouldSyncServices($trigger, $pipedriveVerificationRequired, $isFullyVerified);

        if ($shouldSync['sync_any']) {
            $this->addUserToExternalServices($user, [
                'skip_pipedrive' => ! $shouldSync['pipedrive'],
                'skip_zoho' => ! $shouldSync['zoho'],
                'skip_mailchimp' => ! $shouldSync['mailchimp'],
            ]);
        }
    }

    private function shouldSyncServices($trigger, $pipedriveVerificationRequired, $isFullyVerified)
    {
        $syncPipedrive = false;
        $syncZoho = false;
        $syncMailchimp = false;

        if ($pipedriveVerificationRequired) {
            // Pipedrive verification is required
            if ($isFullyVerified) {
                // User just became fully verified - sync all services
                $syncPipedrive = true;
                $syncZoho = true;
                $syncMailchimp = true;
            }
        } else {
            // Pipedrive verification is NOT required
            if ($trigger === 'register') {
                // Sync all services at registration
                $syncPipedrive = true;
                $syncZoho = true;
                $syncMailchimp = true;
            }
            // For verification triggers when pipedrive verification is disabled, don't sync (already synced at registration)
        }

        return [
            'sync_any' => $syncPipedrive || $syncZoho || $syncMailchimp,
            'pipedrive' => $syncPipedrive,
            'zoho' => $syncZoho,
            'mailchimp' => $syncMailchimp,
        ];
    }

    private function userNeedVerified($user)
    {
        $setting = StatusSetting::first(['emailverification_status', 'msg91_status']);

        return ! (
            ($setting->emailverification_status && ! $user->email_verified) ||
            ($setting->msg91_status && ! $user->mobile_verified) ||
            ! $user->active
        );
    }

    private function updateVerificationAttempts($user, $type = 'email')
    {
        if (! in_array($type, ['email', 'mobile'])) {
            return;
        }

        $verificationAttempt = VerificationAttempt::firstOrCreate(['user_id' => $user->id]);

        $field = $type.'_attempt';
        $verificationAttempt->{$field}++;

        $verificationAttempt->save();
    }

    public function verifySession()
    {
        return successResponse('active');
    }
}
