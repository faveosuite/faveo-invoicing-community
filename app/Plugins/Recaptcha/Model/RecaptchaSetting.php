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
            && ! empty($recaptchaSetting?->v2_site_key)
            && ! empty($recaptchaSetting?->v3_site_key);
    }
}
