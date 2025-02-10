<?php

namespace App\Rules;

use App\ApiKey;
use App\Model\Common\StatusSetting;
use Closure;
use GuzzleHttp\Client;
use Illuminate\Contracts\Validation\ValidationRule;

class CaptchaValidation implements ValidationRule
{
    protected $message;

    public function __construct($message = null)
    {
        $this->message = $message ?? __('message.hacking_attempt');
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            // Check if reCAPTCHA is enabled
            $settings = StatusSetting::find(1);
            if ($settings->v3_recaptcha_status !== 1 && $settings->recaptcha_status !== 1) {
                return; // Skip validation if disabled
            }
            // Get the secret key
            $secretKey = ! empty(env('NOCAPTCHA_SECRET')) ? env('NOCAPTCHA_SECRET') : ApiKey::find(1)->captcha_secretCheck;

            // Make the reCAPTCHA request
            $client = new Client();
            $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => [
                    'secret' => $secretKey,
                    'response' => $value,
                ],
            ]);

            $responseBody = json_decode($response->getBody(), true);
            // Validate response
            if (! $responseBody['success']) {
                $fail($this->message);
            }
        } catch (\Exception $e) {
            $fail($this->message);
        }
    }
}
