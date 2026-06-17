<?php

namespace App\Plugins\Recaptcha\Services;

use Http;

class RecaptchaVerifier
{
    /**
     * Returns true if successful, otherwise returns an error message.
     */
    public function verify(
        string $response,
        string $secretKey,
        string $type,
        string $ip,
        string $expectedHostname,
        float $scoreThreshold = 0.5
    ): array|string|null|true {
        $httpResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secretKey,
            'response' => $response,
            'remoteip' => $ip,
        ]);

        $responseBody = $httpResponse->json();

        if (! ($responseBody['success'] ?? false)) {
            return __('recaptcha::recaptcha.invalid_secret_or_token');
        }

        if ($type === 'v3' && (($responseBody['action'] ?? '') !== 'settings_save' || ($responseBody['hostname'] ?? '') !== $expectedHostname)) {
            return __('recaptcha::recaptcha.captcha_verification_failed');
        }

        return true;
    }
}
