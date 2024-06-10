<?php

namespace App\Http\Controllers\Auth;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Model\Common\StatusSetting;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showLinkRequestForm()
    {
        $status = StatusSetting::select('recaptcha_status', 'msg91_status', 'emailverification_status', 'terms')->first();
        $apiKeys = ApiKey::select('nocaptcha_sitekey', 'captcha_secretCheck', 'msg91_auth_key', 'terms_url')->first();

        return view('themes.default1.front.auth.password', compact('status', 'apiKeys'));
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendResetLinkEmail(Request $request)
    {
        try {
            $apiKeys = StatusSetting::value('recaptcha_status');
            $captchaRule = $apiKeys ? 'required|' : 'sometimes|';
            $this->validate($request,
                ['email' => 'required|email|exists:users,email',
                    'pass-recaptcha-response-1' => $captchaRule.'captcha',
                ]
            );
            $email = $request->email;
            $token = str_random(40);
            $password = new \App\Model\User\Password();
            if ($password->where('email', $email)->first()) {
                $password->where('email', $email)->update(['created_at' => \Carbon\Carbon::now()]);
                $token = $password->where('email', $email)->first()->token;
            } else {
                $activate = $password->create(['email' => $email, 'token' => $token, 'created_at' => \Carbon\Carbon::now()]);
                $token = $activate->token;
            }

            $url = url("password/reset/$token");

            $user = new \App\User();
            $user = $user->where('email', $email)->first();
            if (! $user) {
                return redirect()->back()->with('fails', 'Invalid Email');
            }
            //check in the settings
            $settings = new \App\Model\Common\Setting();
            $setting = $settings::find(1);
            //template
            $templates = new \App\Model\Common\Template();
            $temp_id = $setting->forgot_password;
            $template = $templates->where('id', $temp_id)->first();

            $contact = getContactData();
            $replace = ['name' => $user->first_name.' '.$user->last_name, 'url' => $url, 'contact_us' => $setting->website, 'contact' => $contact['contact'],
                'logo' => $contact['logo'], 'reply_email' => $setting->company_email];
            $from = $setting->email;
            $to = $user->email;
            $contactUs = $setting->website;
            $subject = $template->name;
            $data = $template->data;
            $type = '';

            if ($template) {
                $type_id = $template->type;
                $temp_type = new \App\Model\Common\TemplateType();
                $type = $temp_type->where('id', $type_id)->first()->name;
            }
            if (emailSendingStatus()) {
                $mail = new \App\Http\Controllers\Common\PhpMailController();
                $mail->SendEmail($setting->email, $user->email, $template->data, $template->name, $replace, $type);
                $response = ['type' => 'success',   'message' => 'Reset instructions have been mailed to '.$user->email.'. Be sure to check your Junk folder if you do not see an email from us in your Inbox within a few minutes.'];
            } else {
                $response = ['type' => 'fails',   'message' => 'System email is not configured. Please contact admin.'];
            }

            return response()->json($response);
        } catch (\Exception $ex) {
            dd($ex);
            $result = ['Reset instructions have been mailed to '.$request->email.'. Be sure to check your Junk folder if you do not see an email from us in your Inbox within a few minutes.'];

            return response()->json(compact('result'), 500);
        }
    }
}
