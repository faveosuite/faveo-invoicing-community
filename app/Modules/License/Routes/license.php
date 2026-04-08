<?php

use App\Modules\License\Controllers\LicenseAdminController;
use App\Modules\License\Controllers\LicenseCallbackController;
use App\Modules\License\Controllers\VersionCallbackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| License Module Routes
|--------------------------------------------------------------------------
|
| These routes handle license verification, installation tracking,
| version management, and admin operations for the merged license system.
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
Route::post('/apl_callbacks/connection_test.php', [LicenseCallbackController::class, 'connection']);
Route::post('/apl_callbacks/license_install.php', [LicenseCallbackController::class, 'licenseInstall']);
Route::post('/apl_callbacks/license_scheme.php', [LicenseCallbackController::class, 'licenseScheme']);
Route::post('/apl_callbacks/license_verify.php', [LicenseCallbackController::class, 'licenseVerify']);

// AFU Version Callbacks — original URL format: /aus_callbacks/*.php
Route::post('/aus_callbacks/download_file.php', [VersionCallbackController::class, 'downloadFile']);

// API-style callback routes (also used by some clients)
Route::post('/api/ConnectionTest', [LicenseCallbackController::class, 'connection']);
Route::post('/api/licenseInstall', [LicenseCallbackController::class, 'licenseInstall']);
Route::post('/api/licenseScheme', [LicenseCallbackController::class, 'licenseScheme']);
Route::post('/api/licenseVerify', [LicenseCallbackController::class, 'licenseVerify']);
Route::post('/api/getVersions', [VersionCallbackController::class, 'getVersions']);
Route::post('/api/getAllVersions', [VersionCallbackController::class, 'getAllVersions']);
Route::post('/api/fetchQuery', [VersionCallbackController::class, 'fetchQuery']);
Route::post('/api/downloadFile', [VersionCallbackController::class, 'downloadFile']);
Route::post('/api/pdf', [VersionCallbackController::class, 'downloadFile']);

// ============================================================================
// PUBLIC LICENSE INFO ROUTES (Some use whitelist middleware in original)
// ============================================================================

Route::get('/api/licenseInfo', [LicenseAdminController::class, 'licenseInfo']);
Route::get('/api/IndividuallicenseInfo', [LicenseAdminController::class, 'individualLicenseInfo']);
Route::get('/api/getOrder', [LicenseAdminController::class, 'getOrder']);
Route::get('/api/pluginLicense', [LicenseAdminController::class, 'pluginLicense']);
Route::post('/api/pluginLicense', [LicenseAdminController::class, 'pluginLicense']);
Route::post('/api/LicenseReissue', [LicenseAdminController::class, 'reissueLicenseCloud']);

// ============================================================================
// ADMIN LICENSE MANAGEMENT ROUTES (requires auth middleware)
// Mirrors: /api/admin/* endpoints from original license app
// ============================================================================
Route::prefix('api/admin')->middleware(['auth'])->group(function () {
    // License CRUD
    Route::post('license/add', [LicenseAdminController::class, 'create']);
    Route::post('license/edit', [LicenseAdminController::class, 'edit']);
    Route::post('license/deactivate', [LicenseAdminController::class, 'deactivate']);
    Route::post('license/reactivate', [LicenseAdminController::class, 'reactivate']);
    Route::post('license/updateLicenseCode', [LicenseAdminController::class, 'updateLicenseCode']);
    Route::post('license/syncAddonLicense', [LicenseAdminController::class, 'syncAddonLicense']);

    // Product operations
    Route::get('getProductIdbyKey', [LicenseAdminController::class, 'getProductIdByKey']);

    // Search
    Route::post('search', [LicenseAdminController::class, 'search']);

    // Installation management
    Route::post('getInstallationLogs', [LicenseAdminController::class, 'getInstallationLogs']);
    Route::post('updateInstallationLogs', [LicenseAdminController::class, 'updateInstallationLogs']);
    Route::post('installations/edit', [LicenseAdminController::class, 'updateInstallation']);
    Route::post('installation/reissue', [LicenseAdminController::class, 'reissueLicenseCloud']);
    Route::post('addInstallation', [LicenseAdminController::class, 'addInstallation']);

    // License info
    Route::get('license/{licenseCode}', [LicenseAdminController::class, 'getByCode']);
    Route::get('license/{licenseCode}/installations', [LicenseAdminController::class, 'getInstallations']);
});
