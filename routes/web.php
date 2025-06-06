<?php

use App\Http\Controllers\Api;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Common;
use App\Http\Controllers\Common\FileManagerController;
use App\Http\Controllers\Common\MSG91Controller;
use App\Http\Controllers\Common\PipedriveController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FreeTrailController;
use App\Http\Controllers\Front;
use App\Http\Controllers\Github;
use App\Http\Controllers\Google2FAController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Jobs;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\License;
use App\Http\Controllers\License\LocalizedLicenseController;
use App\Http\Controllers\Order;
//use App\Http\Controllers\PhoneVerificationController;
use App\Http\Controllers\Payment;
use App\Http\Controllers\Product;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\SocialLoginsController;
use App\Http\Controllers\Tenancy;
use App\Http\Controllers\ThirdPartyAppController;
use App\Http\Controllers\User;
use App\Http\Controllers\WelcomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::post('create/tenant/purchase', [Tenancy\CloudExtraActivities::class, 'storeTenantTillPurchase']);

// VisitStats::routes();
Route::get('refresh-csrf', function () {
    return response()->json([
        'token' => csrf_token(), ],
        200);
});
// social logins routes
Route::post('otp2/send', [Auth\AuthController::class, 'otp']);
Route::get('social-logins', [SocialLoginsController::class, 'view'])->middleware('auth');
Route::get('edit/SocialLogins/{id}', [SocialLoginsController::class, 'edit'])->middleware('auth');
Route::post('update-social-login', [SocialLoginsController::class, 'update'])->name('update-social-login');
//Route::post('verifying/phone', [PhoneVerificationController::class, 'create']);
Route::post('store-basic-details', [Auth\LoginController::class, 'storeBasicDetailsss'])->name('store-basic-details');

// !social logins rotes end

