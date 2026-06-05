<?php

namespace App\Plugins\Recaptcha\Model;

use Illuminate\Database\Eloquent\Model;

class RecaptchaSetting extends Model
{
    protected $fillable = [
        'v2_site_key',
        'v2_secret_key',
        'v3_site_key',
        'v3_secret_key',
        'captcha_version',
        'failover_action',
        'score_threshold',
        'theme',
        'size',
        'badge_position',
    ];

    public static function isCaptchaCanRun(): bool
    {
        $statusSetting = \App\Model\Common\StatusSetting::first();
        $recaptchaSetting = self::first();

        return auth()->guest()
            && ($statusSetting?->recaptcha_status ?? false)
            && (
                ! empty($recaptchaSetting?->v2_site_key) || ! empty($recaptchaSetting?->v3_site_key)
            );
    }

    /**
     * Guest-safe configuration consumed by the Vue reCAPTCHA layer.
     *
     * Never includes secret keys — token verification stays server-side in the
     * RecaptchaMiddleware.
     */
    public static function publicConfig(): array
    {
        $status = \App\Model\Common\StatusSetting::first();
        $settings = self::firstOrCreate([]);

        $statusEnabled = (bool) ($status?->recaptcha_status ?? false);
        $hasKey = ! empty($settings->v2_site_key) || ! empty($settings->v3_site_key);

        return [
            'enabled' => $statusEnabled && $hasKey,
            'version' => $settings->captcha_version ?? 'v3_invisible',
            'failover_action' => $settings->failover_action ?? 'none',
            'v2_site_key' => $settings->v2_site_key ?? '',
            'v3_site_key' => $settings->v3_site_key ?? '',
            'theme' => $settings->theme ?? 'light',
            'size' => $settings->size ?? 'normal',
            'badge_position' => $settings->badge_position ?? 'bottomright',
            'lang' => app()->getLocale() ?? 'en',
        ];
    }
}
