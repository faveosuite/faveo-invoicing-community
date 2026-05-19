<?php

use App\Http\Controllers\Api;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Common;
use App\Http\Controllers\Common\FileManagerController;
use App\Http\Controllers\Common\PipedriveController;
use App\Http\Controllers\Common\Sms\MSG91Controller;
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
use App\Http\Controllers\Payment\OpenPaymentController;
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
Route::match(['get', 'post'], 'faveo-whatsapp', [\App\Http\Controllers\WhatsappController::class, 'whatsappWebhook']);

Route::post('create/tenant/purchase', [Tenancy\CloudExtraActivities::class, 'storeTenantTillPurchase']);

// VisitStats::routes();
Route::get('refresh-csrf', function () {
    return response()->json([
        'token' => csrf_token(), ],
        200);
});

Route::post('otp2/send', [Auth\AuthController::class, 'otp']);
//Route::post('verifying/phone', [PhoneVerificationController::class, 'create']);

Route::middleware('installAgora')->group(function () {
    Route::get('whatsapp-test', function () {
        return view('themes.default1.common.whatsapp-testing');
    });
    Route::get('get-webhook-url', [\App\Http\Controllers\WhatsappController::class, 'getWebhookUrl']);
    Route::get('whatsapp-integration', [\App\Http\Controllers\WhatsappController::class, 'index']);
    Route::get('whatsapp-users', [\App\Http\Controllers\WhatsappController::class, 'index1'])->middleware('auth');
    Route::post('webhook-url-edit', [\App\Http\Controllers\WhatsappController::class, 'webhookUrlEdit']);
    Route::get('whatsapp-users-table', [\App\Http\Controllers\WhatsappController::class, 'whatsappTable']);
    Route::get('whatsapp-users-api', [\App\Http\Controllers\WhatsappController::class, 'whatsappUsersApi']);
    Route::get('whatsapp-client-table/{orderid}', [\App\Http\Controllers\WhatsappController::class, 'whatsappClientTable']);
    Route::post('whatsapp-deregister', [\App\Http\Controllers\WhatsappController::class, 'deregister']);
    Route::post('direct-whatsapp', [\App\Http\Controllers\WhatsappController::class, 'directSaveWhatsapp']);
    Route::post('url-save', [\App\Http\Controllers\WhatsappController::class, 'urlSave']);
    // Route::post('save-access-token', [\App\Http\Controllers\WhatsappController::class, 'saveAccessToken']);
    Route::post('save-waba-id', [\App\Http\Controllers\WhatsappController::class, 'saveWabaId']);
    Route::get('whatsapp-integration-info', [\App\Http\Controllers\WhatsappController::class, 'whatsappIntegration']);
    Route::post('whatsapp-integration-save', [\App\Http\Controllers\WhatsappController::class, 'whatsappSave']);
    Route::post('store_toggle_state', [Common\TemplateController::class, 'toggle'])->withoutMiddleware(['auth', 'admin']);
    Route::get('pricing', [Front\CartController::class, 'cart'])->name('pricing');
    Route::get('group/{templateid}/{group}/', [Front\PageController::class, 'pageTemplates']);
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
//    Route::get('mailchimp', [Common\MailChimpController::class, 'mailChimpSettings'])->middleware('admin');
    Route::patch('mailchimp', [Common\MailChimpController::class, 'postMailChimpSettings']);
    Route::get('mail-chimp/mapping', [Common\MailChimpController::class, 'mapField'])->middleware('admin');
    Route::patch('mail-chimp/mapping', [Common\MailChimpController::class, 'postMapField']);
    Route::patch('mailchimp-ispaid/mapping', [Common\MailChimpController::class, 'postIsPaidMapField']);
    Route::patch('mailchimp-group/mapping', [Common\MailChimpController::class, 'postGroupMapField']);
    Route::get('get-group-field/{value}', [Common\MailChimpController::class, 'addInterestFieldsToAgora']);
    Route::get('contact-us', [Front\PageController::class, 'contactUs']);
    Route::post('contact-us', [Front\PageController::class, 'postContactUs']);
    Route::post('remove-coupon', [Front\CartController::class, 'removeCoupon']);
    Route::post('remove-product');
    Route::get('confirm/payment', [RazorpayController::class, 'afterPayment']);
    Route::post('stripeUpdatePayment/confirm', [Front\ClientController::class, 'stripeUpdatePayment']);
    Route::get('get-my-payment/{orderid}/{userid}', [Front\ClientController::class, 'getPaymentByOrderId'])->name('get-my-payment');

    /*
     * Front Client Pages
     *
     * All the api's under this are for client panel
     *
     *
     *
     */

    //Login api's
    Route::post('login', [Auth\LoginController::class, 'login'])->name('login')->middleware(['blockFailedVerifications:login']);
    Route::auth();
    Route::post('auth/register', [Auth\RegisterController::class, 'postRegister'])->name('auth/register');
    Route::get('auth/logout', [Auth\LoginController::class, 'logout'])->name('logout');

    Route::middleware(['blockFailedVerifications:2fa', 'session.timeout:10,2fa'])->group(function () {
        Route::get('2fa/session-check', [Google2FAController::class, 'verifySession'])->name('2fa.session.check');
        Route::get('recovery-code', [Google2FAController::class, 'showRecoveryCode']);
        Route::get('verify-2fa', [Google2FAController::class, 'verify2fa']);
        Route::post('2fa/loginValidate', [Google2FAController::class, 'postLoginValidateToken'])->name('2fa/loginValidate');
        Route::post('verify-recovery-code', [Google2FAController::class, 'verifyRecoveryCode'])->name('verify-recovery-code');
    });

    /*
 * 2FA Routes
 */

    Route::post('/2fa/enable', [Google2FAController::class, 'enableTwoFactor']);
    Route::post('2fa/disable/{userId?}', [Google2FAController::class, 'disableTwoFactor']);

    Route::post('2fa/setupValidate', [Google2FAController::class, 'postSetupValidateToken']);
    Route::get('verify-password', [Google2FAController::class, 'verifyPassword']);
    Route::post('2fa-recovery-code', [Google2FAController::class, 'generateRecoveryCode']);
    Route::get('get-recovery-code', [Google2FAController::class, 'getRecoveryCode']);
    Route::get('recovery-code', [Google2FAController::class, 'showRecoveryCode']);
    Route::post('verify-2fa-admin', [Google2FAController::class, 'postSetupValidateToken'])->name('verify.2fa.admin');
    Route::post('verify-recovery-code', [Google2FAController::class, 'verifyRecoveryCode'])->name('verify-recovery-code');

    Route::get('get-loginstate/{state}', [Auth\AuthController::class, 'getState']);
    Route::get('get-countries', [Auth\AuthController::class, 'getCountries']);
    Route::get('get-code', [WelcomeController::class, 'getCode']);
    Route::get('get-currency', [WelcomeController::class, 'getCurrency'])->middleware('admin'); //Not in use

    //Dashboard api's
    Route::get('client-dashboard', [Front\ClientController::class, 'index']); // use this or use the next one which will be very useful
    Route::get('client-dashboard-details', [Front\ClientController::class, 'clientDetails']);

    // master api's
    Route::get('master-data', [Front\ClientController::class, 'masterData'])->name('master-data');
    Route::post('demo-request', [Front\PageController::class, 'postDemoReq'])->withoutMiddleware(['auth']);
    Route::get('language/control', [LanguageController::class, 'fetchLangDropdownUsers']);
    Route::get('js/lang', [LanguageController::class, 'getLanguageFile'])->name('assets.lang');
    Route::post('trial-cloud-products', [Tenancy\CloudExtraActivities::class, 'trialCloudProducts']);
    Route::post('create/tenant/purchase', [Tenancy\CloudExtraActivities::class, 'storeTenantTillPurchase']);
    Route::post('available-groups', [Product\GroupController::class, 'getAvailableGroups'])->withoutMiddleware(['auth', 'admin']);
    Route::post('mail-chimp/subcribe', [Common\MailChimpController::class, 'addSubscriberByClientPanel']);
    Route::post('first-login', [FreeTrailController::class, 'firstLoginAttempt']);

    //invoice api's
    Route::get('my-invoices', [Front\ClientController::class, 'invoices'])->name('my-invoices');
    Route::get('my-invoice/{id}', [Front\ClientController::class, 'getInvoice']);
    Route::get('get-my-invoices', [Front\ClientController::class, 'getInvoices'])->name('get-my-invoices');
    Route::delete('invoices/delete/{id}', [Front\ClientController::class, 'invoiceDelete']);
    Route::get('paynow/{id}', [Front\CheckoutController::class, 'payNow']);
    //when company name and address is not present in the users details a dialog box will open and the details will be taken
    Route::post('store-basic-details', [Auth\LoginController::class, 'storeBasicDetailsss'])->name('store-basic-details');

    //order api's
    Route::get('my-orders', [Front\ClientController::class, 'orders']);
    Route::get('get-my-orders', [Front\ClientController::class, 'getClientOrderVue'])->name('get-my-orders');
    Route::get('my-order/{id}', [Front\ClientController::class, 'getOrder']);
    Route::get('renew-popup-details/{productid}', [Front\ClientController::class, 'renewPopupVue']);
    Route::get('get-my-invoices/{orderid}/{userid}/{admin?}', [Front\ClientController::class, 'getInvoicesByOrderId']);
    Route::get('get-my-payment-client/{orderid}/{userid}', [Front\ClientController::class, 'getPaymentByOrderIdClient'])->name('get-my-payment-client');
    // Route::get('autoPayment-client/{orderid}', [Front\ClientController::class, 'getAutoPaymentStatus']);
    Route::get('get-versions/{productid}/{clientid}/{invoiceid}/', [Front\ClientController::class, 'getVersionList'])->name('get-versions');
    Route::get('get-github-versions/{productid}/{clientid}/{invoiceid}/', [Front\ClientController::class, 'getGithubVersionList'])->name('get-github-versions');

    //renew api's
    Route::get('renew/{id}/{agents?}', [Order\RenewController::class, 'renewForm']);
    Route::post('renew/{id}', [Order\RenewController::class, 'renew']);
    Route::get('get-renew-cost', [Order\RenewController::class, 'getCost']);
    Route::post('client/renew/{id}', [Order\RenewController::class, 'renewByClient']);
    Route::get('autopaynow/{id}', [Front\ClientController::class, 'autoRenewbyid']);

    //cart api's
    Route::get('cart-access', [Front\BaseClientController::class, 'cartAccess']);
    Route::post('cart/remove', [Front\CartController::class, 'cartRemove']);

    Route::post('strRenewal-enable', [Front\ClientController::class, 'enableAutorenewalStatus']);
    Route::post('renewal-disable', [Front\ClientController::class, 'disableAutorenewalStatus']);
    Route::post('rzpRenewal-disable/{orderid}', [Front\ClientController::class, 'enableRzpStatus']);
    //Route::get('my-subscriptions', [Front\ClientController::class, 'subscriptions']);
    //Route::get('get-my-subscriptions', [Front\ClientController::class, 'getSubscriptions']);
    Route::get('uploadFile', [License\LocalizedLicenseController::class, 'storeFile']);
    Route::get('my-profile', [Front\ClientController::class, 'profile']);
    Route::patch('my-profile', [Front\ClientController::class, 'postProfile']);
    Route::patch('my-password', [Front\ClientController::class, 'postPassword']);
    Route::get('paynow/{id}', [Front\CheckoutController::class, 'payNow'])->middleware(['auth']);

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
     * Social Media
     */
    Route::prefix('social-media')->group(function () {
        Route::get('list', [Common\SocialMediaController::class, 'getSocialList']);
        Route::get('show/{id}', [Common\SocialMediaController::class, 'getSocialMedia']);
        Route::post('create', [Common\SocialMediaController::class, 'createSocialMedia']);
        Route::patch('update/{id}', [Common\SocialMediaController::class, 'updateSocial']);
        Route::delete('delete', [Common\SocialMediaController::class, 'deleteSocialMedia']);
    });

//    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/', fn () => redirect(url('admin/dashboard')));

    Route::get('/auth/redirect/{provider}', [Auth\LoginController::class, 'redirectToGithub']);
    Route::get('/auth/callback/{provider}', [Auth\LoginController::class, 'handler']);

    Route::get('activate/{token}', [Auth\AuthController::class, 'activate']);
    Route::get('footer1', [Front\WidgetController::class, 'footer1'])->name('footer1')->withoutMiddleware(['auth', 'admin']);
//    Route::get('activate/{token}', [Auth\AuthController::class, 'activate']);

    /*
     * Client api's completion
     *
     */

    /*
     * Email Api keys
     */
    Route::post('emailData', [Common\SettingsController::class, 'emailData']);
    Route::post('emailCheckboxData', [Common\SettingsController::class, 'emailCheckboxData']);
    Route::get('get-email-validation-logs', [Common\SettingsController::class, 'getEmailValidationLogs']);
    Route::get('get-email-validation-results', [Common\SettingsController::class, 'getEmailValidationResults']);
    Route::get('get-email-validation-user-results', [Common\SettingsController::class, 'getEmailValidationUserResults']);

    Route::post('mobileData', [Common\SettingsController::class, 'mobileData']);
    Route::post('email-settings-save', [Common\SettingsController::class, 'emailSettingsSave']);

    /*
     * Github Api keys
     */
    Route::post('githubkeys', [Common\SettingsController::class, 'githubkeys']);
    Route::post('github-setting', [Github\GithubController::class, 'postSettings']);

    /*
     * Mobile Api keys
     */
    Route::post('mobileData', [Common\SettingsController::class, 'mobileData']);
    Route::post('mobile-settings-save', [Common\SettingsController::class, 'mobileSettingsSave']);

    /*
     * Google ReCaptcha Api Keys
     */
    Route::post('captchaDetails', [Common\BaseSettingsController::class, 'captchaDetails'])->name('captchaDetails');
    Route::post('googleCaptcha', [Common\SettingsController::class, 'googleCaptcha']);

    /*
     * Mailchimp Api keys
     */
    Route::post('mailchimpkeys', [Common\SettingsController::class, 'mailchimpKeys']);
    Route::post('updateMailchimpDetails', [Common\BaseSettingsController::class, 'updateMailchimpDetails'])->name('updateMailchimpDetails');

    /*
     * Mobile Verification Api (Msg91)
     */
    Route::post('mobileVerification', [Common\SettingsController::class, 'mobileVerification']);
    Route::get('msgThirdPartyUpdate/{thirdPartyId}', [MSG91Controller::class, 'getThirdPartyMsgDetails']);
    Route::post('updatemobileDetails', [Common\BaseSettingsController::class, 'updateMobileDetails'])->name('updatemobileDetails');

    /*
     * Pipedrive Api keys
     */
    Route::post('pipedrivekeys', [Common\SettingsController::class, 'pipedrivekeys']);
    Route::post('updatepipedriveDetails', [Common\BaseSettingsController::class, 'updatepipedriveDetails'])->name('updatepipedriveDetails');

    /*
     * Terms Api Keys
     */
    Route::post('termsUrl', [Common\SettingsController::class, 'termsUrl']);
    Route::post('updateTermsDetails', [Common\BaseSettingsController::class, 'updateTermsDetails'])->name('updateTermsDetails');

    /*
     * Profile Process
     */
    Route::get('profile', [User\ProfileController::class, 'profile']);
    Route::patch('profile', [User\ProfileController::class, 'updateProfile']);
    Route::patch('password', [User\ProfileController::class, 'updatePassword']);
    Route::get('profile/countries', [User\ProfileController::class, 'getCountries']);
    Route::get('profile/states/{countryCode}', [User\ProfileController::class, 'getStatesByCountry']);

    /*
     * Settings
     */

    Route::post('changeLogo', [Common\SettingsController::class, 'delete']);

    Route::get('settings', [Common\SettingsController::class, 'settings']);
    Route::get('/datatable/data', [Common\SettingsController::class, 'getDataTableData'])->name('datatable.data');
    Route::post('mobileVerification', [Common\SettingsController::class, 'mobileVerification']);
    Route::post('termsUrl', [Common\SettingsController::class, 'termsUrl']);
    Route::post('zohokeys', [Common\SettingsController::class, 'zohokeys']);
    Route::post('twitterkeys', [Common\SettingsController::class, 'twitterkeys']);

    /**
     * System Settings.
     */
    Route::get('systemSettings/list', [Common\SettingsController::class, 'settingsSystem']);
    Route::post('systemSettings/update', [Common\SettingsController::class, 'postSettingsSystem']);
    Route::get('settings/system-data', [Common\SettingsController::class, 'getSystemSettingsData']);
    Route::patch('settings/system-data', [Common\SettingsController::class, 'updateSystemSettingsData']);

    Route::get('settings/email', [Common\EmailSettingsController::class, 'settingsEmail'])->middleware('auth');
    Route::patch('settings/email', [Common\EmailSettingsController::class, 'postSettingsEmail']);
    Route::get('settings/index-data', [Common\SettingsController::class, 'getSettingsIndexData']);
    Route::get('settings/template', [Common\SettingsController::class, 'settingsTemplate']);
    Route::patch('settings/template', [Common\SettingsController::class, 'postSettingsTemplate']);
    Route::get('settings/error', [Common\SettingsController::class, 'getErrorSettings']);
    Route::patch('settings/error', [Common\SettingsController::class, 'postSettingsError']);
    Route::get('settings/cron-data', [Common\SettingsController::class, 'getCronSettingsData']);
    Route::patch('settings/cron-data', [Common\SettingsController::class, 'updateCronSettingsData']);
    Route::patch('settings/cron-days', [Common\SettingsController::class, 'updateCronDaysData']);
    Route::get('settings/recaptcha', [Common\SettingsController::class, 'getRecaptchaSettings']);
    Route::patch('settings/recaptcha', [Common\SettingsController::class, 'updateRecaptchaSettings']);
    Route::get('settings/pipedrive', [Common\SettingsController::class, 'getPipedriveSettings']);
    Route::patch('settings/pipedrive', [Common\SettingsController::class, 'updatePipedriveSettings']);
    Route::get('settings/msg91', [Common\SettingsController::class, 'getMsg91Settings']);
    Route::get('settings/github', [Common\SettingsController::class, 'getGithubSettings']);
    Route::get('settings/mailchimp', [Common\SettingsController::class, 'getMailchimpSettings']);
    Route::get('settings/terms', [Common\SettingsController::class, 'getTermsSettings']);
    Route::get('settings/email-validation', [Common\SettingsController::class, 'getEmailValidationSettings']);
    Route::get('settings/mobile-validation', [Common\SettingsController::class, 'getMobileValidationSettings']);
    Route::get('settings/cloud-details', [Common\SettingsController::class, 'getCloudDetails']);
    Route::get('localized-license/files', [LocalizedLicenseController::class, 'filesApi']);
    Route::delete('localized-license/files', [LocalizedLicenseController::class, 'deleteFileApi']);
    Route::get('settings/activitylog', [Common\SettingsController::class, 'settingsActivity']);
    Route::get('settings/maillog', [Common\SettingsController::class, 'settingsMail']);

    // Debug APi
    Route::get('debugg', [Common\SettingsController::class, 'debugSettings']);
    Route::post('save/debugg', [Common\SettingsController::class, 'postdebugSettings']);

    // Social Logins Api
    Route::get('social-logins', [SocialLoginsController::class, 'getSocialLogin'])->middleware('auth');
    Route::get('edit/SocialLogins/{id}', [SocialLoginsController::class, 'editSocialLogin'])->middleware('auth');
    Route::post('update-social-login', [SocialLoginsController::class, 'updateSocialLogin'])->name('update-social-login');

    //language
    Route::get('languages', [LanguageController::class, 'viewLanguage'])->middleware('auth');
    Route::post('language-toggle', [LanguageController::class, 'toggleLanguageStatus']);
    Route::post('language-set-default', [LanguageController::class, 'setDefaultLanguage']);

    // Contact API
    Route::get('contact-option', [Common\SettingsController::class, 'contactOption'])->name('contact-option');
    Route::post('verificationSettings', [Common\SettingsController::class, 'postContactOption']);

    // System Manager APi
    Route::get('system-managers', [Common\SystemManagerController::class, 'getSystemManagers'])->name('system-managers');
    Route::get('search-admins', [Common\SystemManagerController::class, 'searchAdmin'])->name('search-admins');
    Route::post('updateSystemManager', [Common\SystemManagerController::class, 'updateManagerSettings']);

    /*
     * System Logs
    */

    // Get Activity Log
    Route::get('get-activity', [Common\SettingsController::class, 'getActivity'])->name('get-activity');
    Route::get('get-activity-api', [Common\SettingsController::class, 'getActivityApi']);
    Route::get('get-activity-filters', [Common\SettingsController::class, 'getActivityFilters']);

    // Get Payment Log
//    Route::get('settings/paymentlog', [Common\SettingsController::class, 'settingsPayment']);
    Route::get('get-paymentlog', [Common\SettingsController::class, 'getPaymentlog'])->name('get-paymentlog');
    Route::get('get-payment-log-api', [Common\SettingsController::class, 'getPaymentLogApi']);
    Route::delete('paymentlog-delete', [Common\SettingsController::class, 'destroyPayment'])->name('paymentlog-delete');

    // Get Msg91 Log
//    Route::get('sms/reports', [Common\Sms\MSG91Controller::class, 'msg91Reports']);
    Route::get('sms/reports', [Common\Sms\MSG91Controller::class, 'getMsg91Reports']);
    Route::get('getMsgStatus', [Common\Sms\MSG91Controller::class, 'getMsgStauts']);

    Route::get('get-email', [Common\SettingsController::class, 'getMails'])->name('get-email');
    Route::get('/email-log/body/{id}', [Common\SettingsController::class, 'getBody'])->name('email-log.body');
    Route::delete('activity-delete', [Common\SettingsController::class, 'destroy'])->name('activity-delete');
    Route::delete('email-delete', [Common\SettingsController::class, 'destroyEmail'])->name('email-delete');
    Route::post('licenseStatus', [Common\SettingsController::class, 'licenseStatus'])->name('licenseStatus');
    Route::post('updateDetails', [Common\SettingsController::class, 'updateDetails'])->name('updateDetails');
    Route::post('updatemobileDetails', [Common\SettingsController::class, 'updateMobileDetails'])->name('updatemobileDetails');
    Route::post('updateemailDetails', [Common\SettingsController::class, 'updateEmailDetails'])->name('updateemailDetails');
    Route::post('updatetwitterDetails', [Common\SettingsController::class, 'updateTwitterDetails'])->name('updatetwitterDetails');
    Route::post('updatezohoDetails', [Common\SettingsController::class, 'updateZohoDetails'])->name('updatezohoDetails');
    Route::post('mailchimp-prod-status', [Common\SettingsController::class, 'updateMailchimpProductStatus'])->name('mailchimp-prod-status');
    Route::post('mailchimp-paid-status', [Common\SettingsController::class, 'updateMailchimpIsPaidStatus'])->name('mailchimp-paid-status');
    Route::post('updatedomainCheckDetails', [Common\SettingsController::class, 'updatedomainCheckDetails'])->name('updatedomainCheckDetails');
    Route::post('v3captchaDetails', [Common\SettingsController::class, 'v3captchaDetails'])->name('v3captchaDetails');
    Route::get('demo/page', [Front\PageController::class, 'VewDemoPage']);
    Route::post('save/demo', [Front\PageController::class, 'saveDemoPage']);

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
    Route::post('upload-image', [Product\ProductController::class, 'uploadImage'])->name('upload-image');
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
    Route::prefix('currency')->group(function () {
        Route::get('list', [Payment\CurrencyController::class, 'getCurrencyList']);
        Route::post('update-currency', [Payment\CurrencyController::class, 'updatecurrency']);
        Route::post('dashboard-currency/{id}', [Payment\CurrencyController::class, 'setDashboardCurrency']);
        Route::post('default-currency/{id}', [Payment\CurrencyController::class, 'setDefaultCurrency']);
    });

    /*
     * Tax
     */

    Route::get('tax-options', [Payment\TaxController::class, 'getTaxOptionsApi']);
    Route::post('taxes/option', [Payment\TaxController::class, 'saveTaxOptionSetting'])->name('taxes/option');
    Route::get('tax-tables', [Payment\TaxController::class, 'getTax']);
    Route::get('tax/edit/{id}', [Payment\TaxController::class, 'editTaxApi']);
    Route::put('tax/{id}', [Payment\TaxController::class, 'updateTaxApi']);
    Route::post('create/tax-class', [Payment\TaxController::class, 'saveTaxClassSettingApi']);
    Route::delete('tax/delete', [Payment\TaxController::class, 'deleteTax']);
    Route::get('get-state/{state}', [Payment\TaxController::class, 'getState']);
    Route::get('get-taxtable', [Payment\TaxController::class, 'getTaxTable'])->name('get-taxtable');

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
     * License Type
    */
    Route::get('get-license-type', [License\LicenseSettingsController::class, 'getLicenseTypes'])->name('get-license-type');
    Route::get('get-license-type/{id}', [License\LicenseSettingsController::class, 'getLicenseTypeById']);
    Route::post('create-license-type', [License\LicenseSettingsController::class, 'createLicense']);
    Route::put('update-license-type/{id}', [License\LicenseSettingsController::class, 'updateLicense']);
    Route::delete('delete-license-type', [License\LicenseSettingsController::class, 'deleteLicense'])->name('license-type-delete');

    /*
     * License Permission
    */
    Route::get('get-license-permission', [License\LicensePermissionsController::class, 'getPermissions'])->name('get-license-permission');
    Route::delete('add-permission', [License\LicensePermissionsController::class, 'addPermission'])->name('add-permission');
    Route::get('tick-permission', [License\LicensePermissionsController::class, 'tickPermission'])->name('tick-permission');
    Route::get('orders/license/{order_number}', function ($orderNumber) {
        return redirect('/orders/'.\App\Model\Order\Order::where('number', $orderNumber)->value('id'));
    });
    /*
     * Order
     */

    Route::resource('orders', Order\OrderController::class);
    Route::get('get-orders', [Order\OrderController::class, 'getOrders'])->name('get-orders');
    Route::get('get-product-versions/{product}', [Order\OrderSearchController::class, 'getProductVersions'])->name('get-product-versions');
    Route::delete('orders-delete', [Order\OrderController::class, 'destroy'])->name('orders-delete');
//    This isn't used anywhere
//    Route::patch('change-domain', [Order\ExtendedOrderController::class, 'changeDomain']);
    Route::patch('reissue-license', [Order\ExtendedOrderController::class, 'reissueLicense']);
    Route::post('edit-update-expiry', [Order\BaseOrderController::class, 'editUpdateExpiry']);
    Route::post('edit-license-expiry', [Order\BaseOrderController::class, 'editLicenseExpiry']);
    Route::post('edit-support-expiry', [Order\BaseOrderController::class, 'editSupportExpiry']);
    Route::post('edit-installation-limit', [Order\BaseOrderController::class, 'editInstallationLimit']);

    Route::post('choose', [License\LocalizedLicenseController::class, 'chooseLicenseMode']);
    Route::get('LocalizedLicense', function () {
        return view('themes.default1.common.Localized');
    })->middleware(['auth', 'admin']);
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

    /**
     * Templates.
     */
    Route::prefix('template')->group(function () {
        Route::get('list', [Common\TemplateController::class, 'getTemplates']);
        Route::get('edit/{id}', [Common\TemplateController::class, 'showTemplate']);
        Route::put('update/{id}', [Common\TemplateController::class, 'updateTemplate']);
    });

    /**
     * Queue.
     */
    Route::get('queue/list', [Jobs\QueueController::class, 'getQueueData']);
    Route::get('queue/{id}', [Jobs\QueueController::class, 'edit'])->name('queue.edit');
    Route::post('queue/{id}', [Jobs\QueueController::class, 'update'])->name('queue.update');
    Route::post('queue/{queue}/activate', [Jobs\QueueController::class, 'activate']);
    Route::get('queue/{id}/form', [Jobs\QueueController::class, 'getFormById'])->name('queue.form');

    /*
     * Monitoring (Pulse / Horizon) — path check API
     */
    Route::get('monitoring/check', [Common\Monitoring\MonitoringController::class, 'checkPulseHorizon'])
        ->name('monitoring.check');

    /*
     * Chat Script
     */
    Route::prefix('chat')->group(function () {
        Route::get('list', [Common\ChatScriptController::class, 'getScriptList']);
        Route::get('show/{id}', [Common\ChatScriptController::class, 'getScript']);
        Route::post('create', [Common\ChatScriptController::class, 'createScript']);
        Route::put('update/{id}', [Common\ChatScriptController::class, 'updateScript']);
        Route::delete('delete', [Common\ChatScriptController::class, 'deleteScript']);
    });

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
    Route::prefix('widgets')->group(function () {
        Route::get('list', [Front\WidgetController::class, 'getWidgetList']);
        Route::get('show/{id}', [Front\WidgetController::class, 'getWidget']);
        Route::put('update/{id}', [Front\WidgetController::class, 'updateWidget']);
        Route::delete('delete', [Front\WidgetController::class, 'deleteWidget']);
        Route::post('create', [Front\WidgetController::class, 'createWidget']);
    });

    /*
     * github
     */
    Route::get('github-auth-app', [Github\GithubController::class, 'authForSpecificApp']);
    Route::get('github-releases', [Github\GithubController::class, 'listRepositories']);
    Route::get('github-downloads', [Github\GithubController::class, 'getDownloadCount']);
//    Route::get('github', [Github\GithubController::class, 'getSettings']);

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
    Route::get('payment-gateway-list', [Common\PaymentSettingsController::class, 'getPaymentGatewayList']);
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
    Route::get('cron/condition/{job}', [Common\SettingsController::class, 'getCronCondition']);

    Route::get('file-storage', [Common\SettingsController::class, 'showFileStorage']);
    Route::post('file-storage-path', [Common\SettingsController::class, 'updateStoragePath']);
//    We don't use this so commanded this
//    Route::get('expired-subscriptions', [Common\CronController::class, 'eachSubscription']);

    Route::get('generate-keys', [HomeController::class, 'createEncryptionKeys']);

    Route::get('get-country', [WelcomeController::class, 'getCountry'])->middleware('admin');

    Route::get('get-code', [WelcomeController::class, 'getCode']);
    Route::get('get-currency', [WelcomeController::class, 'getCurrency'])->middleware('admin');

    /*
     * Third Party Apps
     */

    Route::get('get-third-party-app', [ThirdPartyAppController::class, 'getThirdPartyDetails'])->name('get-third-party-app');

    Route::post('third-party-app-create', [ThirdPartyAppController::class, 'createThirdPartyApp'])->name('third-party-app-create');

    Route::put('third-party-app-update/{id}', [ThirdPartyAppController::class, 'updateThirdPartyApp'])->name('third-party-app-update');

    Route::get('get-app-key', [ThirdPartyAppController::class, 'getAppKey'])->name('get-app-key');

    Route::delete('third-party-delete', [ThirdPartyAppController::class, 'deleteThirdPartyApp'])->name('third-party-delete');

    /*
    * Cloud Api's
    */
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

    Route::post('api/takeCloudDomain', [Tenancy\CloudExtraActivities::class, 'orderDomainCloudAutofill']); //Not in use

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
    Route::post('update-trial-status', [Tenancy\CloudExtraActivities::class, 'updateTrialStatus'])->name('update-trial-status');
    Route::delete('delete-cloud-product', [Tenancy\CloudExtraActivities::class, 'DeleteProductConfig'])->name('delete-cloud-product');

    Route::delete('remove-location', [Tenancy\CloudExtraActivities::class, 'removeLocation'])->name('remove-location');

    Route::post('cloud-data-center-store', [Tenancy\CloudExtraActivities::class, 'storeCloudDataCenter'])->middleware('admin')->name('cloud-data-center-store');

    Route::get('export-tenats', [Tenancy\TenantController::class, 'exportTenats'])->middleware('admin')->name('export-tenats');

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
    Route::post('api/login', [Auth\LoginController::class, 'postLoginAndGetToken']);

    Route::middleware(['blockFailedVerifications:verify', 'session.timeout:10,verify'])->group(function () {
        Route::post('otp/send', [Auth\AuthController::class, 'requestOtp']);
        Route::post('resend_otp', [Auth\AuthController::class, 'retryOTP']);
        Route::post('send-email', [Auth\AuthController::class, 'sendEmail']);
        Route::get('verify', [Auth\AuthController::class, 'verify']);
    });

    Route::middleware(['session.timeout:10,verify'])->group(function () {
        Route::get('verify/session-check', [Auth\AuthController::class, 'verifySession'])->name('verify.session.check');
        Route::post('otp/verify', [Auth\AuthController::class, 'verifyOtp']);
        Route::post('email/verify', [Auth\AuthController::class, 'verifyEmail']);
    });

    Route::prefix('api')->withoutMiddleware(['web'])->middleware(['api'])->group(function () {
        Route::post('productDownload', [Product\BaseProductController::class, 'productDownload']);
        Route::post('productExist', [Product\BaseProductController::class, 'productFileExist']);
        Route::post('updateInstallationStatus', [Product\BaseProductController::class, 'updateStatus']);
//        it receive the reports form the MSG91
        Route::post('msg91/reports/{app_key}/{app_secret}', [Common\Sms\MSG91Controller::class, 'handleReports'])->withoutMiddleware(['admin', 'auth']);
    });

    Route::get('sms/reports', [Common\Sms\MSG91Controller::class, 'msg91Reports']);
    Route::get('getMsgReports', [Common\Sms\MSG91Controller::class, 'getMsg91Reports']);
    Route::get('getMsgFilters', [Common\Sms\MSG91Controller::class, 'getMsgFilters']);
    Route::get('msgThirdPartyUpdate/{thirdPartyId}', [MSG91Controller::class, 'getThirdPartyMsgDetails']);

    //preview image
    Route::get('preview-file', [FileManagerController::class, 'previewFile']);

    Route::get('getPipedriveFields/{group_id}', [PipedriveController::class, 'getLocalFields']);
    Route::get('pipedrive/mapping/{group_id}', [PipedriveController::class, 'getMapFields']);
    Route::post('sync/pipedrive', [PipedriveController::class, 'mappingFields']);
    Route::get('syncing/pipedriveFields', [PipedriveController::class, 'syncFields']);
    Route::post('pipedrive/get-dropdown', [PipedriveController::class, 'getDropdown']);

    Route::middleware(['blockFailedVerifications:verify'])->group(function () {
        Route::post('profile/email/send-otp', [Front\ProfileVerificationController::class, 'sendEmailOtp']);
        Route::post('profile/mobile/send-otp', [Front\ProfileVerificationController::class, 'sendMobileOtp']);
        Route::post('profile/resend-otp', [Front\ProfileVerificationController::class, 'resendOtp']);
    });

    Route::post('profile/email/verify-otp', [Front\ProfileVerificationController::class, 'verifyEmailOtp']);
    Route::post('profile/mobile/verify-otp', [Front\ProfileVerificationController::class, 'verifyMobileOtp']);
});

