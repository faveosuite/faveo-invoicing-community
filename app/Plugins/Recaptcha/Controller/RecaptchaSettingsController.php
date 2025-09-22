<?php

namespace App\Plugins\Recaptcha\Controller;

use App\Http\Controllers\Controller;
use App\Plugins\Recaptcha\Model\RecaptchaSetting;
use App\Plugins\Recaptcha\Requests\UpdateSettingsRequest;

class RecaptchaSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function settings()
    {
        return view('recaptcha::setting');
    }

    public function getSettings()
    {
        $settings = RecaptchaSetting::firstOrCreate([]);

        return successResponse('', $settings);
    }

    /**
     * Update settings.
     */
    public function updateSettings(UpdateSettingsRequest $request)
    {
        // Save settings
        $settings = RecaptchaSetting::firstOrCreate([]);
        $settings->update($request->only([
            'v2_site_key', 'v2_secret_key', 'v3_site_key', 'v3_secret_key',
            'captcha_version', 'failover_action', 'score_threshold', 'language',
            'error_message', 'theme', 'size', 'badge_position',
        ]));

        return successResponse(__('recaptcha::recaptcha.captcha_settings_updated'));
    }
}
