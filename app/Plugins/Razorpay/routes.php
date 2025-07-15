<?php

//Event::listen(\App\Events\PaymentGateway::class, function ($event) {
//    $controller = new App\Plugins\Razorpay\Controllers\ProcessController();
//    echo $controller->PassToPayment($event->para);
//});//no
//Route::get('payment-gateway/razorpay', 'App\Plugins\Razorpay\Controllers\SettingsController@Settings');//no
//Route::patch('payment-gateway/stripe', 'App\Plugins\Stripe\Controllers\SettingsController@postSettings');//no
//Route::post('change-base-currency/payment-gateway/stripe', 'App\Plugins\Stripe\Controllers\SettingsController@changeBaseCurrency');//no
//Route::get('update-api-key/payment-gateway/razorpay', 'App\Plugins\Razorpay\Controllers\SettingsController@updateApiKey');//yes
