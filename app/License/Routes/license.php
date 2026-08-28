<?php

declare(strict_types=1);

use App\License\Controllers\Admin\BannedHostController;
use App\License\Controllers\Admin\CallBackController;
use App\License\Controllers\Admin\ClientController;
use App\License\Controllers\Admin\InstallationController;
use App\License\Controllers\Admin\LanguageController;
use App\License\Controllers\Admin\LicenseController;
use App\License\Controllers\Admin\NotificationsController;
use App\License\Controllers\Admin\ReportsController;
use App\License\Controllers\Admin\VersionsController;
use App\License\Controllers\Admin\Views\InstallationViewController;
use App\License\Controllers\Admin\Views\LicenseViewController;
use App\License\Controllers\Admin\Views\VersionsViewController;
use App\License\Controllers\AflCallbacks\ConnectionTestController;
use App\License\Controllers\AflCallbacks\LicenseInstallController;
use App\License\Controllers\AflCallbacks\LicenseSchemeController;
use App\License\Controllers\AflCallbacks\LicenseVerifyController;
use App\License\Controllers\AfuCallbacks\DownloadFileController;
use App\License\Controllers\AfuCallbacks\GetAllVersionsController;
use App\License\Controllers\AfuCallbacks\GetVersionsController;
use App\License\Controllers\LicenseApiController;
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
// AFU Version Callbacks and API-style equivalents — throttled per IP
Route::middleware('throttle:60,1')->group(function (): void {
    Route::post('/apl_callbacks/connection_test.php', [ConnectionTestController::class, 'connection']);
    Route::post('/apl_callbacks/license_install.php', [LicenseInstallController::class, 'licenseInstall']);
    Route::post('/apl_callbacks/license_scheme.php', [LicenseSchemeController::class, 'licenseScheme']);
    Route::post('/apl_callbacks/license_verify.php', [LicenseVerifyController::class, 'licenseVerify']);
    Route::post('/api/ConnectionTest', [ConnectionTestController::class, 'connection']);
    Route::post('/api/licenseInstall', [LicenseInstallController::class, 'licenseInstall']);
    Route::post('/api/licenseScheme', [LicenseSchemeController::class, 'licenseScheme']);
    Route::post('/api/licenseVerify', [LicenseVerifyController::class, 'licenseVerify']);
    Route::post('/api/getVersions', [GetVersionsController::class, 'getVersions']);
    Route::post('/api/getAllVersions', [GetAllVersionsController::class, 'getAllVersions']);
});

// File download routes — tighter limit to prevent bandwidth abuse
Route::middleware('throttle:10,1')->group(function (): void {
    Route::post('/aus_callbacks/download_file.php', [DownloadFileController::class, 'downloadFile']);
    Route::post('/api/downloadFile', [DownloadFileController::class, 'downloadFile']);
    Route::post('/api/pdf', [DownloadFileController::class, 'downloadFile']);
});

// ============================================================================
// PUBLIC LICENSE INFO ROUTES
// Called by external Faveo instances for license/plugin info
// ============================================================================

Route::middleware('throttle:120,1')->group(function (): void {
    Route::get('/api/licenseInfo', [LicenseApiController::class, 'licenseInfo']);
    Route::get('/api/IndividuallicenseInfo', [LicenseApiController::class, 'individualLicenseInfo']);
    Route::get('/api/getOrder', [LicenseApiController::class, 'getOrder']);
    Route::get('/api/pluginLicense', [LicenseApiController::class, 'pluginLicense']);
    Route::post('/api/pluginLicense', [LicenseApiController::class, 'pluginLicense']);
    Route::post('/api/LicenseReissue', [LicenseApiController::class, 'reissueLicenseCloud']);
});

// Language translations for Vue
Route::get('/license-manager-lang', [LanguageController::class, 'getLanguageFile']);

// ============================================================================
// ADMIN API ROUTES (Protected - Manager middleware required)
// These routes are called by the License Manager Vue UI
// ============================================================================

