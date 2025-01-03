<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\License\LicenseController;
use App\Model\Common\StatusSetting;
use App\Model\User\AccountActivate;
use App\Rules\CaptchaValidation;
use App\User;
use App\VerificationAttempt;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
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

    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
        $license = new LicenseController();
        $this->licensing = $license;
    }

    public function activate($token, AccountActivate $activate, Request $request, User $user)
    {
        try {
            $activate = $activate->where('token', $token)->first();
            $url = 'login';
            if ($activate) {
                $email = $activate->email;
            } else {
                throw new NotFoundHttpException('Token mismatch. Account cannot be activated.');
            }
            $user = $user->where('email', $email)->first();
            if ($user) {
                if ($user->active == 0) {
                    $user->active = 1;
                    $this->emailverificationAttempt($user);
                    $user->save();
                    $status = StatusSetting::select('mailchimp_status', 'pipedrive_status', 'zoho_status')->first();
                    $this->addUserToPipedrive($user, $status->pipedrive_status); //Add user to pipedrive
                    $this->addUserToZoho($user, $status->zoho_status); //Add user to zoho
                    $this->addUserToMailchimp($user, $status->mailchimp_status); // Add user to mailchimp
                    if (\Session::has('session-url')) {
                        $url = \Session::get('session-url');

                        return redirect($url);
                    }

                    return redirect($url)->with('success', 'Email verification successful.
                    Please login to access your account !!');
                } else {
                    return redirect($url)->with('warning', 'This email is already verified');
                }
            } else {
                throw new NotFoundHttpException('User with this email not found.');
            }
        } catch (\Exception $ex) {
            if ($ex->getCode() == 400) {
                return redirect($url)->with('success', 'Email verification successful,
                 Please login to access your account');
            }

            return redirect($url)->with('fails', $ex->getMessage());
        }
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    public function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    public function requestOtp(Request $request)
    {
        $request->validate([
            'eid' => 'required|string',
        ]);

        try {
            // Decrypt the email
            $email = Crypt::decrypt($request->eid);

            // Find the user by email
            $user = User::where('email', $email)->firstOrFail();

            if ($user->mobile_verified) {
                return errorResponse(__('message.mobile_already_verified'));
            }

            // Handle verification attempts
            $attempts = VerificationAttempt::firstOrCreate(['user_id' => $user->id]);

            if ($attempts->updated_at->lte(Carbon::now()->subHours(6))) {
                $attempts->update([
                    'mobile_attempt' => 0,
                    'email_attempt' => 0,
                ]);
            }

            if ($attempts->mobile_attempt >= 3) {
                return errorResponse(__('message.otp_verification.max_attempts_exceeded'));
            }

            $attempts->mobile_attempt = (int) $attempts->mobile_attempt + 1;
            $attempts->save();

            if (! $this->sendOtp($user->mobile_code.$user->mobile)) {
                return errorResponse(__('message.otp_verification.send_failure'));
            }

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
        ]);
        try {
            $email = Crypt::decrypt($request->eid);
            $type = $request->input('type');

            $user = User::where('email', $email)->firstOrFail();

            // Handle verification attempts
            $attempts = VerificationAttempt::firstOrCreate(['user_id' => $user->id]);

            if ($attempts->updated_at->lte(Carbon::now()->subHours(6))) {
                $attempts->update([
                    'mobile_attempt' => 0,
                    'email_attempt' => 0,
                ]);
            }

            if ($attempts->mobile_attempt >= 3) {
                return errorResponse(__('message.otp_verification.resend_max_attempts_exceeded'));
            }

            $attempts->mobile_attempt = (int) $attempts->mobile_attempt + 1;
            $attempts->save();

            if (! $this->sendForReOtp($user->mobile_code.$user->mobile, $type)) {
                return errorResponse(__('message.otp_verification.resend_failure'));
            }

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
        ]);
        try {
            $email = Crypt::decrypt($request->eid);

            $user = User::where('email', $email)->firstOrFail();

            // Handle verification attempts
            $attempts = VerificationAttempt::firstOrCreate(['user_id' => $user->id]);

            if ($attempts->updated_at->lte(Carbon::now()->subHours(6))) {
                $attempts->update([
                    'mobile_attempt' => 0,
                    'email_attempt' => 0,
                ]);
            }

            if ($attempts->email_attempt >= 3) {
                return errorResponse(__('message.email_verification.max_attempts_exceeded'));
            }

            if (AccountActivate::where('email', $email)->first() && $method !== 'GET') {
                return successResponse(\Lang::get('message.email_verification.already_sent'));
            }

            $this->sendActivation($email, $method);

            $attempts->email_attempt = (int) $attempts->email_attempt + 1;
            $attempts->save();

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
        if (rateLimitForKeyIp('verify_mobile_otp', 5, 1, $request->ip())) {
            return errorResponse('Too Many attempts.');
        }

        $request->validate([
            'eid' => 'required|string',
            'otp' => 'required|string|size:6',
            'g-recaptcha-response' => [isCaptchaRequired()['is_required'], new CaptchaValidation()],
        ]);
        try {
            // Decrypt the email
            $email = Crypt::decrypt($request->eid);
            $otp = $request->otp;

            // Find the user by email
            $user = User::where('email', $email)->firstOrFail();

            // Validate OTP
            if (! is_numeric($request->otp)) {
                return errorResponse(__('message.otp_invalid_format'));
            }

            if (! $this->sendVerifyOTP($otp, $user->mobile_code.$user->mobile)) {
                return errorResponse(__('message.otp_invalid'));
            }
            $user->mobile_verified = 1;
            $user->save();

            if (! \Auth::check() && StatusSetting::first()->value('emailverification_status') !== 1) {
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
            'g-recaptcha-response' => [isCaptchaRequired()['is_required'], new CaptchaValidation()],
        ]);

        try {
            $otp = $request->input('otp');

            if (rateLimitForKeyIp('request_email', 5, 1, $request->ip())) {
                return errorResponse(__('message.email_verification.max_attempts_exceeded'));
            }
            // Decrypt the email
            $email = Crypt::decrypt($request->eid);

            $user = User::where('email', $email)->firstOrFail();

            $account = AccountActivate::where('email', $email)->latest()->first(['token', 'updated_at']);

            if ($account->token !== $otp) {
                return errorResponse(__('message.email_verification.invalid_token'));
            }

            if ($account->updated_at->addMinutes(10) < Carbon::now()) {
                return errorResponse(__('message.email_verification.token_expired'));
            }

            AccountActivate::where('email', $email)->delete();

            $user->active = 1;
            $user->save();

            if (! \Auth::check() && StatusSetting::first()->value('emailverification_status') === 1) {
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
                echo '<option value="">Choose</option>';
                foreach ($states as $stateList) {
                    echo '<option value='.$stateList->state_subdivision_code.'>'
                .$stateList->state_subdivision_name.'</option>';
                }
            } else {
                echo "<option value=''>No States Available</option>";
            }
        } catch (\Exception $ex) {
            echo "<option value=''>Problem while loading</option>";

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
                ->select('templates.data', 'templates.name')
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
        $mail->SendEmail($from, $to, $template_data, $template_name, $replace, 'sales_manager_email', $bcc);
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
                ->select('templates.data', 'templates.name')
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
        $mail->SendEmail($from, $to, $template_data, $template_name, $replace, 'account_manager_email', $bcc);
    }

    public function verify()
    {
        $user = \Session::get('user');
        if ($user) {
            $eid = Crypt::encrypt($user->email);
            $setting = StatusSetting::first(['recaptcha_status', 'v3_recaptcha_status', 'emailverification_status', 'msg91_status']);

            return view('themes.default1.user.verify', compact('user', 'eid', 'setting'));
        }

        return redirect('login');
    }
}
