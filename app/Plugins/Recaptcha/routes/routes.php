<?php

use App\Plugins\Recaptcha\Controller\RecaptchaConfigController;
use App\Plugins\Recaptcha\Controller\RecaptchaSettingsController;

// Guest-safe public config for the Vue reCAPTCHA layer. Wrapped in `web` so the
// session/locale middleware run (the v3 -> v2 fallback session lives in `web`).
Route::middleware('web')->get('recaptcha/config', [RecaptchaConfigController::class, 'show']);

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('recaptcha-settings', [RecaptchaSettingsController::class, 'getSettings']);
    Route::match(['post', 'patch'], 'recaptcha-settings', [RecaptchaSettingsController::class, 'updateSettings']);
});