Route::prefix('open-payment')->withoutMiddleware(['auth', 'web'])->group(function () {
    // Payment Page View
    Route::get('/', function () {
        return view('open-payment');
    })->name('open-payment.page');

    // Create Order
    Route::post('create', [OpenPaymentController::class, 'createOrder'])->name('open-payment.create');

    // Get Order Details (Summary)
    Route::get('order/{id}', [OpenPaymentController::class, 'getOrderDetails'])->name('open-payment.details');

    // Prepare Payment Gateway (AJAX)
    Route::post('prepare', [OpenPaymentController::class, 'preparePayment'])->name('open-payment.prepare');

    // Verify Payments
    Route::post('verify/razorpay', [OpenPaymentController::class, 'verifyRazorpayPayment'])->name('open-payment.verify.razorpay');
    Route::post('verify/stripe', [OpenPaymentController::class, 'verifyStripePayment'])->name('open-payment.verify.stripe');

    // Webhooks (CSRF exempt - handled in VerifyCsrfToken middleware)
    Route::post('webhook/stripe', [OpenPaymentController::class, 'handleStripeWebhook'])->name('open-payment.webhook.stripe');
    Route::post('webhook/razorpay', [OpenPaymentController::class, 'handleRazorpayWebhook'])->name('open-payment.webhook.razorpay');

    // Admin Routes
    Route::get('list', [OpenPaymentController::class, 'listOrders'])->name('open-payment.list');
    Route::get('admin/{id}', [OpenPaymentController::class, 'getOrder'])->name('open-payment.admin.get');

    // Stripe 3D Secure Redirect Handler
    Route::get('stripe/callback', [OpenPaymentController::class, 'handleStripeCallback'])->name('open-payment.stripe.callback');
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

// });

// Updated APIs will need to change after complete API conversion

Route::get('users', [User\ClientController::class, 'getAllUsers']);
Route::delete('users', [User\ClientController::class, 'deleteBulkUsers']);
Route::put('users', [User\ClientController::class, 'userCreate']);
Route::get('user/{id}', [User\ClientController::class, 'getEditUser']);
Route::patch('user/{id}', [User\ClientController::class, 'userUpdate']);
Route::get('user/{id}/summary', [User\ClientController::class, 'getUserSummary']);
Route::get('user/{id}/invoices', [User\ClientController::class, 'getUserInvoices']);
Route::get('user/{id}/payments', [User\ClientController::class, 'getUserPayments']);
Route::get('user/{id}/comments', [User\ClientController::class, 'getUserComments']);
Route::post('user/{id}/comments', [User\ClientController::class, 'storeUserComment']);
Route::put('user/{id}/comments/{commentId}', [User\ClientController::class, 'updateUserComment']);
Route::delete('user/{id}/comments/{commentId}', [User\ClientController::class, 'deleteUserComment']);

Route::get('soft-delete', [User\SoftDeleteController::class, 'softDeletedUsers']);
Route::get('user/restore/{id}', [User\SoftDeleteController::class, 'restoreUser']);
Route::delete('permanent-delete-client', [User\SoftDeleteController::class, 'permanentDeleteUser']);

Route::get('orders', [Order\OrderController::class, 'getOrders']);
Route::delete('orders', [Order\OrderController::class, 'deleteBulkOrders']);
Route::get('order/{id}', [Order\OrderController::class, 'getOrder']);

Route::get('get-installation-details/{orderId}', [Order\OrderController::class, 'getInstallationDetails']);
Route::get('getOrderPayments/{orderId}', [Order\OrderController::class, 'getPaymentByOrderId']);
Route::get('getOrderInvoices/{orderId}', [Order\OrderController::class, 'getOrderInvoices']);
Route::patch('reissue-license', [Order\ExtendedOrderController::class, 'reissueLicense']);
Route::post('edit-update-expiry', [Order\BaseOrderController::class, 'editUpdateExpiry']);
Route::post('edit-license-expiry', [Order\BaseOrderController::class, 'editLicenseExpiry']);
Route::post('edit-support-expiry', [Order\BaseOrderController::class, 'editSupportExpiry']);
Route::post('edit-installation-limit', [Order\BaseOrderController::class, 'editInstallationLimit']);
Route::post('switch-license-mode', [License\LocalizedLicenseController::class, 'chooseLicenseMode']);
Route::get('invoices', [Order\InvoiceController::class, 'getInvoices']);
Route::delete('invoices', [Order\InvoiceController::class, 'deleteBulkInvoices']);
Route::get('invoice/{id}', [Order\InvoiceController::class, 'getInvoice']);
Route::post('get-price', [Product\ProductController::class, 'getPrice']);
Route::get('dependency/{type}', [Common\Dependency\DependencyController::class, 'handle']);

Route::get('pages', [Front\PageController::class, 'getAllPages']);
Route::delete('pages', [Front\PageController::class, 'deleteBulkPages']);
Route::post('page', [Front\PageController::class, 'createPage']);
Route::get('page/{id}', [Front\PageController::class, 'getPage']);
Route::put('page/{id}', [Front\PageController::class, 'updatePage']);
Route::get('demo', [Front\PageController::class, 'getDemoStatus']);
Route::post('save/demo', [Front\PageController::class, 'saveDemoPage']);
Route::get('products', [Product\ProductController::class, 'getAllProducts']);
Route::delete('products', [Product\ProductController::class, 'deleteBulkProducts']);
Route::get('product/{productId}', [Product\ProductController::class, 'getProduct']);
Route::get('product/uploads/{productId}', [Product\ProductController::class, 'getProductUploads']);
Route::delete('product/upload', [Product\ProductController::class, 'deleteBulkProductUpload']);
Route::get('product/upload/{productUploadId}', [Product\ProductController::class, 'getProductUpload']);
Route::patch('product/upload/{productUploadId}', [Product\ProductController::class, 'updateProductUpload']);
Route::put('product/upload/{productId}/', [Product\ProductController::class, 'productUploadCreate']);
Route::put('product', [Product\ProductController::class, 'productCreate']);
Route::patch('product/{productId}', [Product\ProductController::class, 'updateProduct']);
Route::get('plans', [Product\PlanController::class, 'getAllPlans']);
Route::put('plans', [Product\PlanController::class, 'planCreate']);
Route::get('plan/{planId}', [Product\PlanController::class, 'getPlan']);
Route::patch('plan/{planId}', [Product\PlanController::class, 'updatePlan']);
Route::delete('plans', [Product\PlanController::class, 'deleteBulkPlans']);
Route::get('promotions', [Payment\PromotionController::class, 'getAllPromotions']);
Route::get('promotion/{promotionId}', [Payment\PromotionController::class, 'getPromotion']);
Route::get('getPromotionCode', [Payment\PromotionController::class, 'getCode']);
Route::patch('updatePromotion/{promotionId}', [Payment\PromotionController::class, 'updatePromotionCode']);
Route::put('promotionCreate', [Payment\PromotionController::class, 'promotionCodeCreate']);
Route::delete('promotions', [Payment\PromotionController::class, 'deleteBulkPromotions']);
Route::get('groups', [Product\GroupController::class, 'getProductGroups']);
Route::get('group/{group_id}', [Product\GroupController::class, 'getGroup']);
Route::patch('group/{group_id}', [Product\GroupController::class, 'updateGroup']);
Route::put('group', [Product\GroupController::class, 'groupCreate']);
Route::delete('group', [Product\GroupController::class, 'deleteBulkGroups']);
Route::get('reports', [ReportController::class, 'getAllReports']);
Route::get('download-exported-file/{id}', [User\ClientController::class, 'downloadExportedFile'])->name('download.exported.file');
Route::delete('reports', [ReportController::class, 'deleteBulkReports']);
Route::get('reports/setting', [ReportController::class, 'getReportsSettings']);
Route::patch('reports/setting', [ReportController::class, 'updateReportsSettings']);

Route::get('dashboard', [DashboardController::class, 'dashboard']);

Route::get('module-settings', [Common\SettingsController::class, 'getModuleSettings']);

// Admin Vue Panel — guard at the server level so unauthenticated users never
// receive the blade/JS bundle and see a flash of the admin UI.
Route::get('/admin/{any?}', function () {
    if (! auth()->check()) {
        return redirect(url('/login'));
    }

    return view('admin');
})->where('any', '.*');
