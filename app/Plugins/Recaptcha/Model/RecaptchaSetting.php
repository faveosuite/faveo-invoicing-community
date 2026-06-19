<?php

namespace App\Plugins\Recaptcha\Model;

use App\Model\Common\StatusSetting;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $v2_site_key
 * @property string|null $v2_secret_key
 * @property string|null $v3_site_key
 * @property string|null $v3_secret_key
 * @property string $captcha_version
 * @property string $failover_action
 * @property numeric $score_threshold
 * @property string $theme
 * @property string $size
 * @property string $badge_position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecaptchaSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecaptchaSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecaptchaSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecaptchaSetting whereBadgePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecaptchaSetting whereCaptchaVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecaptchaSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecaptchaSetting whereFailoverAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecaptchaSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecaptchaSetting whereScoreThreshold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecaptchaSetting whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecaptchaSetting whereTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecaptchaSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecaptchaSetting whereV2SecretKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecaptchaSetting whereV2SiteKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecaptchaSetting whereV3SecretKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecaptchaSetting whereV3SiteKey($value)
 * @mixin \Eloquent
 */
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
        $statusSetting = StatusSetting::first();
        $recaptchaSetting = self::first();

        return auth()->guest()
            && ($statusSetting->recaptcha_status ?? false)
            && (
                ! empty($recaptchaSetting->v2_site_key) || ! empty($recaptchaSetting->v3_site_key)
            );
    }

    /**
     * Guest-safe configuration consumed by the Vue reCAPTCHA layer.
     *
     * Never includes secret keys — token verification stays server-side in the
     * RecaptchaMiddleware.
     * @return array<mixed>
     */
    public static function publicConfig(): array
    {
        $status = StatusSetting::first();
        $settings = self::firstOrCreate([]);

        $statusEnabled = (bool) ($status->recaptcha_status ?? false);
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
            'lang' => app()->getLocale() ?? 'en', // @phpstan-ignore nullCoalesce.expr
        ];
    }
}
