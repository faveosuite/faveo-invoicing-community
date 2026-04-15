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
})->where('any', '.*')->middleware(['web', 'auth', 'admin']);

// ============================================================================
// ADMIN API ROUTES (Protected - Manager middleware required)
// These routes are called by the License Manager Vue UI
// ============================================================================

Route::prefix('api/admin')->middleware(['web', 'auth', 'admin'])->group(function () {

    // ========================================================================
    // DASHBOARD
    // ========================================================================
    Route::get('/dashboarddropdown', [\App\Modules\License\Controllers\Admin\DashboardController::class, 'dashboard']);

    // ========================================================================
    // PRODUCTS
    // ========================================================================
    Route::get('/viewproducts', [\App\Modules\License\Controllers\Admin\ProductsController::class, 'show']);
    Route::get('/product/{product_id}', [\App\Modules\License\Controllers\Admin\ProductsController::class, 'edit']);
    Route::get('/productView/{product_id}', [\App\Modules\License\Controllers\Admin\Views\ProductsViewController::class, 'getProductDetails']);
    Route::get('/productInstallations/{product_id}', [\App\Modules\License\Controllers\Admin\Views\ProductsViewController::class, 'getProductInstallations']);
    Route::get('/productLicenses/{product_id}', [\App\Modules\License\Controllers\Admin\Views\ProductsViewController::class, 'getProductLicenses']);
    Route::get('/productVersions/{product_id}', [\App\Modules\License\Controllers\Admin\Views\ProductsViewController::class, 'getProductVersions']);
    Route::post('/addProduct', [\App\Modules\License\Controllers\Admin\ProductsController::class, 'addAflAndAfuProduct']);
    Route::post('/updateProduct', [\App\Modules\License\Controllers\Admin\ProductsController::class, 'updateAflAndAfuProduct']);
    Route::delete('/allProductDelete', [\App\Modules\License\Controllers\Admin\ProductsController::class, 'deleteAflAndAfuProduct']);
    Route::post('/restoreProduct', [\App\Modules\License\Controllers\Admin\ProductsController::class, 'restoreSuspendedProduct']);
    Route::get('/afuProducts', [\App\Modules\License\Controllers\Update\AfuProductsController::class, 'getProducts']);

    // ========================================================================
    // VERSIONS
    // ========================================================================
    Route::get('/viewVersions', [\App\Modules\License\Controllers\Admin\VersionsController::class, 'show']);
    Route::get('/versionView/{version_id}', [\App\Modules\License\Controllers\Admin\Views\VersionsViewController::class, 'getVersionInfo']);
    Route::get('/versionCallbacks/{version_id}', [\App\Modules\License\Controllers\Admin\Views\VersionsViewController::class, 'getVersionCallbacks']);
    Route::post('/versions/add', [\App\Modules\License\Controllers\Update\AfuVersionsController::class, 'versionAdd']);
    Route::post('/versions/edit', [\App\Modules\License\Controllers\Update\AfuVersionsController::class, 'versionUpdate']);
    Route::delete('/versions/delete', [\App\Modules\License\Controllers\Update\AfuVersionsController::class, 'deleteVersion']);

    // ========================================================================
    // LICENSES
    // ========================================================================
    Route::get('/viewLicenses', [\App\Modules\License\Controllers\Admin\LicenseController::class, 'show']);
    Route::get('/licenseView/{license_id}', [\App\Modules\License\Controllers\Admin\Views\LicenseViewController::class, 'getLicenseDetails']);
    Route::get('/license/{license_id}', [\App\Modules\License\Controllers\Admin\LicenseController::class, 'edit']);
    Route::get('/licenseInstallation/{license_id}', [\App\Modules\License\Controllers\Admin\Views\LicenseViewController::class, 'getLicenseInstallations']);
    Route::get('/licenseCallbacks/{license_id}', [\App\Modules\License\Controllers\Admin\Views\LicenseViewController::class, 'getLicenseCallBacks']);
    Route::get('/installationLogs/{id}', [\App\Modules\License\Controllers\Admin\Views\LicenseViewController::class, 'getLicenseInstallationLogs']);
    Route::post('/license/add', [\App\Modules\License\Controllers\Admin\LicenseController::class, 'licenseAdd']);
    Route::post('/license/edit', [\App\Modules\License\Controllers\Admin\LicenseController::class, 'licenseUpdate']);
    Route::delete('/license/delete', [\App\Modules\License\Controllers\Admin\LicenseController::class, 'deleteLicense']);

    // ========================================================================
    // INSTALLATIONS
    // ========================================================================
    Route::get('/viewInstallations', [\App\Modules\License\Controllers\Admin\InstallationController::class, 'show']);
    Route::get('/installationView/{installation_id}', [\App\Modules\License\Controllers\Admin\Views\InstallationViewController::class, 'getInstallation']);
    Route::get('/installation/{installation_id}', [\App\Modules\License\Controllers\Admin\InstallationController::class, 'edit']);
    Route::get('/installationCallbacks/{installation_id}', [\App\Modules\License\Controllers\Admin\Views\InstallationViewController::class, 'getInstallationCallBacks']);
    Route::post('/installations/edit', [\App\Modules\License\Controllers\Admin\InstallationController::class, 'installationUpdate']);
    Route::delete('/installations/delete', [\App\Modules\License\Controllers\Admin\InstallationController::class, 'deleteInstallations']);

    // ========================================================================
    // CALLBACKS
    // ========================================================================
    Route::get('/showLicenseCallbacks', [\App\Modules\License\Controllers\Admin\CallBackController::class, 'licneseCallbacks']);
    Route::get('/showUpdateCallbacks', [\App\Modules\License\Controllers\Admin\CallBackController::class, 'updateCallbacks']);

    // ========================================================================
    // REPORTS
    // ========================================================================
    Route::get('/reportLicense', [\App\Modules\License\Controllers\Admin\ReportsController::class, 'reportArrayLicense']);
    Route::get('/reportCracking', [\App\Modules\License\Controllers\Admin\ReportsController::class, 'reportArrayCracking']);
    Route::get('/reportSystem', [\App\Modules\License\Controllers\Admin\ReportsController::class, 'reportArraySystem']);
    Route::get('/reportUpdate', [\App\Modules\License\Controllers\Admin\ReportsController::class, 'reportArrayUpdate']);

    // ========================================================================
    // BANNED HOSTS
    // ========================================================================
    Route::get('/viewBannedHost', [\App\Modules\License\Controllers\Admin\BannedHostController::class, 'show']);
    Route::get('/viewBannedHost/{banned_host_id}', [\App\Modules\License\Controllers\Admin\BannedHostController::class, 'view']);
    Route::post('/bannedHosts/add', [\App\Modules\License\Controllers\Admin\BannedHostController::class, 'bannedHostAdd']);
    Route::post('/bannedHosts/edit', [\App\Modules\License\Controllers\Admin\BannedHostController::class, 'bannedHostUpdate']);
    Route::delete('/bannedHosts/delete', [\App\Modules\License\Controllers\Admin\BannedHostController::class, 'deleteBannedHost']);

    // ========================================================================
    // WHITELIST IPS
    // ========================================================================
    Route::get('/view-Whitelist', [\App\Modules\License\Controllers\WhitelistIpsController::class, 'view']);
    Route::get('/whitelist-edit/{id}', [\App\Modules\License\Controllers\WhitelistIpsController::class, 'edit']);
    Route::post('/whitelist/updateOrCreate', [\App\Modules\License\Controllers\WhitelistIpsController::class, 'whitelistAdd']);
    Route::delete('/delete-whitelist-ip', [\App\Modules\License\Controllers\WhitelistIpsController::class, 'deleteWhitelistIp']);

    // ========================================================================
    // CLIENTS
    // ========================================================================
    Route::get('/viewClients/{client_id?}', [\App\Modules\License\Controllers\Admin\ClientsController::class, 'show']);
});
