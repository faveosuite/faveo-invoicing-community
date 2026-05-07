<?php

use App\License\Controllers\Admin\LanguageController;
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
Route::middleware('throttle:60,1')->group(function () {
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
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/aus_callbacks/download_file.php', [DownloadFileController::class, 'downloadFile']);
    Route::post('/api/downloadFile', [DownloadFileController::class, 'downloadFile']);
    Route::post('/api/pdf', [DownloadFileController::class, 'downloadFile']);
});

// ============================================================================
// PUBLIC LICENSE INFO ROUTES
// Called by external Faveo instances for license/plugin info
// ============================================================================

Route::middleware('throttle:120,1')->group(function () {
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

Route::prefix('api/admin')->middleware(['web', 'auth', 'admin'])->group(function () {
    // ========================================================================
    // DASHBOARD
    // ========================================================================
    Route::get('/dashboarddropdown', [\App\License\Controllers\Admin\DashboardController::class, 'dashboard']);

    // ========================================================================
    // CLIENTS
    // ========================================================================
    Route::get('/viewClients', [\App\License\Controllers\Admin\ClientController::class, 'viewClients']);
    Route::get('/viewClients/{id}', [\App\License\Controllers\Admin\ClientController::class, 'viewClients']);
    Route::get('/viewproducts', [\App\License\Controllers\Admin\ClientController::class, 'viewProducts']);

    // ========================================================================
    // VERSIONS
    // ========================================================================
    Route::get('/viewVersions', [\App\License\Controllers\Admin\VersionsController::class, 'show']);
    Route::get('/versionView/{version_id}', [\App\License\Controllers\Admin\Views\VersionsViewController::class, 'getVersionInfo']);
    Route::get('/versionCallbacks/{version_id}', [\App\License\Controllers\Admin\Views\VersionsViewController::class, 'getVersionCallbacks']);
    Route::post('/versions/add', [\App\License\Controllers\Update\AfuVersionsController::class, 'versionAdd']);
    Route::post('/versions/edit', [\App\License\Controllers\Update\AfuVersionsController::class, 'versionUpdate']);
    Route::post('/versions/delete', [\App\License\Controllers\Update\AfuVersionsController::class, 'deleteVersion']);

    // ========================================================================
    // LICENSES
    // ========================================================================
    Route::get('/viewLicenses', [\App\License\Controllers\Admin\LicenseController::class, 'show']);
    Route::get('/licenseView/{license_id}', [\App\License\Controllers\Admin\Views\LicenseViewController::class, 'getLicenseDetails']);
    Route::get('/license/{license_id}', [\App\License\Controllers\Admin\LicenseController::class, 'edit']);
    Route::get('/licenseInstallation/{license_id}', [\App\License\Controllers\Admin\Views\LicenseViewController::class, 'getLicenseInstallations']);
    Route::get('/licenseCallbacks/{license_id}', [\App\License\Controllers\Admin\Views\LicenseViewController::class, 'getLicenseCallBacks']);
    Route::get('/installationLogs/{id}', [\App\License\Controllers\Admin\Views\LicenseViewController::class, 'getLicenseInstallationLogs']);
    Route::post('/license/add', [\App\License\Controllers\Admin\LicenseController::class, 'licenseAdd']);
    Route::post('/license/edit', [\App\License\Controllers\Admin\LicenseController::class, 'licenseUpdate']);
    Route::post('/license/delete', [\App\License\Controllers\Admin\LicenseController::class, 'deleteLicense']);

    // ========================================================================
    // INSTALLATIONS
    // ========================================================================
    Route::get('/viewInstallations', [\App\License\Controllers\Admin\InstallationController::class, 'show']);
    Route::get('/installationView/{installation_id}', [\App\License\Controllers\Admin\Views\InstallationViewController::class, 'getInstallation']);
    Route::get('/installation/{installation_id}', [\App\License\Controllers\Admin\InstallationController::class, 'edit']);
    Route::get('/installationCallbacks/{installation_id}', [\App\License\Controllers\Admin\Views\InstallationViewController::class, 'getInstallationCallBacks']);
    Route::post('/installations/edit', [\App\License\Controllers\Admin\InstallationController::class, 'installationUpdate']);
    Route::post('/installations/delete', [\App\License\Controllers\Admin\InstallationController::class, 'deleteInstallations']);

    // ========================================================================
    // CALLBACKS
    // ========================================================================
    Route::get('/showLicenseCallbacks', [\App\License\Controllers\Admin\CallBackController::class, 'licneseCallbacks']);
    Route::get('/showUpdateCallbacks', [\App\License\Controllers\Admin\CallBackController::class, 'updateCallbacks']);

    // ========================================================================
    // REPORTS
    // ========================================================================
    Route::get('/reportLicense', [\App\License\Controllers\Admin\ReportsController::class, 'reportArrayLicense']);
    Route::get('/reportCracking', [\App\License\Controllers\Admin\ReportsController::class, 'reportArrayCracking']);
    Route::get('/reportSystem', [\App\License\Controllers\Admin\ReportsController::class, 'reportArraySystem']);
    Route::get('/reportUpdate', [\App\License\Controllers\Admin\ReportsController::class, 'reportArrayUpdate']);

    // ========================================================================
    // BANNED HOSTS
    // ========================================================================
    Route::get('/viewBannedHost', [\App\License\Controllers\Admin\BannedHostController::class, 'show']);
    Route::get('/viewBannedHost/{banned_host_id}', [\App\License\Controllers\Admin\BannedHostController::class, 'view']);
    Route::post('/bannedHosts/add', [\App\License\Controllers\Admin\BannedHostController::class, 'bannedHostAdd']);
    Route::post('/bannedHosts/edit', [\App\License\Controllers\Admin\BannedHostController::class, 'bannedHostUpdate']);
    Route::post('/bannedHosts/delete', [\App\License\Controllers\Admin\BannedHostController::class, 'deleteBannedHost']);

    // ========================================================================
    // WHITELIST IPS
    // ========================================================================
    Route::get('/view-Whitelist', [\App\License\Controllers\WhitelistIpsController::class, 'view']);
    Route::get('/whitelist-edit/{id}', [\App\License\Controllers\WhitelistIpsController::class, 'edit']);
    Route::post('/whitelist/updateOrCreate', [\App\License\Controllers\WhitelistIpsController::class, 'whitelistAdd']);
    Route::post('/delete-whitelist-ip', [\App\License\Controllers\WhitelistIpsController::class, 'deleteWhitelistIp']);

    // ========================================================================
    // SERVER NOTIFICATIONS
    // ========================================================================
    Route::get('/viewNotifications', [\App\License\Controllers\Admin\NotificationsController::class, 'showLicenseNotifications']);
    Route::post('/notifications/{notification_id}', [\App\License\Controllers\Admin\NotificationsController::class, 'updateLicenseNotifications']);
    Route::get('/showUpdateNotifications', [\App\License\Controllers\Admin\NotificationsController::class, 'showUpdateNotifications']);
    Route::post('/updateNotifications/{notification_id}', [\App\License\Controllers\Admin\NotificationsController::class, 'updateUpdateNotifications']);
});
