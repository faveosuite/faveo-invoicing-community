<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Controller;
use App\Model\Common\Setting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Rules\Honeypot;
use App\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

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
     * Send a reset link to the given user.
     *
     * Laravel's password broker creates and stores the (hashed) token, throttles repeat
     * requests per email and evens out response timing; we only send our own mail template.
     * The reply is the same whether or not the account exists (no account enumeration).
     * Per-IP limit: the 'forgot-password' throttle on the route.
     */
    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        // No `exists` rule: the reply must not reveal whether an account exists.
        $this->validate($request,
            ['email' => 'required|email',
                'honeypot' => [new Honeypot],
            ],
            [
                'email.required' => __('validation.custom_email.required'),
                'email.email' => __('validation.custom_email.email'),
            ]);

        $mailEnabled = emailSendingStatus();

        try {
            Password::broker()->sendResetLink(['email' => $request->input('email')], function (User $user, string $token) use ($mailEnabled): void {
                if ($mailEnabled) {
                    $this->mailResetLink($user, $token);
                }
            });
        } catch (\Throwable $throwable) {
            \Logger::exception($throwable);
        }

        return $mailEnabled
            ? successResponse(__('validation.forgot_email_validation'))
            : errorResponse(__('validation.forgot_email_validation'));
    }

    private function mailResetLink(User $user, string $token): void
    {
        // Email in the link: tokens are stored hashed, so they're looked up by email.
        $url = route('password.reset', ['token' => $token, 'email' => $user->email]);

        /** @var Setting $setting */
        $setting = Setting::find(1);
        /** @var Template $template */
        $template = TemplateType::getSelectedTemplate('forgot_password_mail');
        $contact = getContactData();
        $replace = ['name' => $user->first_name.' '.$user->last_name, 'url' => $url, 'contact_us' => $setting->website, 'contact' => $contact['contact'],
            'logo' => $contact['logo'], 'reply_email' => $setting->company_email];
        $type = $template->type()->value('name') ?? '';

        new PhpMailController()->sendEmail($setting->email, $user->email, $template->data, $template->name, (string) $type, $replace, $type);
    }
}
