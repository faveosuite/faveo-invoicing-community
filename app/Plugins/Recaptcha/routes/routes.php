<?php


use App\Plugins\Recaptcha\Controller\RecaptchaSettingsController;

Route::get('recaptcha-settings', [RecaptchaSettingsController::class, 'getSettings']);
Route::post('recaptcha-settings', [RecaptchaSettingsController::class, 'updateSettings']);
Route::get('recaptcha', [RecaptchaSettingsController::class, 'settings']);
Route::post('captcha/verify', [RecaptchaSettingsController::class, 'updateSettings']);