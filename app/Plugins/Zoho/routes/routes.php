<?php


use App\Plugins\Zoho\Controllers\ZohoOAuthController;
use App\Plugins\Zoho\Integrations\Crm\Controllers\ZohoCrmController;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\ZohoCampaignsController;


Route::prefix('zoho')->group(function () {

    // Oauth 2.0 connect
    Route::get('connect', [ZohoOAuthController::class, 'connectPage']);
    Route::get('oauth/redirect', [ZohoOAuthController::class, 'getAuthorizationUrl']);
    Route::get('oauth/callback', [ZohoOAuthController::class, 'handleZohoCallback']);


    // Common Routes
    Route::get('options/{zohoFieldId}', [ZohoCrmController::class, 'getOptions']);
    Route::post('mapping/save', [ZohoCrmController::class, 'updateMapping']);

    // Campaigns Routes
    Route::prefix('campaigns')->group(function () {
        Route::get('{module}/mapping', function ($module) {
            $platform = 'campaigns';
            return view('zoho::mapping', compact('module', 'platform'));
        });
        Route::get('{module}/mapping/data', [ZohoCampaignsController::class, 'getCampaignsMappedFields']);
        Route::get('contacts/fields', [ZohoCampaignsController::class, 'getCampaignsContactFields']);
        Route::post('subscribe', [ZohoCampaignsController::class, 'subscribeCampaign']);
        Route::get('sync', [ZohoCampaignsController::class, 'syncFields']);
    });

    // Crm Routes
    Route::prefix('crm')->group(function () {
        Route::get('{module}/mapping', function ($module) {
            $platform = 'crm';
            return view('zoho::mapping', compact('module', 'platform'));
        });

        Route::get('{module}/mapping/data', [ZohoCrmController::class, 'getCrmMappedFields']);
        Route::get('contacts/fields', [ZohoCrmController::class, 'getCrmContactsFields']);
        Route::get('accounts/fields', [ZohoCrmController::class, 'getCrmAccountsFields']);
        Route::get('sync', [ZohoCrmController::class, 'syncFields']);
    });
});