<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Controller;
use App\Model\Common\Setting;
use App\Model\Common\TemplateType;
use App\Model\User\Password;
use App\Rules\Honeypot;
use App\User;
use Exception;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

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
     */
    public function __construct()
    {
        $this->middleware('guest');

        $this->middleware(['recaptcha:forgot'])->only('sendResetLinkEmail');
    }

    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validate($request,
            ['email' => 'required|email|exists:users,email',
                'forgot' => [new Honeypot()],
            ],
            [
                'email.required' => __('validation.custom_email.required'),
                'email.email' => __('validation.custom_email.email'),
                'email.exists' => __('validation.custom_email.exists'),
            ]);

        try {
            $email = $request->email;

            $rateLimit = rateLimitForKeyIp('forgot_password'.$email, 3, 360, (string) $request->ip());

            if ($rateLimit['status']) {
                return errorResponse(__('message.too_many_forgot_attempts', ['time' => $rateLimit['remainingTime']]));
            }

            $token = Str::random(40);
            $password = new Password();
            if ($password->where('email', $email)->first()) {
                $password->where('email', $email)->delete();
            }

            $activate = $password->create(['email' => $email, 'token' => $token, 'created_at' => Date::now()]);
            $token = $activate->token;

            $url = url('password/reset/'.$token);

            $user = new User();
            $user = $user->where('email', $email)->firstOrFail();

            //check in the settings
            /** @var \App\Model\Common\Setting $setting */
            $setting = Setting::find(1);
            //template
            /** @var \App\Model\Common\Template $template */
            $template = TemplateType::getSelectedTemplate('forgot_password_mail');

            $contact = getContactData();
            $replace = ['name' => $user->first_name.' '.$user->last_name, 'url' => $url, 'contact_us' => $setting->website, 'contact' => $contact['contact'],
                'logo' => $contact['logo'], 'reply_email' => $setting->company_email];
            $from = $setting->email;
            $to = $user->email;
            $contactUs = $setting->website;
            $subject = $template->name;
            $data = $template->data;
            $type = $template?->type()->value('name') ?? '';
            if (emailSendingStatus()) {
                $mail = new PhpMailController();
                $mail->SendEmail($setting->email, $user->email, $template->data, $template->name, $template->type()->value('name'), $replace, $type);

                return successResponse(__('validation.forgot_email_validation'));
            }

            return errorResponse(__('validation.forgot_email_validation'));
        } catch (Exception) {
            return successResponse(__('validation.forgot_email_validation'));
        }
    }
}
