<?php

// Stripe admin settings only. The SPA invoice-payment flow is wired in
// routes/web.php (Front\PaymentController -> App\Services\Payment\InvoicePaymentService
// -> App\Plugins\Payment\Gateways\StripeGateway).
Route::get('get-stripe-settings', 'App\Plugins\Stripe\Controllers\SettingsController@getSettings');
Route::get('update-api-key/payment-gateway/stripe', 'App\Plugins\Stripe\Controllers\SettingsController@updateApiKey');
