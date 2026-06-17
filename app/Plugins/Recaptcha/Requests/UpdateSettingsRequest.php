<?php

namespace App\Plugins\Recaptcha\Requests;

use App\Plugins\Recaptcha\Services\RecaptchaVerifier;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isV3 = $this->input('captcha_version') === 'v3_invisible';
        $needsV2 = in_array($this->input('captcha_version'), ['v2_checkbox', 'v2_invisible'], strict: true)
            || ($this->input('captcha_version') === 'v3_invisible' && $this->input('failover_action') === 'v2_checkbox');

        return [
            'recaptcha_status' => ['sometimes', 'boolean'],
            'captcha_version' => ['required', 'in:v2_checkbox,v2_invisible,v3_invisible'],
            'failover_action' => ['required', 'in:none,v2_checkbox'],
            'theme' => ['required', 'in:light,dark'],
            'size' => ['required', 'in:normal,compact'],
            'badge_position' => ['required', 'in:bottomright,bottomleft,inline'],
            'v3_site_key' => $isV3 ? 'required|string' : 'nullable|string',
            'v3_secret_key' => $isV3 ? 'required|string' : 'nullable|string',
            'score_threshold' => $isV3 ? 'required|numeric|min:0|max:1' : 'nullable|numeric|min:0|max:1',
            'v2_site_key' => $needsV2 ? 'required|string' : 'nullable|string',
            'v2_secret_key' => $needsV2 ? 'required|string' : 'nullable|string',
            'v2_g_recaptcha_response' => ['nullable', 'string'],
            'v3_g_recaptcha_response' => ['nullable', 'string'],
        ];
    }

    #[Override]
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

    #[Override]
    public function messages()
    {
        return [
            'v3_site_key.required' => __('recaptcha::recaptcha.v3_site_key_required'),
            'v3_secret_key.required' => __('recaptcha::recaptcha.v3_secret_key_required'),
            'v2_site_key.required' => __('recaptcha::recaptcha.v2_site_key_required'),
            'v2_secret_key.required' => __('recaptcha::recaptcha.v2_secret_key_required'),
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $isV3 = $this->input('captcha_version') === 'v3_invisible';
            $needsV2 = in_array($this->input('captcha_version'), ['v2_checkbox', 'v2_invisible'], strict: true)
                || ($this->input('captcha_version') === 'v3_invisible' && $this->input('failover_action') === 'v2_checkbox');

            $verifier = resolve(RecaptchaVerifier::class);

            if ($isV3) {
                if (! $this->filled('v3_g_recaptcha_response')) {
                    $validator->errors()->add('v3_site_key', __('recaptcha::recaptcha.complete_recaptcha_v3'));
                } elseif ($this->filled('v3_secret_key')) {
                    $result = $verifier->verify(
                        $this->input('v3_g_recaptcha_response'),
                        $this->input('v3_secret_key'),
                        'v3',
                        $this->ip(),
                        $this->getHost(),
                        (float) $this->input('score_threshold', 0.5)
                    );

                    if ($result !== true) {
                        $validator->errors()->add('v3_secret_key', $result);
                    }
                }
            }

            if ($needsV2) {
                if (! $this->filled('v2_g_recaptcha_response')) {
                    $validator->errors()->add('v2_site_key', __('recaptcha::recaptcha.complete_recaptcha_v2'));
                } elseif ($this->filled('v2_secret_key')) {
                    $result = $verifier->verify(
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
            }
        });
    }
}
