<?php

use App\Plugins\Mailchimp\Http\Controllers\SettingsController;
use App\Plugins\Mailchimp\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// ── Mailchimp webhook (public — no auth) ──────────────────────────────────────
Route::get('mailchimp/webhook',  [WebhookController::class, 'verify']);
Route::post('mailchimp/webhook', [WebhookController::class, 'handle']);

// ── Newsletter widget subscribe (public — recaptcha protected) ────────────────
Route::post('mail-chimp/subscribe', [SettingsController::class, 'subscribeFromWidget'])
    ->middleware('recaptcha:mailChimp');

// ── Admin settings (auth + admin middleware inherited from web.php group) ──────
Route::middleware(['auth', 'admin'])->group(function () {

    // Settings page — replaces GET settings/mailchimp
    Route::get('settings/mailchimp',        [SettingsController::class, 'getSettings']);

    // Paginated list loading for infinite scroll
    Route::get('mailchimp/lists',           [SettingsController::class, 'getPaginatedLists']);

    // Save API key — replaces POST updateMailchimpDetails
    Route::post('updateMailchimpDetails',   [SettingsController::class, 'saveApiKey'])
        ->name('updateMailchimpDetails');

    // Save list + subscribe status — replaces PATCH mailchimp
    Route::patch('mailchimp',               [SettingsController::class, 'saveListSettings']);

    // Mapping page data
    Route::get('mailchimp/mapping-data',    [SettingsController::class, 'getMappingData']);

    // Sync from Mailchimp API
    Route::post('mailchimp/sync-fields',    [SettingsController::class, 'syncMergeFields']);
    Route::post('mailchimp/sync-groups',    [SettingsController::class, 'syncInterestGroups']);

    // Save mappings — replace existing PATCH routes
    Route::patch('mail-chimp/mapping',      [SettingsController::class, 'saveFieldMapping']);
    Route::patch('mailchimp-group/mapping', [SettingsController::class, 'saveGroupMapping']);
    Route::patch('mailchimp-ispaid/mapping',[SettingsController::class, 'saveIsPaidMapping']);

    // Status toggles
    Route::post('mailchimp-prod-status',    [SettingsController::class, 'updateProductStatus'])
        ->name('mailchimp-prod-status');
    Route::post('mailchimp-paid-status',    [SettingsController::class, 'updateIsPaidStatus'])
        ->name('mailchimp-paid-status');
});
