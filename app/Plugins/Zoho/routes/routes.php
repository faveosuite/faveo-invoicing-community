<?php

use App\Plugins\Zoho\Controllers\ZohoOAuthController;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\ZohoCampaignsController;
use App\Plugins\Zoho\Integrations\Crm\Controllers\ZohoCrmController;

Route::middleware(['auth', 'admin'])->prefix('zoho')->group(function (): void {
    // Oauth 2.0 connect
    Route::get('integrations', [ZohoOAuthController::class, 'getIntegrations']);
    Route::get('getKeys/{integrationId}', [ZohoOAuthController::class, 'getOauthClientKeys']);
    Route::post('saveKeys', [ZohoOAuthController::class, 'saveOauthClientKeys']);
    Route::patch('integrations/{id}/toggle', [ZohoOAuthController::class, 'toggleIntegration']);
    Route::post('integrations/{id}/disconnect', [ZohoOAuthController::class, 'disconnect']);
    Route::get('oauth/callback', [ZohoOAuthController::class, 'handleZohoCallback']);

    // Common Routes
    Route::get('options/{zohoFieldId}', [ZohoCrmController::class, 'getOptions']);
    Route::post('mapping/save', [ZohoCrmController::class, 'updateMapping']);

    // Campaigns Routes
    Route::prefix('campaigns')->group(function (): void {
        Route::get('{module}/mapping/data', [ZohoCampaignsController::class, 'getCampaignsMappedFields']);
        Route::get('contacts/fields', [ZohoCampaignsController::class, 'getCampaignsContactFields']);
        Route::get('sync', [ZohoCampaignsController::class, 'syncFields']);
        Route::get('lists', [ZohoCampaignsController::class, 'getLists']);
        Route::post('list', [ZohoCampaignsController::class, 'saveList']);
        Route::post('test', [ZohoCampaignsController::class, 'sendTest']);
    });

    // Crm Routes
    Route::prefix('crm')->group(function (): void {
        Route::get('{module}/mapping/data', [ZohoCrmController::class, 'getCrmMappedFields']);
        Route::get('contacts/fields', [ZohoCrmController::class, 'getCrmContactsFields']);
        Route::get('accounts/fields', [ZohoCrmController::class, 'getCrmAccountsFields']);
        Route::get('sync', [ZohoCrmController::class, 'syncFields']);
        Route::post('test', [ZohoCrmController::class, 'sendTest']);
    });
});
