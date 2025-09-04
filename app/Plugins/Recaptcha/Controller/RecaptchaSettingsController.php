<?php

namespace App\Plugins\Recaptcha\Controller;

use App\Http\Controllers\Controller;
use App\Plugins\Recaptcha\Model\RecaptchaSetting;
use Illuminate\Http\Request;
use Validator;

class RecaptchaSettingsController extends Controller
{
    public function settings()
    {
        return view('recaptcha::setting');
    }

    public function getSettings(Request $request)
    {
        $settings = RecaptchaSetting::firstOrCreate([]);

        return successResponse('', $settings);
    }

    /**
     * Update settings.
     */
    public function updateSettings(Request $request)
    {
        // Basic validation
        $validator = Validator::make($request->all(), [
            'v2_site_key' => 'nullable|string',
            'v2_secret_key' => 'nullable|string',
            'v3_site_key' => 'nullable|string',
            'v3_secret_key' => 'nullable|string',
            'captcha_version' => 'required|in:v2_checkbox,v2_invisible,v3_invisible',
            'failover_action' => 'required|in:none,v2_checkbox',
            'score_threshold' => 'required|numeric|min:0|max:1',
            'error_message' => 'required|string|max:255',
            'theme' => 'required|in:light,dark',
            'size' => 'required|in:normal,compact',
            'badge_position' => 'required|in:bottomright,bottomleft,inline',
            'v2_g_recaptcha_response' => 'nullable|string',
            'v3_g_recaptcha_response' => 'nullable|string',
        ]);

        // Add custom after-validation checks
        $validator->after(function ($validator) use ($request) {
            // v2 verification
            if ($request->filled('v2_g_recaptcha_response') && $request->filled('v2_secret_key')) {
                $result = $this->verifyCaptcha($request->input('v2_g_recaptcha_response'), $request->input('v2_secret_key'), 'v2', $request->ip(), $request->getHost());
                if ($result !== true) {
                    $validator->errors()->add('v2_secret_key', $result);
                }
            }

            // v3 verification
            if ($request->filled('v3_g_recaptcha_response') && $request->filled('v3_secret_key')) {
                $result = $this->verifyCaptcha($request->input('v3_g_recaptcha_response'), $request->input('v3_secret_key'), 'v3', $request->ip(), $request->getHost(), $request->input('score_threshold'));
                if ($result !== true) {
                    $validator->errors()->add('v3_secret_key', $result);
                }
            }
        });

        // Step 3: Fail if validation errors exist
        $validator->validate();

        // Step 4: Save settings
        $settings = RecaptchaSetting::firstOrCreate([]);
        $settings->update($request->only([
            'v2_site_key', 'v2_secret_key', 'v3_site_key', 'v3_secret_key',
            'captcha_version', 'failover_action', 'score_threshold', 'language',
            'error_message', 'theme', 'size', 'badge_position',
        ]));

        return successResponse('reCAPTCHA settings updated successfully!');
    }

    /**
     * Returns true if successful, otherwise returns error message.
     */
    protected function verifyCaptcha($response, $secretKey, $type, $ip, $expectedHostname, $scoreThreshold = 0.5)
    {
        $httpResponse = \Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secretKey,
            'response' => $response,
            'remoteip' => $ip,
        ]);

        $responseBody = $httpResponse->json();

        if (! $responseBody['success']) {
            return 'Secret key or response token is invalid';
        }

        if ($type === 'v3') {
            if (
                ($responseBody['score'] ?? 0) < $scoreThreshold ||
                ($responseBody['action'] ?? '') !== 'settings_save' ||
                ($responseBody['hostname'] ?? '') !== $expectedHostname
            ) {
                return 'Captcha verification failed (score/action/hostname mismatch)';
            }
        }

        return true;
    }
}
