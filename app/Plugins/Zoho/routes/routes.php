<?php

use App\Model\Order\InvoiceItem;
use App\Model\Product\Product;
use App\Plugins\Zoho\Controllers\ZohoController;
use App\Plugins\Zoho\Controllers\ZohoOAuthController;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\ZohoCampaignsController;
use App\Plugins\Zoho\Integrations\Crm\Controllers\ZohoCrmController;
use App\Plugins\Zoho\Models\ZohoIntegration;

Route::prefix('zoho')->group(function () {
    Route::get('demo', function () {
        $freeProducts = Product::whereIn(
            'id',
            InvoiceItem::where('subtotal', 0)->distinct()->pluck('product_id')
        )->get();

        $paidProducts = Product::whereIn(
            'id',
            InvoiceItem::where('subtotal', '!=', 0)->distinct()->pluck('product_id')
        )->get();

        return view('zoho::demo', compact('freeProducts', 'paidProducts'));
    });

    Route::post('testEvent', [ZohoController::class, 'testEvent']);

    // Oauth 2.0 connect
    Route::get('connect', [ZohoOAuthController::class, 'connectPage']);
    Route::get('getKeys/{integrationId}', [ZohoOAuthController::class, 'getOauthClientKeys']);
    Route::post('saveKeys', [ZohoOAuthController::class, 'saveOauthClientKeys']);
    Route::get('oauth/callback', [ZohoOAuthController::class, 'handleZohoCallback']);

    // Common Routes
    Route::get('options/{zohoFieldId}', [ZohoCrmController::class, 'getOptions']);
    Route::post('mapping/save', [ZohoCrmController::class, 'updateMapping']);

    // Campaigns Routes
    Route::prefix('campaigns')->group(function () {
        Route::get('{module}/mapping', function ($module) {
            $integration = ZohoIntegration::where('platform', 'campaigns')->first();

            return view('zoho::mapping', compact('module', 'integration'));
        });
        Route::get('{module}/mapping/data', [ZohoCampaignsController::class, 'getCampaignsMappedFields']);
        Route::get('contacts/fields', [ZohoCampaignsController::class, 'getCampaignsContactFields']);
        Route::post('subscribe', [ZohoCampaignsController::class, 'subscribeCampaign']);
        Route::get('sync', [ZohoCampaignsController::class, 'syncFields']);
    });

    // Crm Routes
    Route::prefix('crm')->group(function () {
        Route::get('{module}/mapping', function ($module) {
            $integration = ZohoIntegration::where('platform', 'crm')->first();

            return view('zoho::mapping', compact('module', 'integration'));
        });

        Route::get('{module}/mapping/data', [ZohoCrmController::class, 'getCrmMappedFields']);
        Route::get('contacts/fields', [ZohoCrmController::class, 'getCrmContactsFields']);
        Route::get('accounts/fields', [ZohoCrmController::class, 'getCrmAccountsFields']);
        Route::get('sync', [ZohoCrmController::class, 'syncFields']);
    });
});
