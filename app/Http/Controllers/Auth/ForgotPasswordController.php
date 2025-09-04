<?php

namespace App\Http\Controllers\Auth;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Model\Common\StatusSetting;
use App\Rules\Honeypot;
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

        $this->middleware(['recaptcha:forgot'])->only('sendResetLinkEmail');
    }

    public function showLinkRequestForm()
    {
        $status = StatusSetting::select('recaptcha_status', 'v3_recaptcha_status', 'msg91_status', 'emailverification_status', 'terms')->first();
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
            $this->validate($request,
                ['email' => 'required|email|exists:users,email',
                    'forgot' => [new Honeypot()],
                ],
                [
                    'email.required' => __('validation.custom_email.required'),
                    'email.email' => __('validation.custom_email.email'),
                    'email.exists' => __('validation.custom_email.exists'),
                ]);
            $email = $request->email;

            $rateLimit = rateLimitForKeyIp('forgot_password'.$email, 3, 360, $request->ip());

            if ($rateLimit['status']) {
                return errorResponse(__('message.too_many_forgot_attempts', ['time' => $rateLimit['remainingTime']]));
            }

            $token = str_random(40);
            $password = new \App\Model\User\Password();
            if ($password->where('email', $email)->first()) {
                $password->where('email', $email)->delete();
            }
            $activate = $password->create(['email' => $email, 'token' => $token, 'created_at' => \Carbon\Carbon::now()]);
            $token = $activate->token;

            $url = url("password/reset/$token");

            $user = new \App\User();
            $user = $user->where('email', $email)->firstOrFail();

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

                return successResponse(__('message.reset_instructions', ['email' => $request->email]));
            } else {
                return errorResponse(__('message.system_email_not_configured'));
            }

            return successResponse(__('message.reset_instructions', ['email' => $request->email]));
        } catch (\Exception $ex) {
            return successResponse(__('message.reset_instructions', ['email' => $request->email]));
        }
    }
}
