<?php

Event::listen(\App\Events\PaymentGateway::class, function ($event) {
    $controller = new App\Plugins\Stripe\Controllers\ProcessController();
    echo $controller->PassToPayment($event->para);
});
Route::get('payment-gateway/stripe', 'App\Plugins\Stripe\Controllers\SettingsController@Settings');
Route::patch('payment-gateway/stripe', 'App\Plugins\Stripe\Controllers\SettingsController@postSettings');
Route::post('change-base-currency/payment-gateway/stripe', 'App\Plugins\Stripe\Controllers\SettingsController@changeBaseCurrency');
Route::get('update-api-key/payment-gateway/stripe', 'App\Plugins\Stripe\Controllers\SettingsController@updateApiKey');
// Route::post('stripe', 'App\Plugins\Stripe\Controllers\SettingsController@stripePost')->name('stripe.post');
Route::get('stripe', 'App\Plugins\Stripe\Controllers\ProcessController@payWithStripe')->name('stripform');
Route::post('stripe', 'App\Plugins\Stripe\Controllers\SettingsController@postPaymentWithStripe')->name('paywithstripe');
Route::post('final/stripe', 'App\Plugins\Stripe\Controllers\SettingsController@final')->name('final');
