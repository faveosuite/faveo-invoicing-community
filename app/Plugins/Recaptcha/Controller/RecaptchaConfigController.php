<?php

namespace App\Plugins\Recaptcha\Controller;

use App\Http\Controllers\Controller;
use App\Plugins\Recaptcha\Model\RecaptchaSetting;

/**
 * Exposes the guest-safe reCAPTCHA configuration to the Vue SPA.
 *
 * Unlike RecaptchaSettingsController (admin-only, returns secret keys), this
 * endpoint is public and returns only what the browser widgets need to render.
 */
class RecaptchaConfigController extends Controller
{
    public function show()
    {
        return successResponse('', RecaptchaSetting::publicConfig());
    }
}
