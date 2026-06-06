<?php

namespace App\Plugins\Recaptcha\Controller;

use App\Http\Controllers\Controller;
use App\Model\Common\StatusSetting;
use App\Plugins\Recaptcha\Model\RecaptchaSetting;
use App\Plugins\Recaptcha\Requests\UpdateSettingsRequest;

class RecaptchaSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function getSettings()
    {
        $status = StatusSetting::first();
        $settings = RecaptchaSetting::firstOrCreate([]);

        return successResponse('', [
            'recaptcha_status' => (bool) optional($status)->recaptcha_status,
            'captcha_version' => $settings->captcha_version ?? 'v2_checkbox',
            'failover_action' => $settings->failover_action ?? 'none',
            'v3_site_key' => $settings->v3_site_key ?? '',
            'v3_secret_key' => $settings->v3_secret_key ?? '',
            'score_threshold' => $settings->score_threshold ?? 0.5,
            'v2_site_key' => $settings->v2_site_key ?? '',
            'v2_secret_key' => $settings->v2_secret_key ?? '',
            'theme' => $settings->theme ?? 'light',
            'size' => $settings->size ?? 'normal',
            'badge_position' => $settings->badge_position ?? 'bottomright',
        ]);
    }

    public function updateSettings(UpdateSettingsRequest $request)
    {
        $status = StatusSetting::findOrFail(1);
        $status->recaptcha_status = $request->boolean('recaptcha_status', true);
        $status->save();

        $settings = RecaptchaSetting::firstOrCreate([]);
        $settings->update($request->only([
            'v2_site_key', 'v2_secret_key', 'v3_site_key', 'v3_secret_key',
            'captcha_version', 'failover_action', 'score_threshold',
            'theme', 'size', 'badge_position',
        ]));

        return successResponse(__('message.recaptcha_settings_updated'));
    }
}