Route::prefix('api/admin')->middleware(['web', 'auth', 'admin'])->group(function (): void {
    // ========================================================================
    // ========================================================================
    // CLIENTS
    // ========================================================================
    Route::get('/viewClients', [ClientController::class, 'viewClients']);
    Route::get('/viewClients/{id}', [ClientController::class, 'viewClients']);
    Route::get('/viewproducts', [ClientController::class, 'viewProducts']);

    // ========================================================================
    // VERSIONS
    // ========================================================================
    Route::get('/viewVersions', [VersionsController::class, 'show']);
    Route::get('/versionView/{version_id}', [VersionsViewController::class, 'getVersionInfo']);
    Route::get('/versionCallbacks/{version_id}', [VersionsViewController::class, 'getVersionCallbacks']);

    // ========================================================================
    // LICENSES
    // ========================================================================
    Route::get('/viewLicenses', [LicenseController::class, 'show']);
    Route::get('/licenseView/{license_id}', [LicenseViewController::class, 'getLicenseDetails']);
    Route::get('/license/{license_id}', [LicenseController::class, 'edit']);
    Route::get('/licenseInstallation/{license_id}', [LicenseViewController::class, 'getLicenseInstallations']);
    Route::get('/licenseCallbacks/{license_id}', [LicenseViewController::class, 'getLicenseCallBacks']);
    Route::get('/installationLogs/{id}', [LicenseViewController::class, 'getLicenseInstallationLogs']);
    Route::post('/license/add', [LicenseController::class, 'licenseAdd']);
    Route::post('/license/edit', [LicenseController::class, 'licenseUpdate']);
    Route::delete('/license/delete', [LicenseController::class, 'deleteLicense']);

    // ========================================================================
    // INSTALLATIONS
    // ========================================================================
    Route::get('/viewInstallations', [InstallationController::class, 'show']);
    Route::get('/installationView/{installation_id}', [InstallationViewController::class, 'getInstallation']);
    Route::get('/installation/{installation_id}', [InstallationController::class, 'edit']);
    Route::get('/installationCallbacks/{installation_id}', [InstallationViewController::class, 'getInstallationCallBacks']);
    Route::post('/installations/edit', [InstallationController::class, 'installationUpdate']);
    Route::delete('/installations/delete', [InstallationController::class, 'deleteInstallations']);

    // ========================================================================
    // CALLBACKS
    // ========================================================================
    Route::get('/showLicenseCallbacks', [CallBackController::class, 'licneseCallbacks']);
    Route::get('/showUpdateCallbacks', [CallBackController::class, 'updateCallbacks']);

    // ========================================================================
    // REPORTS
    // ========================================================================
    Route::get('/reportLicense', [ReportsController::class, 'reportArrayLicense']);
    Route::get('/reportCracking', [ReportsController::class, 'reportArrayCracking']);
    Route::get('/reportSystem', [ReportsController::class, 'reportArraySystem']);
    Route::get('/reportUpdate', [ReportsController::class, 'reportArrayUpdate']);

    // ========================================================================
    // BANNED HOSTS
    // ========================================================================
    Route::get('/viewBannedHost', [BannedHostController::class, 'show']);
    Route::get('/viewBannedHost/{banned_host_id}', [BannedHostController::class, 'view']);
    Route::post('/bannedHosts/add', [BannedHostController::class, 'bannedHostAdd']);
    Route::post('/bannedHosts/edit', [BannedHostController::class, 'bannedHostUpdate']);
    Route::delete('/bannedHosts/delete', [BannedHostController::class, 'deleteBannedHost']);
    Route::get('/bannedHosts/security-settings', [BannedHostController::class, 'getSecuritySettings']);
    Route::post('/bannedHosts/security-settings', [BannedHostController::class, 'updateSecuritySettings']);

    // ========================================================================
    // SERVER NOTIFICATIONS
    // ========================================================================
    Route::get('/viewNotifications', [NotificationsController::class, 'showLicenseNotifications']);
    Route::post('/notifications/{notification_id}', [NotificationsController::class, 'updateLicenseNotifications']);
    Route::get('/showUpdateNotifications', [NotificationsController::class, 'showUpdateNotifications']);
    Route::post('/updateNotifications/{notification_id}', [NotificationsController::class, 'updateUpdateNotifications']);
});