Route::middleware('installAgora')->group(function () {
    Route::post('store_toggle_state', [Common\TemplateController::class, 'toggle'])->withoutMiddleware(['auth', 'admin']);
    Route::get('pricing', [Front\CartController::class, 'cart'])->name('pricing');
    Route::get('group/{templateid}/{groupid}/', [Front\PageController::class, 'pageTemplates']);
    Route::post('cart/remove', [Front\CartController::class, 'cartRemove']);
    Route::post('update-agent-qty', [Front\CartController::class, 'updateAgentQty']);
    Route::post('update-qty', [Front\CartController::class, 'updateProductQty']);
    Route::post('reduce-product-qty', [Front\CartController::class, 'reduceProductQty']);
    Route::post('reduce-agent-qty', [Front\CartController::class, 'reduceAgentQty']);
    Route::post('cart/clear', [Front\CartController::class, 'clearCart']);
    Route::get('show/cart', [Front\CartController::class, 'showCart']);

    Route::get('checkout', [Front\CheckoutController::class, 'checkoutForm']);
    Route::match(['post', 'patch'], 'checkout-and-pay', [Front\CheckoutController::class, 'postCheckout']);
    Route::get('checkout-and-pay', function () {
        return redirect('show/cart');
    });

    Route::post('pricing/update', [Front\CartController::class, 'addCouponUpdate']);
    Route::post('mail-chimp/subcribe', [Common\MailChimpController::class, 'addSubscriberByClientPanel']);
    Route::get('mailchimp', [Common\MailChimpController::class, 'mailChimpSettings'])->middleware('admin');
    Route::patch('mailchimp', [Common\MailChimpController::class, 'postMailChimpSettings']);
    Route::get('mail-chimp/mapping', [Common\MailChimpController::class, 'mapField'])->middleware('admin');
    Route::patch('mail-chimp/mapping', [Common\MailChimpController::class, 'postMapField']);
    Route::patch('mailchimp-ispaid/mapping', [Common\MailChimpController::class, 'postIsPaidMapField']);
    Route::patch('mailchimp-group/mapping', [Common\MailChimpController::class, 'postGroupMapField']);
    Route::get('get-group-field/{value}', [Common\MailChimpController::class, 'addInterestFieldsToAgora']);
    Route::get('contact-us', [Front\PageController::class, 'contactUs']);
    Route::post('contact-us', [Front\PageController::class, 'postContactUs']);
    Route::post('remove-coupon', [Front\CartController::class, 'removeCoupon']);

    Route::post('demo-request', [Front\PageController::class, 'postDemoReq'])->withoutMiddleware(['auth']);
    Route::get('confirm/payment', [RazorpayController::class, 'afterPayment']);
    Route::post('stripeUpdatePayment/confirm', [Front\ClientController::class, 'stripeUpdatePayment']);

    /*
     * Front Client Pages
     */
    Route::get('client-dashboard', [Front\ClientController::class, 'index']);

    Route::post('first-login', [FreeTrailController::class, 'firstLoginAttempt']);

    Route::get('my-invoices', [Front\ClientController::class, 'invoices'])->name('my-invoices');

    Route::get('get-my-invoices', [Front\ClientController::class, 'getInvoices'])->name('get-my-invoices');
    Route::get('get-my-invoices/{orderid}/{userid}/{admin?}', [Front\ClientController::class, 'getInvoicesByOrderId']);

    Route::get('get-my-payment/{orderid}/{userid}', [Front\ClientController::class, 'getPaymentByOrderId'])->name('get-my-payment');

    Route::get('get-my-payment-client/{orderid}/{userid}', [Front\ClientController::class, 'getPaymentByOrderIdClient'])->name('get-my-payment-client');
    // Route::get('autoPayment-client/{orderid}', [Front\ClientController::class, 'getAutoPaymentStatus']);
    Route::post('strRenewal-enable', [Front\ClientController::class, 'enableAutorenewalStatus']);
    Route::post('renewal-disable', [Front\ClientController::class, 'disableAutorenewalStatus']);
    Route::post('rzpRenewal-disable/{orderid}', [Front\ClientController::class, 'enableRzpStatus']);

    Route::get('my-orders', [Front\ClientController::class, 'orders']);
    Route::get('get-my-orders', [Front\ClientController::class, 'getOrders'])->name('get-my-orders');
    Route::get('my-subscriptions', [Front\ClientController::class, 'subscriptions']);
    Route::get('get-my-subscriptions', [Front\ClientController::class, 'getSubscriptions']);
    Route::get('my-invoice/{id}', [Front\ClientController::class, 'getInvoice']);
    Route::get('my-order/{id}', [Front\ClientController::class, 'getOrder']);
    Route::get('uploadFile', [License\LocalizedLicenseController::class, 'storeFile']);
    Route::get('my-profile', [Front\ClientController::class, 'profile']);
    Route::patch('my-profile', [Front\ClientController::class, 'postProfile']);
    Route::patch('my-password', [Front\ClientController::class, 'postPassword']);
    Route::get('paynow/{id}', [Front\CheckoutController::class, 'payNow']);

    Route::delete('invoices/delete/{id}', [Front\ClientController::class, 'invoiceDelete']);

    Route::get('get-versions/{productid}/{clientid}/{invoiceid}/', [Front\ClientController::class, 'getVersionList'])->name('get-versions');
    Route::get('get-github-versions/{productid}/{clientid}/{invoiceid}/', [Front\ClientController::class, 'getGithubVersionList'])->name('get-github-versions');

    // Post Route For Make Razorpay Payment Request
    Route::post('payment/{invoice}', [RazorpayController::class, 'payment'])->name('payment');

    Route::get('downloadLicenseFile', [License\LocalizedLicenseController::class, 'downloadFile'])->name('event.rsvp')->middleware('signed');
    Route::get('downloadPrivate/{orderNo}', [License\LocalizedLicenseController::class, 'downloadPrivate']);
    Route::get('LocalizedLicense/downloadLicense/{fileName}', [License\LocalizedLicenseController::class, 'downloadFileAdmin']);
    Route::get('request', [License\LocalizedLicenseController::class, 'tempOrderLink']);
    Route::get('LocalizedLicense/downloadPrivateKey/{fileName}', [License\LocalizedLicenseController::class, 'downloadPrivateKeyAdmin']);
    /*
     * 2FA Routes
     */

    Route::post('/2fa/enable', [Google2FAController::class, 'enableTwoFactor']);
    Route::post('2fa/disable/{userId?}', [Google2FAController::class, 'disableTwoFactor']);
    Route::get('/2fa/validate', [Google2FAController::class, 'getValidateToken']);
    Route::get('verify-2fa', [Google2FAController::class, 'verify2fa']);
    Route::get('2fa/loginValidate', [Google2FAController::class, 'postLoginValidateToken'])->name('2fa/loginValidate');
    Route::post('2fa/setupValidate', [Google2FAController::class, 'postSetupValidateToken']);
    Route::get('verify-password', [Google2FAController::class, 'verifyPassword']);
    Route::post('2fa-recovery-code', [Google2FAController::class, 'generateRecoveryCode']);
    Route::get('get-recovery-code', [Google2FAController::class, 'getRecoveryCode']);
    Route::get('recovery-code', [Google2FAController::class, 'showRecoveryCode']);
    Route::post('verify-recovery-code', [Google2FAController::class, 'verifyRecoveryCode'])->name('verify-recovery-code');
    /*
     * Social Media
     */

    Route::resource('social-media', Common\SocialMediaController::class);
    Route::get('get-social-media', [Common\SocialMediaController::class, 'getSocials'])->name('get-social-media');
    Route::delete('social-delete', [Common\SocialMediaController::class, 'destroy'])->name('social-delete');

    Route::auth();
    Route::post('auth/register', [Auth\RegisterController::class, 'postRegister'])->name('auth/register');
    Route::get('auth/logout', [Auth\LoginController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index']);

    Route::get('/auth/redirect/{provider}', [Auth\LoginController::class, 'redirectToGithub']);
    Route::get('/auth/callback/{provider}', [Auth\LoginController::class, 'handler']);

    Route::get('activate/{token}', [Auth\AuthController::class, 'activate']);

    /*
     * Client
     */

    //contact-option
    Route::get('contact-option', [Common\SettingsController::class, 'contactOption'])->name('contact-option');

    /*
     * Profile Process
     */
    Route::get('profile', [User\ProfileController::class, 'profile']);
    Route::patch('profile', [User\ProfileController::class, 'updateProfile']);
    Route::patch('password', [User\ProfileController::class, 'updatePassword']);

    /*
     * Settings
     */

    Route::post('changeLogo', [Common\SettingsController::class, 'delete']);

    Route::get('settings', [Common\SettingsController::class, 'settings']);
    Route::get('/datatable/data', [Common\SettingsController::class, 'getDataTableData'])->name('datatable.data');
    Route::post('licensekeys', [Common\SettingsController::class, 'licensekeys']);
    Route::post('googleCaptcha', [Common\SettingsController::class, 'googleCaptcha']);
    Route::post('mobileVerification', [Common\SettingsController::class, 'mobileVerification']);
    Route::post('termsUrl', [Common\SettingsController::class, 'termsUrl']);
    Route::post('zohokeys', [Common\SettingsController::class, 'zohokeys']);
    Route::post('pipedrivekeys', [Common\SettingsController::class, 'pipedrivekeys']);
    Route::post('twitterkeys', [Common\SettingsController::class, 'twitterkeys']);
    Route::post('githubkeys', [Common\SettingsController::class, 'githubkeys']);
    Route::post('mailchimpkeys', [Common\SettingsController::class, 'mailchimpKeys']);

    Route::get('settings/system', [Common\SettingsController::class, 'settingsSystem']);
    Route::patch('settings/system', [Common\SettingsController::class, 'postSettingsSystem']);
    Route::get('settings/email', [Common\EmailSettingsController::class, 'settingsEmail'])->middleware('auth');
    Route::patch('settings/email', [Common\EmailSettingsController::class, 'postSettingsEmail']);
    Route::get('settings/template', [Common\SettingsController::class, 'settingsTemplate']);
    Route::patch('settings/template', [Common\SettingsController::class, 'postSettingsTemplate']);
    Route::patch('settings/error', [Common\SettingsController::class, 'postSettingsError']);
    Route::get('settings/activitylog', [Common\SettingsController::class, 'settingsActivity']);
    Route::get('settings/maillog', [Common\SettingsController::class, 'settingsMail']);
    Route::get('get-activity', [Common\SettingsController::class, 'getActivity'])->name('get-activity');
    Route::get('get-email', [Common\SettingsController::class, 'getMails'])->name('get-email');
    Route::delete('activity-delete', [Common\SettingsController::class, 'destroy'])->name('activity-delete');
    Route::delete('email-delete', [Common\SettingsController::class, 'destroyEmail'])->name('email-delete');
    Route::post('licenseDetails', [Common\BaseSettingsController::class, 'licenseDetails'])->name('licenseDetails');
    Route::post('licenseStatus', [Common\BaseSettingsController::class, 'licenseStatus'])->name('licenseStatus');
    Route::post('updateDetails', [Common\BaseSettingsController::class, 'updateDetails'])->name('updateDetails');
    Route::post('captchaDetails', [Common\BaseSettingsController::class, 'captchaDetails'])->name('captchaDetails');
    Route::post('updatemobileDetails', [Common\BaseSettingsController::class, 'updateMobileDetails'])->name('updatemobileDetails');
    Route::post('updateemailDetails', [Common\BaseSettingsController::class, 'updateEmailDetails'])->name('updateemailDetails');
    Route::post('updatetwitterDetails', [Common\BaseSettingsController::class, 'updateTwitterDetails'])->name('updatetwitterDetails');
    Route::post('updateMailchimpDetails', [Common\BaseSettingsController::class, 'updateMailchimpDetails'])->name('updateMailchimpDetails');
    Route::post('updateTermsDetails', [Common\BaseSettingsController::class, 'updateTermsDetails'])->name('updateTermsDetails');
    Route::post('updatezohoDetails', [Common\BaseSettingsController::class, 'updateZohoDetails'])->name('updatezohoDetails');
    Route::post('updatepipedriveDetails', [Common\BaseSettingsController::class, 'updatepipedriveDetails'])->name('updatepipedriveDetails');
    Route::post('mailchimp-prod-status', [Common\BaseSettingsController::class, 'updateMailchimpProductStatus'])->name('mailchimp-prod-status');
    Route::post('mailchimp-paid-status', [Common\BaseSettingsController::class, 'updateMailchimpIsPaidStatus'])->name('mailchimp-paid-status');
    Route::post('updatedomainCheckDetails', [Common\BaseSettingsController::class, 'updatedomainCheckDetails'])->name('updatedomainCheckDetails');
    Route::get('system-managers', [Common\SystemManagerController::class, 'getSystemManagers'])->name('system-managers');
    Route::get('search-admins', [Common\SystemManagerController::class, 'searchAdmin'])->name('search-admins');
    Route::post('replace-acc-manager', [Common\SystemManagerController::class, 'replaceAccountManager'])->name('replace-acc-manager');
    Route::post('replace-sales-manager', [Common\SystemManagerController::class, 'replaceSalesManager'])->name('replace-sales-manager');
    Route::get('debugg', [Common\SettingsController::class, 'debugSettings']);
    Route::post('save/debugg', [Common\SettingsController::class, 'postdebugSettings']);
    Route::post('v3captchaDetails', [Common\BaseSettingsController::class, 'v3captchaDetails'])->name('v3captchaDetails');
    Route::get('demo/page', [Front\PageController::class, 'VewDemoPage']);
    Route::post('save/demo', [Front\PageController::class, 'saveDemoPage']);
    Route::get('settings/paymentlog', [Common\SettingsController::class, 'settingsPayment']);
    Route::get('get-paymentlog', [Common\SettingsController::class, 'getPaymentlog'])->name('get-paymentlog');
    Route::delete('paymentlog-delete', [Common\SettingsController::class, 'destroyPayment'])->name('paymentlog-delete');

    /*
     * Client
     */

    Route::resource('clients', User\ClientController::class);
    Route::get('deleted-users', [User\SoftDeleteController::class, 'index']);
    Route::get('soft-delete', [User\SoftDeleteController::class, 'softDeletedUsers'])->name('soft-delete');
    Route::get('clients/{id}/restore', [User\SoftDeleteController::class, 'restoreUser']);
    Route::delete('permanent-delete-client', [User\SoftDeleteController::class, 'permanentDeleteUser']);
    Route::get('getClientDetail/{id}', [User\ClientController::class, 'getClientDetail']);
    Route::get('getPaymentDetail/{id}', [User\ClientController::class, 'getPaymentDetail']);
    Route::get('getOrderDetail/{id}', [User\ClientController::class, 'getOrderDetail']);
    Route::get('get-clients', [User\ClientController::class, 'getClients'])->name('get-clients');
    Route::delete('clients-delete', [User\ClientController::class, 'destroy']);
    Route::get('get-users', [User\ClientController::class, 'getUsers']);
    Route::get('search-email', [User\ClientController::class, 'search'])->name('search-email');

    Route::resource('products', Product\ProductController::class);
    Route::get('get-products', [Product\ProductController::class, 'getProducts'])->name('get-products');
    // Route::get('get-products', [Product\ProductController::class, 'GetProducts']);
    Route::delete('products-delete', [Product\ProductController::class, 'destroy'])->name('products-delete');
    Route::delete('uploads-delete', [Product\ProductController::class, 'fileDestroy'])->name('uploads-delete');

    Route::post('get-price', [Product\ProductController::class, 'getPrice']);
    Route::get('get-subscription/{id}', [Product\ProductController::class, 'getSubscriptionCheck']);
    Route::get('edit-upload/{id}', [Product\ProductController::class, 'editProductUpload']);
    Route::get('get-upload/{id}', [Product\ProductController::class, 'getUpload'])->name('get-upload');
    Route::post('upload/save', [Product\ProductController::class, 'save'])->name('upload/save');
    Route::post('chunkupload', [Product\ProductController::class, 'uploadFile']);
    Route::patch('upload/{id}', [Product\ProductController::class, 'uploadUpdate']);
    Route::get('get-group-url', [Product\GroupController::class, 'generateGroupUrl']);
    Route::post('save-user-column', [User\SoftDeleteController::class, 'saveUserColumn']);

    Route::get('export-users', [User\ClientController::class, 'exportUsers'])->name('export-users');
    Route::get('download-exported-file/{id}', [User\ClientController::class, 'downloadExportedFile'])->name('download.exported.file');
    Route::get('reports/view', [ReportController::class, 'viewReports']);
    Route::get('get-reports', [ReportController::class, 'getReports']);
    Route::delete('report-delete', [ReportController::class, 'destroyReports']);

    Route::get('records/column', [ReportController::class, 'viewRecordsColumn']);
    Route::post('add_records', [ReportController::class, 'addRecords']);

    Route::post('/save-columns', [User\ClientController::class, 'saveColumns'])->name('save-columns');
    Route::get('/get-columns', [User\ClientController::class, 'getColumns'])->name('get-columns');

    /*
     * Plan
     */

    Route::resource('plans', Product\PlanController::class);
    Route::get('get-plans', [Product\PlanController::class, 'getPlans'])->name('get-plans');
    // Route::get('get-plans', [Product\PlanController::class, 'GetPlans']);
    Route::delete('plans-delete', [Product\PlanController::class, 'destroy'])->name('plans-delete');
    Route::get('get-period', [Product\PlanController::class, 'checkSubscription'])->name('get-period');
    Route::post('postInsertPeriod', [Product\PlanController::class, 'postInsertPeriod']);

    /*
     * Currency
     */

    Route::resource('currency', Payment\CurrencyController::class);
    Route::get('get-currency/datatable', [Payment\CurrencyController::class, 'getCurrency'])->name('get-currency.datatable');
    Route::post('change/currency/status', [Payment\CurrencyController::class, 'updatecurrency'])->name('change.currency.status');

    /*
     * Tax
     */

    Route::resource('tax', Payment\TaxController::class);
    Route::get('get-state/{state}', [Payment\TaxController::class, 'getState']);
    Route::get('get-tax', [Payment\TaxController::class, 'getTax'])->name('get-tax');

    Route::get('get-taxtable', [Payment\TaxController::class, 'getTaxTable'])->name('get-taxtable');
    Route::get('get-loginstate/{state}', [Auth\AuthController::class, 'getState']);

    // Route::get('get-tax', [Payment\TaxController::class, 'GetTax']);

    Route::delete('tax-delete', [Payment\TaxController::class, 'destroy'])->name('tax-delete');
    Route::post('taxes/option', [Payment\TaxController::class, 'saveTaxOptionSetting'])->name('taxes/option');
    Route::post('taxes/class', [Payment\TaxController::class, 'saveTaxClassSetting']);

    /*
     * Promotion
     */

    Route::resource('promotions', Payment\PromotionController::class);

    Route::get('get-promotion-code', [Payment\PromotionController::class, 'getCode'])->name('get-code');
    Route::get('get-promotions', [Payment\PromotionController::class, 'getPromotion'])->name('get-promotions');
    Route::delete('promotions-delete', [Payment\PromotionController::class, 'destroy'])->name('promotions-delete');

    /*
     * Category
     */

    Route::resource('category', Product\CategoryController::class);
    Route::get('get-category', [Product\CategoryController::class, 'getCategory'])->name('get-category');
    Route::delete('category-delete', [Product\CategoryController::class, 'destroy'])->name('category-delete');

    /*
     * Comment
     */
    Route::resource('comment', User\CommentController::class);
    Route::delete('comment-delete', [User\CommentController::class, 'destroy'])->name('comment-delete');

    /*
         * License
         */
    Route::resource('license-type', License\LicenseSettingsController::class);
    Route::get('get-license-type', [License\LicenseSettingsController::class, 'getLicenseTypes'])->name('get-license-type');
    Route::delete('license-type-delete', [License\LicenseSettingsController::class, 'destroy'])->name('license-type-delete');
    Route::get('license-permissions', [License\LicensePermissionsController::class, 'index']);
    Route::get('get-license-permission', [License\LicensePermissionsController::class, 'getPermissions'])->name('get-license-permission');
    Route::delete('add-permission', [License\LicensePermissionsController::class, 'addPermission'])->name('add-permission');
    Route::get('tick-permission', [License\LicensePermissionsController::class, 'tickPermission'])->name('tick-permission');
    Route::get('orders/license/{order_number}', [License\LicenseController::class, 'licenseRedirect']);
    /*
     * Order
     */

    Route::resource('orders', Order\OrderController::class);
    Route::get('get-orders', [Order\OrderController::class, 'getOrders'])->name('get-orders');
    Route::get('get-product-versions/{product}', [Order\OrderSearchController::class, 'getProductVersions'])->name('get-product-versions');
    Route::delete('orders-delete', [Order\OrderController::class, 'destroy'])->name('orders-delete');
    Route::patch('change-domain', [Order\ExtendedOrderController::class, 'changeDomain']);
    Route::patch('reissue-license', [Order\ExtendedOrderController::class, 'reissueLicense']);
    Route::post('edit-update-expiry', [Order\BaseOrderController::class, 'editUpdateExpiry']);
    Route::post('edit-license-expiry', [Order\BaseOrderController::class, 'editLicenseExpiry']);
    Route::post('edit-support-expiry', [Order\BaseOrderController::class, 'editSupportExpiry']);
    Route::post('edit-installation-limit', [Order\BaseOrderController::class, 'editInstallationLimit']);

    Route::post('choose', [License\LocalizedLicenseController::class, 'chooseLicenseMode']);
    Route::get('LocalizedLicense', function () {
        return view('themes.default1.common.Localized');
    })->middleware('auth');
    Route::get('LocalizedLicense/delete/{fileName}', [License\LocalizedLicenseController::class, 'deleteFile']);
    //Route::post('LocalizedLicense/updateLicenseFile/{fileName}',[LocalizedLicenseController::class,'fileEdit']);
    Route::get('get-installation-details/{orderId}', [Order\OrderController::class, 'getInstallationDetails']);

    Route::get('export-orders', [Order\OrderController::class, 'exportOrders'])->name('export-orders');

    /*
     * Groups
     */

    Route::resource('groups', Product\GroupController::class);
    Route::get('get-groups', [Product\GroupController::class, 'getGroups'])->name('get-groups');
    Route::delete('groups-delete', [Product\GroupController::class, 'destroy'])->name('groups-delete');

    /*
     * Templates
     */

    Route::resource('template', Common\TemplateController::class);
    Route::get('get-templates', [Common\TemplateController::class, 'getTemplates'])->name('get-templates');
    // Route::get('get-templates', [Common\TemplateController::class, 'GetTemplates']);
    Route::delete('templates-delete', [Common\TemplateController::class, 'destroy'])->name('templates-delete');

    /**
     * Queue.
     */
    Route::get('queue', [Jobs\QueueController::class, 'index'])->name('queue');
    Route::get('get-queue', [Jobs\QueueController::class, 'getQueues'])->name('get-queue');
    Route::get('queue/{id}', [Jobs\QueueController::class, 'edit'])->name('queue.edit');
    Route::post('queue/{id}', [Jobs\QueueController::class, 'update'])->name('queue.update');

    Route::post('queue/{queue}/activate', [Jobs\QueueController::class, 'activate']);
    Route::get('form/queue', [Jobs\QueueController::class, 'getForm'])->name('queue.form');
    // Route::get('queue-monitoring', [Jobs\QueueController::class, 'monitorQueues']]);

    /*
     * Chat Script
     */
    Route::resource('chat', Common\ChatScriptController::class);
    Route::get('get-script', [Common\ChatScriptController::class, 'getScript'])->name('get-script');
    Route::delete('script-delete', [Common\ChatScriptController::class, 'destroy'])->name('script-delete');
    Route::post('order/execute', [Order\OrderController::class, 'orderExecute']);
    /*
     * Invoices
     */

    Route::get('invoices', [Order\InvoiceController::class, 'index']);
    Route::get('invoices/{id}', [Order\InvoiceController::class, 'show']);
    Route::get('get-client-invoice/{id}', [User\ClientController::class, 'getClientInvoice']);
    Route::get('invoices/edit/{id}', [Order\InvoiceController::class, 'edit']);
    Route::post('invoice/edit/{id}', [Order\InvoiceController::class, 'postEdit']);
    Route::get('get-invoices', [Order\InvoiceController::class, 'getInvoices'])->name('get-invoices');
    Route::get('pdf', [Order\InvoiceController::class, 'pdf']);
    Route::delete('invoice-delete', [Order\InvoiceController::class, 'destroy'])->name('invoice-delete');
    Route::get('invoice/generate', [Order\InvoiceController::class, 'generateById']);
    Route::post('generate/invoice/{user_id?}', [Order\InvoiceController::class, 'invoiceGenerateByForm']);
    Route::post('change-invoiceTotal', [Order\InvoiceController::class, 'invoiceTotalChange'])->name('change-invoiceTotal');
    Route::post('change-paymentTotal', [Order\InvoiceController::class, 'paymentTotalChange'])->name('change-paymentTotal');

    Route::get('export-invoices', [Order\InvoiceController::class, 'exportInvoices'])->name('export-invoices');

    /*
     * Payment
     */
    Route::get('newPayment/receive', [Order\InvoiceController::class, 'newPayment']);
    Route::post('newPayment/receive/{clientid}', [Order\InvoiceController::class, 'postNewPayment']);
    Route::get('payment/receive', [Order\InvoiceController::class, 'payment']);
    Route::post('payment/receive/{id}', [Order\InvoiceController::class, 'postPayment']);
    Route::delete('payment-delete', [Order\InvoiceController::class, 'deletePayment'])->name('payment-delete');
    Route::get('payments/{payment_id}/edit', [Order\InvoiceController::class, 'paymentEditById']);
    Route::post('newMultiplePayment/receive/{clientid}', [Order\InvoiceController::class, 'postNewMultiplePayment']);
    Route::post('newMultiplePayment/update/{clientid}', [Order\InvoiceController::class, 'updateNewMultiplePayment']);

    /*
     * Pages
     */
    Route::resource('pages', Front\PageController::class)->middleware('admin');
    Route::get('pages/{slug}', [Front\PageController::class, 'show']);
    Route::get('page/search', [Front\PageController::class, 'search']);
    Route::get('get-pages', [Front\PageController::class, 'getPages'])->name('get-pages');
    Route::delete('pages-delete', [Front\PageController::class, 'destroy'])->name('pages-delete');

    /*
     * Widgets
     */
    Route::resource('widgets', Front\WidgetController::class);
    Route::get('get-widgets', [Front\WidgetController::class, 'getPages'])->name('get-widgets');
    // Route::get('get-widgets', [Front\WidgetController::class, 'GetPages']);
    Route::delete('widgets-delete', [Front\WidgetController::class, 'destroy']);

    /*
     * github
     */
    Route::get('github-auth-app', [Github\GithubController::class, 'authForSpecificApp']);
    Route::get('github-releases', [Github\GithubController::class, 'listRepositories']);
    Route::get('github-downloads', [Github\GithubController::class, 'getDownloadCount']);
    Route::get('github', [Github\GithubController::class, 'getSettings']);
    Route::post('github-setting', [Github\GithubController::class, 'postSettings']);

    /*
     * download
     */
    Route::get('download/{uploadid}/{userid}/{invoice_number}/{versionid}', [Product\ProductController::class, 'userDownload']);
    Route::get('product/download/{id}/{invoice?}', [Product\ProductController::class, 'adminDownload']);

    /*
     * check version
     */

    Route::get('version', [HomeController::class, 'getVersion']);
    Route::post('verification', [HomeController::class, 'faveoVerification']);
    Route::get('create-keys', [HomeController::class, 'createEncryptionKeys']);
    Route::get('encryption', [HomeController::class, 'getEncryptedData']);

    /*
     * plugins
     */
    Route::get('payment-gateway-integration', [Common\SettingsController::class, 'plugins']);
    Route::post('updatePaymentStatus', [Common\PaymentSettingsController::class, 'updatePaymentStatus']);

    // Route::get('get-plugin', [Common\PaymentSettingsController::class, 'getPlugin'])->name('get-plugin');
    // Route::get('getplugin', [Common\SettingsController::class, 'getPlugin']);
    Route::post('post-plugin', [Common\PaymentSettingsController::class, 'postPlugins'])->name('post.plugin');
    Route::post('plugin/delete/{slug}', [Common\PaymentSettingsController::class, 'deletePlugin'])->name('delete.plugin');
    Route::post('plugin/status/{slug}', [Common\PaymentSettingsController::class, 'statusPlugin'])->name('status.plugin');

    /*
     * Cron Jobs
     */

    Route::get('job-scheduler', [Common\SettingsController::class, 'getScheduler'])->name('get.job.scheduler');
    Route::patch('post-scheduler', [Common\SettingsController::class, 'postSchedular'])->name('post.job.scheduler')->name('post-scheduler'); //to update job scheduler
    Route::patch('cron-days', [Common\SettingsController::class, 'saveCronDays'])->name('cron-days')->name('cron-days');
    Route::post('verify-php-path', [Common\SettingsController::class, 'checkPHPExecutablePath'])->name('verify-cron');
    Route::get('file-storage', [Common\SettingsController::class, 'showFileStorage']);
    Route::post('file-storage-path', [Common\SettingsController::class, 'updateStoragePath']);
    Route::get('expired-subscriptions', [Common\CronController::class, 'eachSubscription']);

    /*


     /* Renew
     */

    Route::get('renew/{id}/{agents?}', [Order\RenewController::class, 'renewForm']);
    Route::post('renew/{id}', [Order\RenewController::class, 'renew']);
    Route::get('get-renew-cost', [Order\RenewController::class, 'getCost']);
    Route::post('client/renew/{id}', [Order\RenewController::class, 'renewByClient']);
    Route::get('autopaynow/{id}', [Front\ClientController::class, 'autoRenewbyid']);

    Route::get('generate-keys', [HomeController::class, 'createEncryptionKeys']);

    Route::get('get-code', [WelcomeController::class, 'getCode']);
    Route::get('get-currency', [WelcomeController::class, 'getCurrency'])->middleware('admin');
    Route::post('dashboard-currency/{id}', [Payment\CurrencyController::class, 'setDashboardCurrency']);
    Route::get('get-country', [WelcomeController::class, 'getCountry'])->middleware('admin');
    Route::get('country-count', [WelcomeController::class, 'countryCount'])->name('country-count')->middleware('admin');

    /*
    Cloud APIs
     */

    Route::resource('third-party-keys', ThirdPartyAppController::class);

    Route::get('get-third-party-app', [ThirdPartyAppController::class, 'getThirdPartyDetails'])->name('get-third-party-app');

    Route::get('get-app-key', [ThirdPartyAppController::class, 'getAppKey'])->name('get-app-key');

    Route::delete('third-party-delete', [ThirdPartyAppController::class, 'destroy'])->name('third-party-delete');

    Route::post('create/tenant', [Tenancy\TenantController::class, 'createTenant']);

    Route::post('change/domain', [Tenancy\CloudExtraActivities::class, 'changeDomain']);

    Route::get('view/tenant', [Tenancy\TenantController::class, 'viewTenant'])->middleware('admin');

    Route::get('get-tenants', [Tenancy\TenantController::class, 'getTenants'])->name('get-tenants')->middleware('admin');

    Route::delete('delete-tenant', [Tenancy\TenantController::class, 'destroyTenant'])->name('delete-tenant')->middleware('admin');

    Route::get('delete/domain/{orderNumber}/{isDelete}', [Tenancy\TenantController::class, 'DeleteCloudInstanceForClient']);

    Route::post('cloud-details', [Tenancy\TenantController::class, 'saveCloudDetails'])->name('cloud-details')->middleware('admin');

    Route::post('cloud-pop-up', [Tenancy\TenantController::class, 'cloudPopUp'])->name('cloud-pop-up')->middleware('admin');

    Route::post('cloud-product-store', [Tenancy\TenantController::class, 'cloudProductStore'])->name('cloud-product-store')->middleware('admin');

    Route::post('enable/cloud', [Tenancy\TenantController::class, 'enableCloud'])->name('enable-cloud')->middleware('admin');

    Route::post('upgrade-plan-for-cloud', [Tenancy\CloudExtraActivities::class, 'upgradePlan']);

    Route::get('api/domain', [Tenancy\CloudExtraActivities::class, 'domainCloudAutofill']);

    Route::post('api/takeCloudDomain', [Tenancy\CloudExtraActivities::class, 'orderDomainCloudAutofill']);

    Route::post('get-cloud-upgrade-cost', [Tenancy\CloudExtraActivities::class, 'getUpgradeCost']);

    Route::post('changeAgents', [Tenancy\CloudExtraActivities::class, 'agentAlteration']);

    Route::post('upgradeDowngradeCloud', [Tenancy\CloudExtraActivities::class, 'upgradeDowngradeCloud']);

    Route::post('api/takeCloudDomain', [Tenancy\CloudExtraActivities::class, 'orderDomainCloudAutofill']);

    Route::post('changeAgents', [Tenancy\CloudExtraActivities::class, 'agentAlteration']);

    Route::get('format-currency', [Tenancy\CloudExtraActivities::class, 'formatCurrency']);

    Route::get('processFormat', [Tenancy\CloudExtraActivities::class, 'processFormat']);

    Route::post('get-agent-inc-dec-cost', [Tenancy\CloudExtraActivities::class, 'getThePaymentCalculationDisplay']);

    // routes/web.php
    Route::post('/update-session', [Tenancy\CloudExtraActivities::class, 'updateSession'])->name('update-session');

    Route::get('fetch-data', [Tenancy\CloudExtraActivities::class, 'fetchData'])->name('fetch-data');

    Route::delete('delete-cloud-product', [Tenancy\CloudExtraActivities::class, 'DeleteProductConfig'])->name('delete-cloud-product');

    Route::delete('remove-location', [Tenancy\CloudExtraActivities::class, 'removeLocation'])->name('remove-location');

    Route::post('cloud-data-center-store', [Tenancy\CloudExtraActivities::class, 'storeCloudDataCenter'])->name('cloud-data-center-store');

    Route::get('export-tenats', [Tenancy\TenantController::class, 'exportTenats'])->name('export-tenats');

    /*
     * Api
     */
    Route::prefix('api')->group(function () {
        /*
         * Unautherised requests
         */
        Route::get('check-url', [Api\ApiController::class, 'checkDomain']);
        Route::post('/csp-report', [Api\ApiController::class, 'logCSP']);
    });

    /*
     * Api Keys
     */
    Route::get('third-party-integration', [Common\SettingsController::class, 'getKeys']);
    Route::patch('apikeys', [Common\SettingsController::class, 'postKeys']);
    Route::post('login', [Auth\LoginController::class, 'login'])->name('login');
    // Route::post('login', [Auth\LoginController::class, 'login'])->name('login');

    Route::post('otp/send', [Auth\AuthController::class, 'requestOtp']);
    Route::post('otp/sendByAjax', [Auth\AuthController::class, 'requestOtpFromAjax']);
    Route::post('otp/verify', [Auth\AuthController::class, 'verifyOtp']);
    Route::post('email/verify', [Auth\AuthController::class, 'verifyEmail']);
    Route::post('resend_otp', [Auth\AuthController::class, 'retryOTP']);
    Route::post('send-email', [Auth\AuthController::class, 'sendEmail']);
    Route::get('verify', [Auth\AuthController::class, 'verify']);

    Route::prefix('api')->withoutMiddleware(['web'])->middleware(['api'])->group(function () {
        Route::post('productDownload', [Product\BaseProductController::class, 'productDownload']);
        Route::post('productExist', [Product\BaseProductController::class, 'productFileExist']);

//        it receive the reports form the MSG91
        Route::post('msg91/reports', [Common\MSG91Controller::class, 'handleReports'])->withoutMiddleware(['admin', 'auth']);
    });

    Route::get('sms/reports', [Common\MSG91Controller::class, 'msg91Reports']);
    Route::get('getMsgReports', [Common\MSG91Controller::class, 'getMsg91Reports']);
    Route::get('msgThirdPartyUpdate/{thirdPartyId}', [MSG91Controller::class, 'getThirdPartyMsgDetails']);

    //preview image
    Route::get('preview-file', [FileManagerController::class, 'previewFile']);

    Route::get('getPipedriveFields/{group_id}', [PipedriveController::class, 'getLocalFields']);
    Route::get('pipedrive/mapping/{group_id}', [PipedriveController::class, 'getMapFields']);
    Route::post('sync/pipedrive', [PipedriveController::class, 'mappingFields']);
    Route::get('syncing/pipedriveFields', [PipedriveController::class, 'syncFields']);
    Route::post('pipedrive/get-dropdown', [PipedriveController::class, 'getDropdown']);
});
/*
* Faveo APIs
*/
Route::post('serial', [HomeController::class, 'serial']);
Route::post('v2/serial', [HomeController::class, 'serialV2']);
Route::get('download/faveo', [HomeController::class, 'downloadForFaveo']);
Route::get('version/latest', [HomeController::class, 'latestVersion']);
Route::post('update-latest-version', [HomeController::class, 'updateLatestVersion']);
Route::post('v1/checkUpdatesExpiry', [HomeController::class, 'checkUpdatesExpiry']);
Route::post('update/lic-code', [HomeController::class, 'updateLicenseCode']);
Route::get('new-version-available', [HomeController::class, 'isNewVersionAvailable']);
Route::post('update-installation-detail', [HomeController::class, 'updateInstallationDetails']);
Route::get('verify/third-party-token', [Tenancy\TenantController::class, 'verifyThirdPartyToken']);
Route::get('api/billingInfo', [HomeController::class, 'getDetailedBillingInfo']);
Route::get('api/pluginInfo', [HomeController::class, 'getDetailsForAClient']);
Route::get('api/billingRelease', [HomeController::class, 'getProductRelease']);

Route::post('renewurl', [HomeController::class, 'renewurl']);

Route::get('pricing/data', [HomeController::class, 'getPricingData']);
Route::get('group/data', [HomeController::class, 'getGroupDatails']);
Route::get('404', function () {
    return view('errors.404');
})->name('error404');
Route::get('/api/download/agents', [Product\BaseProductController::class, 'agentProductDownload']);
Route::get('/product/detail', [Product\BaseProductController::class, 'getProductUsingLicenseCode']);
Route::get('footer1', [Front\WidgetController::class, 'footer1'])->name('footer1')->withoutMiddleware(['auth', 'admin']);

//language
Route::get('languages', [LanguageController::class, 'viewLanguage'])->middleware('auth');
Route::post('language-toggle', [LanguageController::class, 'toggleLanguageStatus']);
Route::get('language/control', [LanguageController::class, 'fetchLangDropdownUsers']);

// });
