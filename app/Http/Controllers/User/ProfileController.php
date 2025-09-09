<?php

namespace App\Http\Controllers\User;

use App\Facades\Attach;
use App\Http\Controllers\Controller;
use App\Jobs\AddUserToExternalService;
use App\Rules\CaptchaValidation;
use App\VerificationAttempt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\User\ProfileRequest;
use App\User;
use Hash;
use App\Model\User\AccountActivate;
use App\Http\Controllers\Auth\BaseAuthController;
use Illuminate\Support\Facades\Crypt;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function profile()
    {
        try {
            $user = \Auth::user();
            $timezonesList = \App\Model\Common\Timezone::get();
            $is2faEnabled = $user->is_2fa_enabled;
            $dateSinceEnabled = $user->google2fa_activation_date;
            foreach ($timezonesList as $timezone) {
                $location = $timezone->location;
                if ($location) {
                    $start = strpos($location, '(');
                    $end = strpos($location, ')', $start + 1);
                    $length = $end - $start;
                    $result = substr($location, $start + 1, $length - 1);
                    $display[] = ['id' => $timezone->id, 'name' => '('.$result.')'.' '.$timezone->name];
                }
            }
            //for display
            $timezones = array_column($display, 'name', 'id');
            $state = getStateByCode($user->state);
            $states = findStateByRegionId($user->country);
            $bussinesses = \App\Model\Common\Bussiness::pluck('name', 'short')->toArray();

            return view('themes.default1.user.profile', compact('bussinesses', 'user', 'timezones', 'state', 'states', 'is2faEnabled', 'dateSinceEnabled'));
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function updateProfile(ProfileRequest $request)
    {
        try {
            $user = \Auth::user();
            if ($request->hasFile('profile_pic')) {
                $path = Attach::put('common/images/users/', $request->file('profile_pic'), null, true);
                $user->profile_pic = basename($path);
            }
            $user->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function updatePassword(ProfileRequest $request)
    {
        try {
            $user = \Auth::user();
            $oldpassword = $request->input('old_password');
            $currentpassword = $user->getAuthPassword();
            $newpassword = $request->input('new_password');
            if (Hash::check($oldpassword, $currentpassword)) {
                $user->password = Hash::make($newpassword);
                $user->save();

                //logout all other session when password is updated
                \Auth::logoutOtherDevices($newpassword);

                \DB::table('password_resets')->where('email', $user->email)->delete();

                return redirect()->back()->with('success1', \Lang::get('message.updated-successfully'));
            } else {
                return redirect()->back()->with('fails1', __('message.incorrect_old_password'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function sendNewEmailVerification(Request $request, $method = 'POST')
    {
        $request->validate([
            'new_email' => 'required|email',
        ], [
            'new_email.required' => __('validation.email_required'),
            'new_email.email' => __('validation.email_invalid'),
        ]);

        try {
            $newEmail = $request->new_email;
            $existingEmail = $request->confirmed_email;
            $user = auth()->user();// logged-in user
            $userEmail = $user->email;

//            if($existingEmail === $userEmail){
//                $this->sendActivationForEdit($user, $userEmail, $method);
//                return successResponse(__('The code sent to your existing email address.'));
//            }

            if (AccountActivate::where('email', $newEmail)->first() && $method !== 'GET') {
                return successResponse(__('message.email_verification.already_sent'));
            }

            // ✅ Pass user object to sendActivationForEdit
            $this->sendActivationForEdit($user, $newEmail, $method);

            return successResponse(
                $method === 'GET'
                    ? __('message.email_verification.resend_success')
                    : __('message.email_verification.send_success')
            );
        } catch (\Exception $exception) {
            return errorResponse(__('message.email_verification.send_failure'));
        }
    }

    public function sendActivationForEdit($user, $email, $method)
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

            // Get template
            $template = \App\Model\Common\Template::find($settings->welcome_mail);
            $website_url = url('/');

            $replace = [
                'name'         => $user->first_name . ' ' . $user->last_name,
                'username'     => $user->email, // old email
                'new_email'    => $email,       // ✅ show new email in template if needed
                'otp'          => $token,
                'website_url'  => $website_url,
                'contact'      => $contact['contact'],
                'logo'         => $contact['logo'],
                'company_email'=> $settings->company_email,
                'reply_email'  => $settings->company_email,
            ];

            $type = '';
            if ($template) {
                $type_id = $template->type;
                $temp_type = new \App\Model\Common\TemplateType();
                $type = $temp_type->where('id', $type_id)->first()->name;
            }

            $mail = new \App\Http\Controllers\Common\PhpMailController();
            // ✅ Send to NEW email
            $mail->SendEmail($settings->email, $email, $template->data, $template->name, $replace, $type);

        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function verifyOtpForEditEmail(Request $request)
    {
        $request->validate([
            'new_email' => 'required|email',
            'otp' => 'required|string|size:6',
        ], [
            'new_email.required' => __('validation.verify_otp.email_required'),
            'new_email.email' => __('validation.verify_otp.email_invalid'),
            'otp.required' => __('validation.verify_otp.otp_required'),
            'otp.size' => __('validation.verify_otp.otp_size'),
        ]);

        try {
            $otp = $request->input('otp');
            $email = $request->input('new_email');

            $account = AccountActivate::where('email', $email)->latest()->first(['token', 'updated_at']);

            if (!$account || $account->token !== $otp) {
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

    public function sendCodeToExistingEmail() {

    }

//    public function verifyOtpForEditEmail(Request $request)
//    {
//        $request->validate([
//            'otp' => 'required|string|size:6',
//        ],
//            [
//                'eid.required' => __('validation.verify_otp.eid_required'),  // Translating for eid field
//                'eid.string' => __('validation.verify_otp.eid_string'),
//                'otp.required' => __('validation.verify_otp.otp_required'),
//                'otp.size' => __('validation.verify_otp.otp_size'),
//                'g-recaptcha-response.required' => __('validation.verify_otp.recaptcha_required'),
//            ]);
//
//        try {
//            $otp = $request->input('otp');
//
//            // Decrypt the email
//            $email = $request->input('new_email');
//
//          //  $user = User::where('email', $email)->firstOrFail();
//
//            $account = AccountActivate::where('email', $email)->latest()->first(['token', 'updated_at']);
//
//            if ($account->token !== $otp) {
//                return errorResponse(__('message.email_verification.invalid_token'));
//            }
//
//            if ($account->updated_at->addMinutes(10) < Carbon::now()) {
//                return errorResponse(__('message.email_verification.token_expired'));
//            }
//
//            AccountActivate::where('email', $email)->delete();
//
//
//            return successResponse(__('message.email_verification.email_verified'));
//        } catch (\Exception $e) {
//            return errorResponse(__('message.email_verification.invalid_token'));
//        }
//    }
}
