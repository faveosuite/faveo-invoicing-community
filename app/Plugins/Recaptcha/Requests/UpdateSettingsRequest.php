<?php

namespace App\Plugins\Recaptcha\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'v2_site_key' => 'nullable|string',
            'v2_secret_key' => 'nullable|string',
            'v3_site_key' => 'nullable|string',
            'v3_secret_key' => 'nullable|string',
            'captcha_version' => 'required|in:v2_checkbox,v2_invisible,v3_invisible',
            'failover_action' => 'required|in:none,v2_checkbox',
            'score_threshold' => 'required|numeric|min:0|max:1',
            'theme' => 'required|in:light,dark',
            'size' => 'required|in:normal,compact',
            'badge_position' => 'required|in:bottomright,bottomleft,inline',
            'v2_g_recaptcha_response' => 'nullable|string',
            'v3_g_recaptcha_response' => 'nullable|string',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'v2_site_key' => __('recaptcha::recaptcha.v2_site_key'),
            'v2_secret_key' => __('recaptcha::recaptcha.v2_secret_key'),
            'v3_site_key' => __('recaptcha::recaptcha.v3_site_key'),
            'v3_secret_key' => __('recaptcha::recaptcha.v3_secret_key'),
            'captcha_version' => __('recaptcha::recaptcha.captcha_version'),
            'failover_action' => __('recaptcha::recaptcha.failover_action'),
            'score_threshold' => __('recaptcha::recaptcha.v3_score_threshold'),
            'theme' => __('recaptcha::recaptcha.theme'),
            'size' => __('recaptcha::recaptcha.size'),
            'badge_position' => __('recaptcha::recaptcha.badge_position'),
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'captcha_version.required' => __('recaptcha::recaptcha.captcha_version_required'),
            'captcha_version.in' => __('recaptcha::recaptcha.captcha_version_in'),
            'failover_action.required' => __('recaptcha::recaptcha.failover_action_required'),
            'failover_action.in' => __('recaptcha::recaptcha.failover_action_in'),
            'score_threshold.required' => __('recaptcha::recaptcha.score_threshold_required'),
            'score_threshold.numeric' => __('recaptcha::recaptcha.score_threshold_numeric'),
            'score_threshold.min' => __('recaptcha::recaptcha.score_threshold_min'),
            'score_threshold.max' => __('recaptcha::recaptcha.score_threshold_max'),
            'theme.required' => __('recaptcha::recaptcha.theme_required'),
            'theme.in' => __('recaptcha::recaptcha.theme_in'),
            'size.required' => __('recaptcha::recaptcha.size_required'),
            'size.in' => __('recaptcha::recaptcha.size_in'),
            'badge_position.required' => __('recaptcha::recaptcha.badge_position_required'),
            'badge_position.in' => __('recaptcha::recaptcha.badge_position_in'),
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // v2 verification
            if ($this->filled('v2_g_recaptcha_response') && $this->filled('v2_secret_key')) {
                $result = $this->verifyCaptcha(
                    $this->input('v2_g_recaptcha_response'),
                    $this->input('v2_secret_key'),
                    'v2',
                    $this->ip(),
                    $this->getHost()
                );

                if ($result !== true) {
                    $validator->errors()->add('v2_secret_key', $result);
                }
            }

            // v3 verification
            if ($this->filled('v3_g_recaptcha_response') && $this->filled('v3_secret_key')) {
                $result = $this->verifyCaptcha(
                    $this->input('v3_g_recaptcha_response'),
                    $this->input('v3_secret_key'),
                    'v3',
                    $this->ip(),
                    $this->getHost(),
                    $this->input('score_threshold')
                );

                if ($result !== true) {
                    $validator->errors()->add('v3_secret_key', $result);
                }
            }

            if (! $this->filled('v2_g_recaptcha_response') && ! $this->filled('v3_g_recaptcha_response')) {
                $validator->errors()->add('captcha', __('recaptcha::recaptcha.captcha_verification_failed'));
            }
        });
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
            return __('recaptcha::recaptcha.invalid_secret_or_token');
        }

        if ($type === 'v3') {
            if (
                ($responseBody['action'] ?? '') !== 'settings_save' ||
                ($responseBody['hostname'] ?? '') !== $expectedHostname
            ) {
                return __('recaptcha::recaptcha.captcha_verification_failed');
            }
        }

        return true;
    }
}
