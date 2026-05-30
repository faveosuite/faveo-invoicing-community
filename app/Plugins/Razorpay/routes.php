<?php

// Razorpay admin settings only. The SPA invoice-payment flow is wired in
// routes/web.php (Front\PaymentController -> App\Services\Payment\InvoicePaymentService
// -> App\Plugins\Payment\Gateways\RazorpayGateway), with verification at
// POST /payment/{invoice} (RazorpayController@payment).
Route::get('get-razorpay-settings', 'App\Plugins\Razorpay\Controllers\SettingsController@getSettings');
Route::get('update-api-key/payment-gateway/razorpay', 'App\Plugins\Razorpay\Controllers\SettingsController@updateApiKey');
