<?php

Event::listen(\App\Events\PaymentGateway::class, function ($event) {
    $controller = new App\Plugins\Payment_module\ProcessController();
    echo $controller->PassToPayment($event->para);
});

//stripe
//Route::get('payment-gateway/stripe', 'App\Plugins\Stripe\Controllers\SettingsController@Settings');
Route::get('payment-gateway/stripe','App\Plugins\Payment_module\Stripe\Controllers\SettingsController@Settings');
//Route::patch('payment-gateway/stripe', 'App\Plugins\Stripe\Controllers\SettingsController@postSettings');
//Route::post('change-base-currency/payment-gateway/stripe', 'App\Plugins\Stripe\Controllers\SettingsController@changeBaseCurrency');

//Route::get('update-api-key/payment-gateway/stripe', 'App\Plugins\Stripe\Controllers\SettingsController@updateApiKey');
Route::get('update-api-key/payment-gateway/stripe', 'App\Plugins\Payment_module\Stripe\Controllers\SettingsController@updateApiKey');

// Route::post('stripe', 'App\Plugins\Stripe\Controllers\SettingsController@stripePost')->name('stripe.post');
//Route::get('stripe', 'App\Plugins\Stripe\Controllers\ProcessController@payWithStripe')->name('stripform');

//Route::post('stripe', 'App\Plugins\Stripe\Controllers\SettingsController@postPaymentWithStripe')->name('paywithstripe');
Route::post('stripe', 'App\Plugins\Payment_module\Stripe\Controllers\OnetimeController@postPaymentWithStripe')->name('paywithstripe');
Route::get('confirm/auto-renewal','App\Plugins\Payment_module\Stripe\Controllers\RecurringController@confirmAutoRenewal');
//Route::post('final/stripe', 'App\Plugins\Stripe\Controllers\SettingsController@final')->name('final');

//Razorpay
Route::get('update-api-key/payment-gateway/razorpay', 'App\Plugins\Payment_module\Razorpay\Controllers\SettingsController@updateApiKey');
Route::get('confirm/payment', 'App\Plugins\Payment_module\Razorpay\Controllers\OnetimeController@afterPayment');
Route::post('payment/{invoice}', 'App\Plugins\Payment_module\Razorpay\Controllers\OnetimeController@payment')->name('payment');
Route::get('payment-gateway/razorpay', 'App\Plugins\Payment_module\Razorpay\Controllers\SettingsController@Settings');

