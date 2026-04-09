<?php

use App\Modules\License\Controllers\AflCallbacks\ConnectionTestController;
use App\Modules\License\Controllers\AflCallbacks\LicenseInstallController;
use App\Modules\License\Controllers\AflCallbacks\LicenseSchemeController;
use App\Modules\License\Controllers\AflCallbacks\LicenseVerifyController;
use App\Modules\License\Controllers\AfuCallbacks\DownloadFileController;
use App\Modules\License\Controllers\AfuCallbacks\FetchQueryController;
use App\Modules\License\Controllers\AfuCallbacks\GetAllVersionsController;
use App\Modules\License\Controllers\AfuCallbacks\GetVersionsController;
use App\Modules\License\Controllers\LicenseApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| License Module Routes
|--------------------------------------------------------------------------
|
| These routes handle license verification, installation tracking,
| and version management for the merged license system.
|
| IMPORTANT: The callback routes MUST match the original license app URLs
| exactly, because deployed client software has these URLs hardcoded.
|
*/

// ============================================================================
// EXTERNAL CALLBACK ROUTES (Public - No auth required)
// Called by deployed client software (Faveo Helpdesk, etc.)
// ============================================================================

// APL License Callbacks — original URL format: /apl_callbacks/*.php
Route::post('/apl_callbacks/connection_test.php', [ConnectionTestController::class, 'connection']);
Route::post('/apl_callbacks/license_install.php', [LicenseInstallController::class, 'licenseInstall']);
Route::post('/apl_callbacks/license_scheme.php', [LicenseSchemeController::class, 'licenseScheme']);
Route::post('/apl_callbacks/license_verify.php', [LicenseVerifyController::class, 'licenseVerify']);

// AFU Version Callbacks — original URL format: /aus_callbacks/*.php
Route::post('/aus_callbacks/download_file.php', [DownloadFileController::class, 'downloadFile']);

// API-style callback routes (also used by some clients)
Route::post('/api/ConnectionTest', [ConnectionTestController::class, 'connection']);
Route::post('/api/licenseInstall', [LicenseInstallController::class, 'licenseInstall']);
Route::post('/api/licenseScheme', [LicenseSchemeController::class, 'licenseScheme']);
Route::post('/api/licenseVerify', [LicenseVerifyController::class, 'licenseVerify']);
Route::post('/api/getVersions', [GetVersionsController::class, 'getVersions']);
Route::post('/api/getAllVersions', [GetAllVersionsController::class, 'getAllVersions']);
Route::post('/api/fetchQuery', [FetchQueryController::class, 'fetchQuery']);
Route::post('/api/downloadFile', [DownloadFileController::class, 'downloadFile']);
Route::post('/api/pdf', [DownloadFileController::class, 'downloadFile']);

// ============================================================================
// PUBLIC LICENSE INFO ROUTES
// Called by external Faveo instances for license/plugin info
// ============================================================================

Route::get('/api/licenseInfo', [LicenseApiController::class, 'licenseInfo']);
Route::get('/api/IndividuallicenseInfo', [LicenseApiController::class, 'individualLicenseInfo']);
Route::get('/api/getOrder', [LicenseApiController::class, 'getOrder']);
Route::get('/api/pluginLicense', [LicenseApiController::class, 'pluginLicense']);
Route::post('/api/pluginLicense', [LicenseApiController::class, 'pluginLicense']);
Route::post('/api/LicenseReissue', [LicenseApiController::class, 'reissueLicenseCloud']);

// ============================================================================
// LICENSE MANAGER SPA ENTRY POINT
// Catch-all route to serve the Vue SPA for the license manager UI
// ============================================================================

Route::get('/license-manager/{any?}', function () {
    return view('license::welcome');
})->where('any', '.*')->middleware('web');
