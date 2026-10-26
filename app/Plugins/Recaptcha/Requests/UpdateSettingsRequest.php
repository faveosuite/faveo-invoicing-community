<?php

namespace App\Plugins\Recaptcha\Requests;

use App\Plugins\Recaptcha\Services\RecaptchaVerifier;
use App\Traits\RequestJsonValidation;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class UpdateSettingsRequest extends FormRequest
{
    use RequestJsonValidation;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        $isV3 = $this->usesV3();
        $needsV2 = $this->needsV2();

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

    public function withValidator(mixed $validator): void
    {
        $validator->after(function ($validator): void {
            $verifier = resolve(RecaptchaVerifier::class);

            if ($this->usesV3()) {
                $this->checkKeys($validator, $verifier, 'v3');
            }

            if ($this->needsV2()) {
                $this->checkKeys($validator, $verifier, 'v2');
            }
        });
    }

    private function usesV3(): bool
    {
        return $this->input('captcha_version') === 'v3_invisible';
    }

    /** v2 keys are in use: a v2 version, or v3 falling back to the v2 checkbox. */
    private function needsV2(): bool
    {
        return in_array($this->input('captcha_version'), ['v2_checkbox', 'v2_invisible'], strict: true)
            || ($this->usesV3() && $this->input('failover_action') === 'v2_checkbox');
    }

    /**
     * The admin must have solved this version's challenge, and (when a secret is given)
     * Google must accept it with that secret.
     */
    private function checkKeys(mixed $validator, RecaptchaVerifier $verifier, string $type): void
    {
        if (! $this->filled($type.'_g_recaptcha_response')) {
            $validator->errors()->add($type.'_site_key', __('recaptcha::recaptcha.complete_recaptcha_'.$type));

            return;
        }

        if (! $this->filled($type.'_secret_key')) {
            return;
        }

        $result = $verifier->verify(
            $this->input($type.'_g_recaptcha_response'),
            $this->input($type.'_secret_key'),
            $type,
            (string) $this->ip(),
            $this->getHost(),
        );

        if ($result !== true) {
            $validator->errors()->add($type.'_secret_key', $result);
        }
    }
}
