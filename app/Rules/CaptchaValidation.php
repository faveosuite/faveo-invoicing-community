<?php

namespace App\Rules;

use App\ApiKey;
use App\Model\Common\StatusSetting;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CaptchaValidation implements ValidationRule
{
    protected $message;
    protected $action;
    protected $implicit;

    public function __construct($action = 'default', $message = null, $implicit = false)
    {
        $this->message = $message ?? __('message.captcha_message');
        $this->action = $action;
        $this->implicit = $implicit;
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
            $v2Enabled = $settings->recaptcha_status === 1;
            $v3Enabled = $settings->v3_recaptcha_status === 1;
            $V2V3Enabled = $settings->v3_v2_recaptcha_status === 1;
            if (! $v2Enabled && ! $v3Enabled && ! $V2V3Enabled) {
                return; // Skip validation if disabled
            }
            // Get the secret key
            $secretKey = config('services.recaptcha.secret') ?? ApiKey::find(1)?->captcha_secretCheck;
            $expectedHostname = request()->getHost();
            $minScore = config('services.recaptcha.score', 0.5);

            // Make the reCAPTCHA request
            $response = \Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);

            $responseBody = $response->json();

            if (isset($responseBody['success']) && ! $responseBody['success']) {
                $fail($this->message);

                return;
            }

            if ($v3Enabled && isset($responseBody['score'])) {
                if ($responseBody['score'] < $minScore || $this->action !== $responseBody['action'] || ($responseBody['hostname'] ?? null) !== $expectedHostname) {
                    $fail($this->message);

                    return;
                }
            }
        } catch (\Exception $e) {
            $fail($this->message);
        }
    }
}
