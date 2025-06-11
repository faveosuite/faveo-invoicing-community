<?php

Event::listen(\App\Events\PaymentGateway::class, function ($event) {
    $controller = new App\Plugins\Razorpay\Controllers\ProcessController();
    echo $controller->PassToPayment($event->para);
});
Route::get('payment-gateway/razorpay', 'App\Plugins\Razorpay\Controllers\SettingsController@Settings');
Route::patch('payment-gateway/stripe', 'App\Plugins\Stripe\Controllers\SettingsController@postSettings');
Route::post('change-base-currency/payment-gateway/stripe', 'App\Plugins\Stripe\Controllers\SettingsController@changeBaseCurrency');
Route::get('update-api-key/payment-gateway/razorpay', 'App\Plugins\Razorpay\Controllers\SettingsController@updateApiKey');
