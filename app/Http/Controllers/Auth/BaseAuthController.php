<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Controller;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Common\TemplateType;
use App\Model\User\AccountActivate;
use App\User;
use Exception;

class BaseAuthController extends Controller
{
    public function sendActivation(mixed $email, mixed $method): void
    {
        $user = User::where('email', $email)->first();
        $contact = getContactData();
        if (! $user) {
            throw new Exception(__('message.activation_link_sent'));
        }

        try {
            $activate_model = new AccountActivate();

            if ($method == 'GET') {
                $response = $activate_model->where('email', $email)->first();

                if ($response) {
                    $token = random_int(100000, 999999);
                    $response->update(['token' => $token]);
                } else {
                    // Create a new record if it doesn't exist
                    $token = random_int(100000, 999999);
                    $activate_model->create(['email' => $email, 'token' => $token]);
                }
            } else {
                // For non-GET methods, always create a new record
                $token = random_int(100000, 999999);
                $activate_model->create(['email' => $email, 'token' => $token]);
            }

            // Check the settings
            /** @var \App\Model\Common\Setting $settings */
            $settings = Setting::find(1);

            // Retrieve the template
            /** @var \App\Model\Common\Template $template */
            $template = TemplateType::getSelectedTemplate('welcome_mail');
            $website_url = url('/');
            $replace = [
                'name' => $user->first_name.' '.$user->last_name,
                'username' => $user->email,
                'otp' => $token,
                'website_url' => $website_url,
                'contact' => $contact['contact'],
                'logo' => $contact['logo'],
                'company_email' => $settings->company_email,
                'reply_email' => $settings->company_email,
            ];

            $type = $template->type()->value('name') ?? '';

            $mail = new PhpMailController();
            $mail->SendEmail($settings->email, $user->email, $template->data, $template->name, $template->type()->value('name'), $replace, $type);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    protected function userNeedVerified(User $user): bool
    {
        /** @var \App\Model\Common\StatusSetting $setting */
        $setting = StatusSetting::first(['emailverification_status', 'msg91_status']);

        if ($setting->emailverification_status == 1 && $user->email_verified != 1) {
            return false;
        }

        if ($setting->msg91_status == 1 && $user->mobile_verified != 1) {
            return false;
        }

        return $user->active == 1;
    }
}
