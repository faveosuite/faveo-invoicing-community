<?php

use App\Http\Controllers\BillingInstaller\InstallerController;
use App\Http\Controllers\Common\SettingsController;
use Illuminate\Support\Facades\Route;
Route::middleware(['installer'])->group(function () {

Route::get('db-setup', [InstallerController::class, 'databasePage'])->name('db-setup');

Route::get('post-check',[InstallerController::class, 'database'])->name('database');

Route::get('get-start', [InstallerController::class, 'account'])->name('get-start');

Route::get('final', [InstallerController::class, 'finalize'])->name('final');

Route::post('posting', [InstallerController::class, 'configurationcheck'])->name('posting');

Route::post('config-check', [InstallerController::class, 'dbsetup'])->name('config-check');

Route::post('create/env', [InstallerController::class, 'createEnv'])->name('create.env');

Route::get('preinstall/check', [InstallerController::class, 'checkPreInstall'])->name('preinstall.check');

Route::get('migrate', [InstallerController::class, 'migrate'])->name('migrate');

Route::get('getTimeZone', [InstallerController::class, 'getTimeZoneDropDown']);

Route::post('accountcheck', [InstallerController::class, 'accountcheck'])->name('accountcheck');

Route::post('lang', [InstallerController::class, 'getLang'])->withoutMiddleware(['isInstalled']);

Route::get('language/settings', [InstallerController::class, 'languageList'])->withoutMiddleware(['isInstalled']);
Route::post('/update/language', [InstallerController::class, 'storeLanguage'])->withoutMiddleware(['isInstalled']);
Route::get('/current-language', [InstallerController::class, 'getCurrentLang'])->withoutMiddleware(['isInstalled']);

});
