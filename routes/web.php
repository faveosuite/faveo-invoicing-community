<?php

// ============================================================
// SECTION 1: USE IMPORTS
// ============================================================

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
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Route organization:
|
|  SECTION 2  — External / Public APIs  (no auth, outside installAgora)
|  SECTION 3  — Webhooks                (CSRF-exempt external POST)
|  SECTION 4  — installAgora group
|    4a  Auth APIs          (login, register, 2FA, password reset)
|    4b  Shared APIs        (used by both Client & Admin panels)
|    4c  Client Panel APIs  (pages/client/* Vue pages)
|    4d  Admin Panel APIs   (pages/admin/* Vue pages)
|    4e  Internal / System  (api prefix, product download callbacks)
|  SECTION 5  — SPA shell routes (must stay last)
|
*/

// ============================================================
// SECTION 2: EXTERNAL / PUBLIC APIs
// Callers are external systems (Faveo helpdesk, licensee servers, etc.)
// No auth required. These intentionally live OUTSIDE installAgora so that
// license/serial endpoints respond even during maintenance or fresh install.
// ============================================================

// --- Faveo product / license callbacks ---
Route::post('serial', [HomeController::class, 'serial']);
Route::post('v2/serial', [HomeController::class, 'serialV2']);
Route::get('download/faveo', [HomeController::class, 'downloadForFaveo']);
Route::get('version/latest', [HomeController::class, 'latestVersion']);
Route::post('update-latest-version', [HomeController::class, 'updateLatestVersion']);
Route::post('v1/checkUpdatesExpiry', [HomeController::class, 'checkUpdatesExpiry']);
Route::post('update/lic-code', [HomeController::class, 'updateLicenseCode']);
Route::get('new-version-available', [HomeController::class, 'isNewVersionAvailable']);
Route::post('renewurl', [HomeController::class, 'renewurl']);

// --- Tenancy / third-party token verify ---
Route::get('verify/third-party-token', [Tenancy\TenantController::class, 'verifyThirdPartyToken']);

// --- Billing info / release info (queried by external dashboards) ---
Route::get('api/billingInfo', [HomeController::class, 'getDetailedBillingInfo']);
Route::get('api/pluginInfo', [HomeController::class, 'getDetailsForAClient']);
Route::get('api/billingRelease', [HomeController::class, 'getProductRelease']);

// --- Agent / product download (external, signed URLs) ---
Route::get('/api/download/agents', [Product\BaseProductController::class, 'agentProductDownload']);
Route::get('/product/detail', [Product\BaseProductController::class, 'getProductUsingLicenseCode']);

// ============================================================
// SECTION 3: WEBHOOKS
// CSRF-exempt external POST — WhatsApp, Stripe, Razorpay.
// These are registered early so VerifyCsrfToken can exclude them.
// ============================================================

Route::match(['get', 'post'], 'faveo-whatsapp',
    [\App\Http\Controllers\WhatsappController::class, 'whatsappWebhook']);

// ============================================================
// SECTION 4: installAgora MIDDLEWARE GROUP
// All application routes live here. The installAgora middleware
// ensures the app is fully set up before any feature is accessible.
// ============================================================

Route::middleware('installAgora')->group(function () {
    // ==========================================================
    // 4a. AUTH APIs
    // Guest-accessible within installAgora (login, register, 2FA,
    // password reset, OTP, social auth). No `auth` middleware here.
    // ==========================================================

    // CSRF token refresh — called by Vue SPA before form submission
    Route::get('refresh-csrf', function () {
        return response()->json(['token' => csrf_token()], 200);
    });

    // SPA login-form config (honeypot metadata, captcha settings, etc.)
    Route::get('honeypot', fn () => successResponse('honeypot', honeypotData()));
    Route::get('auth/login-config', [Auth\LoginController::class,        'loginConfig']);
    Route::get('auth/forgot-config', [Auth\ForgotPasswordController::class, 'showLinkRequestForm']);
    Route::get('auth/reset-validate/{token}', [Auth\ResetPasswordController::class, 'showResetForm']);
    Route::get('auth/verify-config', [Auth\AuthController::class,          'verifyConfig']);

    Route::post('login', [Auth\LoginController::class, 'login'])
        ->name('login')
        ->middleware(['blockFailedVerifications:login']);

    Route::post('auth/register', [Auth\RegisterController::class, 'postRegister'])->name('auth/register');
    Route::get('auth/logout', [Auth\LoginController::class,    'logout'])->name('logout');

    // Password reset (GET kept for `password.reset` name used by reset emails)
    Route::get('password/reset/{token}', [Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/email', [Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::post('password/reset', [Auth\ResetPasswordController::class, 'reset'])->name('password.update');

    // 2FA — login-time validation
    Route::middleware(['blockFailedVerifications:2fa', 'session.timeout:10,2fa'])->group(function () {
        Route::get('2fa/session-check', [Google2FAController::class, 'verifySession'])->name('2fa.session.check');
        Route::get('recovery-code', [Google2FAController::class, 'showRecoveryCode']);
        Route::get('verify-2fa', [Google2FAController::class, 'verify2fa']);
        Route::get('auth/2fa-check', [Google2FAController::class, 'verify2fa']);
        Route::post('2fa/loginValidate', [Google2FAController::class, 'postLoginValidateToken'])->name('2fa/loginValidate');
        Route::post('verify-recovery-code', [Google2FAController::class, 'verifyRecoveryCode'])->name('verify-recovery-code');
    });

    // 2FA — setup (requires password confirmation)
    Route::middleware(['password.confirm'])->group(function () {
        Route::get('show/verify-password', [Google2FAController::class, 'showVerifyPassword']);
        Route::post('/2fa/enable', [Google2FAController::class, 'enableTwoFactor']);
        Route::post('2fa-recovery-code', [Google2FAController::class, 'generateRecoveryCode']);
        Route::post('2fa/setupValidate', [Google2FAController::class, 'postSetupValidateToken']);
    });
    Route::post('2fa/disable/{userId?}', [Google2FAController::class, 'disableTwoFactor']);
    Route::post('verify-password', [Google2FAController::class, 'verifyPassword']);
    Route::get('get-recovery-code', [Google2FAController::class, 'getRecoveryCode']);
    Route::post('verify-2fa-admin', [Google2FAController::class, 'postSetupValidateToken'])->name('verify.2fa.admin');

    // OTP / email verification
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

    // Social / OAuth logins
    Route::get('/auth/redirect/{provider}', [Auth\LoginController::class, 'redirectToGithub']);
    Route::get('/auth/callback/{provider}', [Auth\LoginController::class, 'handler']);

    // Auth state helpers
    Route::get('get-loginstate/{state}', [Auth\AuthController::class, 'getState']);
    Route::get('get-countries', [Auth\AuthController::class, 'getCountries']);

    // API token login (used by third-party integrations)
    Route::post('api/login', [Auth\LoginController::class, 'postLoginAndGetToken']);

    // SPA auth check — both SPAs call GET /api/user to hydrate auth state.
    // Returns JSON (never HTML) so auth store works correctly.
    Route::get('api/user', function () {
        return auth()->check()
            ? successResponse('user', auth()->user())
            : response()->json(['message' => 'Unauthenticated.'], 401);
    });

    // ==========================================================
    // 4b. SHARED APIs
    // Used by BOTH the Client Panel and Admin Panel.
    // ==========================================================

    // Language / locale
    Route::get('language/control', [LanguageController::class, 'fetchLangDropdownUsers']);
    Route::get('js/lang', [LanguageController::class, 'getLanguageFile'])->name('assets.lang');

    // Dependency lookups (countries, states, timezones, products, plans, etc.)
    // Used heavily by both panels for dropdowns / form options.
    Route::get('dependency/{type}', [Common\Dependency\DependencyController::class, 'handle']);

    // PDF invoice download (client views their invoice; admin generates them)
    Route::get('pdf', [Order\InvoiceController::class, 'pdf']);

    // Invoice by ID — both panels fetch a single invoice
    Route::get('invoice/{id}', [Order\InvoiceController::class, 'getInvoice']);

    // Order reissue — called from client OrderShow AND admin OrderShow
    Route::patch('reissue-license', [Order\ExtendedOrderController::class, 'reissueLicense']);

    // 2FA management (shared — both panels have a 2FA settings page)
    // (Already registered in 4a above under password.confirm middleware)

    // Verify password (shared profile guard — both panels)
    // (Already registered in 4a above)

    // WhatsApp shared actions — client can save WABA details; admin manages integration
    Route::post('save-waba-id', [\App\Http\Controllers\WhatsappController::class, 'saveWabaId']);
    Route::post('url-save', [\App\Http\Controllers\WhatsappController::class, 'urlSave']);
    Route::post('webhook-url-edit', [\App\Http\Controllers\WhatsappController::class, 'webhookUrlEdit']);
    Route::post('whatsapp-deregister', [\App\Http\Controllers\WhatsappController::class, 'deregister']);

    // Contact us (client contact page + admin test)
    Route::post('contact-us', [Front\PageController::class, 'postContactUs']);

    // Domain management (client can change domain; admin manages cloud)
    Route::post('change/domain', [Tenancy\CloudExtraActivities::class, 'changeDomain']);

    // ==========================================================
    // 4c. CLIENT PANEL APIs
    // Consumed primarily by pages/client/* Vue pages.
    // ==========================================================

    // --- Dashboard ---
    Route::get('client-dashboard-details', [Front\ClientController::class, 'clientDetails']);

    // --- Store / Public pages (withoutMiddleware so guests can browse) ---
    Route::get('pricing/data', [HomeController::class,     'getPricingData']);
    Route::get('group/data', [HomeController::class,     'getGroupDatails']);
    Route::get('store/groups', [Front\StoreController::class, 'getGroups'])->name('store.groups');
    Route::get('store/{groupId}/products', [Front\StoreController::class, 'getProducts'])
        ->where('groupId', '[0-9]+')
        ->name('store.group.products');
    Route::get('store/cloud-products', [FreeTrailController::class, 'getCloudProducts'])->name('store.cloud.products');
    Route::post('free-trial/start', [FreeTrailController::class, 'startTrial'])->name('free-trial.start');
    Route::post('available-groups', [Product\GroupController::class, 'getAvailableGroups'])
        ->withoutMiddleware(['auth', 'admin']);
    Route::post('newsletter/subscribe', [Front\NewsletterController::class, 'subscribe'])
        ->middleware('recaptcha:newsletter');
    Route::post('demo-request', [Front\PageController::class, 'postDemoReq'])
        ->withoutMiddleware(['auth']);
    Route::get('404', function () {
        return view('errors.404');
    })->name('error404');

    // Published pages / contact info / demo status (public, no auth)
    Route::get('published-pages', [Front\PageController::class, 'publishedPages']);
    Route::get('page-content/{slug}', [Front\PageController::class, 'pageBySlug']);
    Route::get('contact-us-info', [Front\PageController::class, 'contactUsInfo']);
    Route::get('demo', [Front\PageController::class, 'getDemoStatus']);

    // Open Payment (pay/*) — public payment page, no auth required
    Route::prefix('pay')->withoutMiddleware(['auth', 'web'])->group(function () {
        Route::get('/', fn () => view('client'))->name('open-payment.page');
        Route::get('config', [OpenPaymentController::class, 'getConfig'])->name('open-payment.config');
        Route::get('detect-country', [OpenPaymentController::class, 'detectCountry'])->name('open-payment.detect-country');
        Route::get('calculate', [OpenPaymentController::class, 'calculate'])->name('open-payment.calculate');
        Route::post('create', [OpenPaymentController::class, 'createOrder'])
            ->name('open-payment.create')
            ->middleware(['throttle:10,1', 'recaptcha:open_payment']);
        Route::get('order/{id}', [OpenPaymentController::class, 'getOrderDetails'])->name('open-payment.details');
        Route::post('prepare', [OpenPaymentController::class, 'preparePayment'])->name('open-payment.prepare');
        Route::post('stripe/card-session', [OpenPaymentController::class, 'stripeCardSession'])
            ->name('open-payment.stripe.card-session')
            ->middleware('throttle:20,1');
        Route::post('verify/razorpay', [OpenPaymentController::class, 'verifyRazorpayPayment'])
            ->name('open-payment.verify.razorpay')
            ->middleware('throttle:20,1');
        Route::post('verify/stripe', [OpenPaymentController::class, 'verifyStripePayment'])
            ->name('open-payment.verify.stripe')
            ->middleware('throttle:20,1');
        Route::post('webhook/stripe', [Front\PaymentController::class, 'stripeWebhook'])->name('webhook.stripe');
        Route::post('webhook/razorpay', [Front\PaymentController::class, 'razorpayWebhook'])->name('webhook.razorpay');
        Route::get('stripe/callback', [OpenPaymentController::class, 'handleStripeCallback'])->name('open-payment.stripe.callback');
        // Admin-only open-payment views
        Route::get('list', [OpenPaymentController::class, 'listOrders'])->name('open-payment.list');
        Route::get('admin/{id}', [OpenPaymentController::class, 'getOrder'])->name('open-payment.admin.get');
    });

    // DB-backed shopping cart (Vue SPA)
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [Front\Cart\CartApiController::class, 'show'])->name('show');
        Route::post('items', [Front\Cart\CartApiController::class, 'addItem'])->name('items.add');
        Route::put('items/{item}', [Front\Cart\CartApiController::class, 'updateItem'])->name('items.update');
        Route::delete('items/{item}', [Front\Cart\CartApiController::class, 'removeItem'])->name('items.remove');
        Route::delete('/', [Front\Cart\CartApiController::class, 'clear'])->name('clear');
        Route::middleware('auth')->group(function () {
            Route::post('coupon', [Front\Cart\CartApiController::class, 'applyCoupon'])->name('coupon.apply');
            Route::delete('coupon', [Front\Cart\CartApiController::class, 'removeCoupon'])->name('coupon.remove');
            Route::get('checkout', [Front\Cart\CartApiController::class, 'checkout'])->name('checkout');
            Route::post('place-order', [Front\Cart\CartApiController::class, 'placeOrder'])->name('place-order');
        });
    });

    // Legacy cart endpoints (inside installAgora, session-backed)
    Route::get('cart-access', [Front\BaseClientController::class, 'cartAccess']);
    Route::post('cart/remove', [Front\CartController::class,      'cartRemove']);
    Route::get('pricing', [Front\CartController::class,      'cart'])->name('pricing');
    Route::get('group/{templateid}/{group}/', [Front\PageController::class, 'pageTemplates']);
    Route::post('update-agent-qty', [Front\CartController::class,      'updateAgentQty']);
    Route::post('update-qty', [Front\CartController::class,      'updateProductQty']);
    Route::post('reduce-product-qty', [Front\CartController::class,      'reduceProductQty']);
    Route::post('reduce-agent-qty', [Front\CartController::class,      'reduceAgentQty']);
    Route::post('cart/clear', [Front\CartController::class,      'clearCart']);
    Route::get('show/cart', [Front\CartController::class,      'showCart']);
    Route::get('checkout', [Front\CheckoutController::class,  'checkoutForm']);
    Route::match(['post', 'patch'], 'checkout-and-pay', [Front\CheckoutController::class, 'postCheckout']);
    Route::get('checkout-and-pay', fn () => redirect('show/cart'));
    Route::post('pricing/update', [Front\CartController::class,      'addCouponUpdate']);
    Route::post('remove-coupon', [Front\CartController::class,      'removeCoupon']);
    Route::post('remove-product', fn () => abort(404));

    // --- Invoices (client) ---
    Route::get('my-invoices', fn () => view('client'))->name('my-invoices');
    Route::get('get-my-invoices', [Front\ClientController::class, 'getInvoices'])->name('get-my-invoices');
    Route::delete('invoices/delete/{id}', [Front\ClientController::class, 'invoiceDelete']);
    Route::get('paynow/{id}', [Front\CheckoutController::class, 'payNow'])->middleware(['auth']);
    Route::post('store-basic-details', [Auth\LoginController::class, 'storeBasicDetails'])->name('store-basic-details');

    // --- Orders (client) ---
    Route::get('get-my-orders', [Front\ClientController::class, 'getClientOrder'])->name('get-my-orders');
    Route::get('renew-popup-details/{productid}', [Front\ClientController::class, 'renewPopupVue']);
    Route::get('get-cloud-settings/{orderId}', [Front\ClientController::class, 'getCloudSettings']);
    Route::get('get-my-invoices/{orderid}/{userid}/{admin?}', [Front\ClientController::class, 'getInvoicesByOrderId']);
    Route::get('get-my-payment-client/{orderid}/{userid}', [Front\ClientController::class, 'getPaymentByOrderIdClient'])->name('get-my-payment-client');
    Route::get('get-my-installations/{orderid}', [Front\ClientController::class, 'getOrderInstallations']);
    Route::get('get-versions/{orderid}', [Front\ClientController::class, 'getVersionList'])->name('get-versions');

    // --- Renew (client) ---
    Route::get('renew/{id}/{agents?}', [Order\RenewController::class, 'renewForm']);
    Route::post('renew/{id}', [Order\RenewController::class, 'renew']);
    Route::get('get-renew-cost', [Order\RenewController::class, 'getCost']);
    Route::post('client/renew/{id}', [Order\RenewController::class, 'renewByClient']);
    Route::get('autopaynow/{id}', [Front\ClientController::class, 'autoRenewbyid']);

    // --- Payments (client) ---
    Route::post('payment/{invoice}', [RazorpayController::class,    'payment'])->name('payment');
    Route::get('confirm/payment', [RazorpayController::class,    'afterPayment']);
    Route::post('stripeUpdatePayment/confirm', [Front\ClientController::class, 'stripeUpdatePayment']);
    Route::middleware('auth')->group(function () {
        Route::get('invoice/{invoice}/pay-init', [Front\PaymentController::class, 'payInit'])->name('invoice.pay.init');
        Route::get('invoice/{invoice}/pay-success', [Front\PaymentController::class, 'paySuccess'])->name('invoice.pay.success');
        Route::post('invoice/{invoice}/stripe/session', [Front\PaymentController::class, 'stripeSession'])->name('invoice.pay.stripe.session');
        Route::post('invoice/{invoice}/stripe/confirm', [Front\PaymentController::class, 'stripeConfirm'])->name('invoice.pay.stripe.confirm');
        Route::post('invoice/{invoice}/razorpay/order', [Front\PaymentController::class, 'razorpayOrder'])->name('invoice.pay.razorpay.order');
    });

    // --- Auto-renewal (client) ---
    Route::prefix('auto-renewal/{order}')->group(function () {
        Route::post('stripe/session', [Front\AutoRenewalController::class, 'stripeSession']);
        Route::post('stripe/confirm', [Front\AutoRenewalController::class, 'stripeConfirm']);
        Route::post('razorpay/order', [Front\AutoRenewalController::class, 'razorpayOrder']);
        Route::post('razorpay/confirm', [Front\AutoRenewalController::class, 'razorpayConfirm']);
        Route::post('disable', [Front\AutoRenewalController::class, 'disable']);
    });

    // --- Profile (client) ---
    Route::get('get-my-profile', [Front\ClientController::class, 'profile'])->name('get-my-profile');
    Route::patch('my-profile', [Front\ClientController::class, 'postProfile']);
    Route::patch('my-password', [Front\ClientController::class, 'postPassword']);

    // Profile OTP verification
    Route::middleware(['blockFailedVerifications:verify'])->group(function () {
        Route::post('profile/email/send-otp', [Front\ProfileVerificationController::class, 'sendEmailOtp']);
        Route::post('profile/mobile/send-otp', [Front\ProfileVerificationController::class, 'sendMobileOtp']);
        Route::post('profile/resend-otp', [Front\ProfileVerificationController::class, 'resendOtp']);
    });
    Route::post('profile/email/verify-otp', [Front\ProfileVerificationController::class, 'verifyEmailOtp']);
    Route::post('profile/mobile/verify-otp', [Front\ProfileVerificationController::class, 'verifyMobileOtp']);

    // --- Cloud (client self-service) ---
    Route::post('trial-cloud-products', [Tenancy\CloudExtraActivities::class, 'trialCloudProducts']);
    Route::post('create/tenant/purchase', [Tenancy\CloudExtraActivities::class, 'storeTenantTillPurchase']);
    Route::post('get-cloud-upgrade-cost', [Tenancy\CloudExtraActivities::class, 'getUpgradeCost']);
    Route::post('changeAgents', [Tenancy\CloudExtraActivities::class, 'agentAlteration']);
    Route::post('upgradeDowngradeCloud', [Tenancy\CloudExtraActivities::class, 'upgradeDowngradeCloud']);
    Route::get('format-currency', [Tenancy\CloudExtraActivities::class, 'formatCurrency']);
    Route::get('processFormat', [Tenancy\CloudExtraActivities::class, 'processFormat']);
    Route::post('get-agent-inc-dec-cost', [Tenancy\CloudExtraActivities::class, 'getThePaymentCalculationDisplay']);
    Route::get('api/domain', [Tenancy\CloudExtraActivities::class, 'domainCloudAutofill']);
    Route::post('api/takeCloudDomain', [Tenancy\CloudExtraActivities::class, 'orderDomainCloudAutofill']); // Not in use

    // --- WhatsApp (client webhook info) ---
    Route::get('get-webhook-url', [\App\Http\Controllers\WhatsappController::class, 'getWebhookUrl']);
    Route::get('whatsapp-client-numbers/{orderid}', [\App\Http\Controllers\WhatsappController::class, 'whatsappClientNumbers']);

    // --- Widgets / footer (client) ---
    Route::get('footer1', [Front\WidgetController::class, 'footer1'])
        ->name('footer1')
        ->withoutMiddleware(['auth', 'admin']);

    // ==========================================================
    // 4d. ADMIN PANEL APIs
    // Consumed primarily by pages/admin/* Vue pages.
    // ==========================================================

    // --------------------------------------------------------
    // Users & Clients
    // --------------------------------------------------------

    // RESTful user endpoints
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

    // Soft-delete / restore
    Route::get('soft-delete', [User\SoftDeleteController::class, 'softDeletedUsers'])->name('soft-delete');
    Route::get('user/restore/{id}', [User\SoftDeleteController::class, 'restoreUser']);
    Route::delete('permanent-delete-client', [User\SoftDeleteController::class, 'permanentDeleteUser']);

    // Legacy client detail endpoints (admin panels still reference these)
    Route::get('getClientDetail/{id}', [User\ClientController::class, 'getClientDetail']);
    Route::delete('clients-delete', [User\ClientController::class, 'destroy']);
    Route::get('get-users', [User\ClientController::class, 'getUsers']);
    Route::post('/save-columns', [User\ClientController::class, 'saveColumns'])->name('save-columns');
    Route::get('/get-columns', [User\ClientController::class, 'getColumns'])->name('get-columns');
    Route::get('export-users', [User\ClientController::class, 'exportUsers'])->name('export-users');

    // Admin profile
    Route::get('profile', [User\ProfileController::class, 'profile']);
    Route::patch('profile', [User\ProfileController::class, 'updateProfile']);
    Route::patch('password', [User\ProfileController::class, 'updatePassword']);
    Route::get('profile/countries', [User\ProfileController::class, 'getCountries']);
    Route::get('profile/states/{countryCode}', [User\ProfileController::class, 'getStatesByCountry']);

    Route::get('get-code', [WelcomeController::class, 'getCode']);
    Route::get('get-currency', [WelcomeController::class, 'getCurrency'])->middleware('admin'); // Not in use
    Route::get('get-country', [WelcomeController::class, 'getCountry'])->middleware('admin');

    Route::delete('comment-delete', [User\CommentController::class, 'destroy'])->name('comment-delete');

    // --------------------------------------------------------
    // Products, Uploads, Plans, Groups, Categories
    // --------------------------------------------------------

    // RESTful product endpoints
    Route::get('products', [Product\ProductController::class, 'getAllProducts']);
    Route::delete('products', [Product\ProductController::class, 'deleteBulkProducts']);
    Route::put('product', [Product\ProductController::class, 'productCreate']);
    Route::get('product/{productId}', [Product\ProductController::class, 'getProduct']);
    Route::patch('product/{productId}', [Product\ProductController::class, 'updateProduct']);
    Route::get('product/{productId}/plugins', [Product\ProductPluginController::class, 'index']);
    Route::post('product/{productId}/plugins', [Product\ProductPluginController::class, 'sync']);
    Route::get('product/uploads/{productId}', [Product\ProductController::class, 'getProductUploads']);
    Route::get('product/upload/{productUploadId}', [Product\ProductController::class, 'getProductUpload']);
    Route::patch('product/upload/{productUploadId}', [Product\ProductController::class, 'updateProductUpload']);
    Route::put('product/upload/{productId}/', [Product\ProductController::class, 'productUploadCreate']);
    Route::delete('product/upload', [Product\ProductController::class, 'deleteBulkProductUpload']);

    // Legacy product action endpoints
    Route::get('get-subscription/{id}', [Product\ProductController::class, 'getSubscriptionCheck']);
    Route::delete('products-delete', [Product\ProductController::class, 'destroy'])->name('products-delete');
    Route::delete('uploads-delete', [Product\ProductController::class, 'fileDestroy'])->name('uploads-delete');
    Route::post('get-price', [Product\ProductController::class, 'getPrice']);
    Route::post('upload/save', [Product\ProductController::class, 'save'])->name('upload/save');
    Route::post('chunkupload', [Product\ProductController::class, 'uploadFile']);
    Route::patch('upload/{id}', [Product\ProductController::class, 'uploadUpdate']);
    Route::post('upload-image', [Product\ProductController::class, 'uploadImage'])->name('upload-image');
    Route::get('get-group-url', [Product\GroupController::class,   'generateGroupUrl']);

    // RESTful plan endpoints
    Route::get('plans', [Product\PlanController::class, 'getAllPlans']);
    Route::put('plans', [Product\PlanController::class, 'planCreate']);
    Route::get('plan/{planId}', [Product\PlanController::class, 'getPlan']);
    Route::patch('plan/{planId}', [Product\PlanController::class, 'updatePlan']);
    Route::delete('plans', [Product\PlanController::class, 'deleteBulkPlans']);
    Route::delete('plans-delete', [Product\PlanController::class, 'destroy'])->name('plans-delete');
    Route::get('get-period', [Product\PlanController::class, 'checkSubscription'])->name('get-period');

    // RESTful group endpoints
    Route::get('groups', [Product\GroupController::class, 'getProductGroups']);
    Route::get('group/{group_id}', [Product\GroupController::class, 'getGroup']);
    Route::patch('group/{group_id}', [Product\GroupController::class, 'updateGroup']);
    Route::put('group', [Product\GroupController::class, 'groupCreate']);
    Route::delete('group', [Product\GroupController::class, 'deleteBulkGroups']);
    Route::delete('groups-delete', [Product\GroupController::class, 'destroy'])->name('groups-delete');

    // Categories
    Route::delete('category-delete', [Product\CategoryController::class, 'destroy'])->name('category-delete');

    // --------------------------------------------------------
    // Orders
    // --------------------------------------------------------

    // RESTful order endpoints
    Route::get('orders', [Order\OrderController::class, 'getOrders']);
    Route::delete('orders', [Order\OrderController::class, 'deleteBulkOrders']);
    Route::get('order/{id}', [Order\OrderController::class, 'getOrder']);
    Route::get('getOrderPayments/{orderId}', [Order\OrderController::class, 'getPaymentByOrderId']);
    Route::get('getOrderInvoices/{orderId}', [Order\OrderController::class, 'getOrderInvoices']);

    // Legacy order endpoints
    Route::get('get-orders', [Order\OrderController::class, 'getOrders'])->name('get-orders');
    Route::post('edit-update-expiry', [Order\BaseOrderController::class, 'editUpdateExpiry']);
    Route::post('edit-license-expiry', [Order\BaseOrderController::class, 'editLicenseExpiry']);
    Route::post('edit-support-expiry', [Order\BaseOrderController::class, 'editSupportExpiry']);
    Route::get('get-installation-details/{orderId}', [Order\OrderController::class, 'getInstallationDetails']);
    Route::get('export-orders', [Order\OrderController::class, 'exportOrders'])->name('export-orders');
    Route::get('orders/license/{order_number}', function ($orderNumber) {
        return redirect('/orders/'.\App\Model\Order\Order::where('number', $orderNumber)->value('id'));
    });

    Route::post('switch-license-mode', [License\LocalizedLicenseController::class, 'chooseLicenseMode']);

    // --------------------------------------------------------
    // Invoices & Payments
    // --------------------------------------------------------

    // RESTful invoice endpoints
    Route::get('invoices', [Order\InvoiceController::class, 'getInvoices']);
    Route::delete('invoices', [Order\InvoiceController::class, 'deleteBulkInvoices']);
    Route::delete('payments', [Order\InvoiceController::class, 'deleteBulkPayments']);

    // Legacy invoice endpoints
    Route::post('invoice/edit/{id}', [Order\InvoiceController::class, 'postEdit']);
    Route::get('get-invoices', [Order\InvoiceController::class, 'getInvoices'])->name('get-invoices');
    Route::post('generate/invoice/{user_id?}', [Order\InvoiceController::class, 'invoiceGenerateByForm']);
    Route::post('change-invoiceTotal', [Order\InvoiceController::class, 'invoiceTotalChange'])->name('change-invoiceTotal');
    Route::get('export-invoices', [Order\InvoiceController::class, 'exportInvoices'])->name('export-invoices');

    // Admin payment forms (legacy blade views — still referenced from admin OrderShow)
    Route::get('newPayment/receive', [Order\InvoiceController::class, 'newPayment']);
    Route::post('newPayment/receive/{clientid}', [Order\InvoiceController::class, 'postNewPayment']);
    Route::post('payment/receive/{id}', [Order\InvoiceController::class, 'postPayment']);
    Route::post('newMultiplePayment/receive/{clientid}', [Order\InvoiceController::class, 'postNewMultiplePayment']);
    Route::post('newMultiplePayment/update/{clientid}', [Order\InvoiceController::class, 'updateNewMultiplePayment']);

    // --------------------------------------------------------
    // Pages & Widgets (Admin CMS)
    // --------------------------------------------------------

    // RESTful page endpoints
    Route::get('pages', [Front\PageController::class, 'getAllPages']);
    Route::delete('pages', [Front\PageController::class, 'deleteBulkPages']);
    Route::post('page', [Front\PageController::class, 'createPage']);
    Route::get('page/{id}', [Front\PageController::class, 'getPage']);
    Route::put('page/{id}', [Front\PageController::class, 'updatePage']);

    // Legacy page admin endpoints
    Route::delete('pages-delete', [Front\PageController::class, 'destroy'])->name('pages-delete');
    Route::post('save/demo', [Front\PageController::class, 'saveDemoPage']);

    // Widget management
    Route::prefix('widgets')->group(function () {
        Route::get('list', [Front\WidgetController::class, 'getWidgetList']);
        Route::get('show/{id}', [Front\WidgetController::class, 'getWidget']);
        Route::put('update/{id}', [Front\WidgetController::class, 'updateWidget']);
        Route::delete('delete', [Front\WidgetController::class, 'deleteWidget']);
        Route::post('create', [Front\WidgetController::class, 'createWidget']);
    });

    // --------------------------------------------------------
    // Promotions & Coupons
    // --------------------------------------------------------

    // RESTful promotion endpoints
    Route::get('promotions', [Payment\PromotionController::class, 'getAllPromotions']);
    Route::get('promotion/{promotionId}', [Payment\PromotionController::class, 'getPromotion']);
    Route::get('getPromotionCode', [Payment\PromotionController::class, 'getCode']);
    Route::patch('updatePromotion/{promotionId}', [Payment\PromotionController::class, 'updatePromotionCode']);
    Route::put('promotionCreate', [Payment\PromotionController::class, 'promotionCodeCreate']);
    Route::delete('promotions', [Payment\PromotionController::class, 'deleteBulkPromotions']);

    // Legacy promotion endpoints
    Route::get('get-promotions', [Payment\PromotionController::class, 'getPromotion'])->name('get-promotions');
    Route::delete('promotions-delete', [Payment\PromotionController::class, 'destroy'])->name('promotions-delete');

    // --------------------------------------------------------
    // Tax & Currency
    // --------------------------------------------------------

    Route::prefix('currency')->group(function () {
        Route::get('list', [Payment\CurrencyController::class, 'getCurrencyList']);
        Route::post('update-currency', [Payment\CurrencyController::class, 'updatecurrency']);
        Route::post('dashboard-currency/{id}', [Payment\CurrencyController::class, 'setDashboardCurrency']);
        Route::post('default-currency/{id}', [Payment\CurrencyController::class, 'setDefaultCurrency']);
    });

    Route::get('tax-options', [Payment\TaxController::class, 'getTaxOptionsApi']);
    Route::post('taxes/option', [Payment\TaxController::class, 'saveTaxOptionSetting'])->name('taxes/option');
    Route::get('tax-tables', [Payment\TaxController::class, 'getTax']);
    Route::get('tax/edit/{id}', [Payment\TaxController::class, 'editTaxApi']);
    Route::put('tax/{id}', [Payment\TaxController::class, 'updateTaxApi']);
    Route::post('create/tax-class', [Payment\TaxController::class, 'saveTaxClassSettingApi']);
    Route::delete('tax/delete', [Payment\TaxController::class, 'deleteTax']);
    Route::get('get-state/{state}', [Payment\TaxController::class, 'getState']);

    // --------------------------------------------------------
    // License Types & Permissions
    // --------------------------------------------------------

    Route::get('get-license-type', [License\LicenseSettingsController::class, 'getLicenseTypes'])->name('get-license-type');
    Route::get('get-license-type/{id}', [License\LicenseSettingsController::class, 'getLicenseTypeById']);
    Route::post('create-license-type', [License\LicenseSettingsController::class, 'createLicense']);
    Route::put('update-license-type/{id}', [License\LicenseSettingsController::class, 'updateLicense']);
    Route::delete('delete-license-type', [License\LicenseSettingsController::class, 'deleteLicense'])->name('license-type-delete');

    Route::get('get-license-permission', [License\LicensePermissionsController::class, 'getPermissions'])->name('get-license-permission');
    Route::delete('add-permission', [License\LicensePermissionsController::class, 'addPermission'])->name('add-permission');
    Route::get('tick-permission', [License\LicensePermissionsController::class, 'tickPermission'])->name('tick-permission');

    // --------------------------------------------------------
    // Email Templates
    // --------------------------------------------------------

    Route::prefix('template')->group(function () {
        Route::get('list', [Common\TemplateController::class, 'getTemplates']);
        Route::get('edit/{id}', [Common\TemplateController::class, 'showTemplate']);
        Route::put('update/{id}', [Common\TemplateController::class, 'updateTemplate']);
    });
    Route::post('store_toggle_state', [Common\TemplateController::class, 'toggle'])
        ->withoutMiddleware(['auth', 'admin']);
    Route::get('/email-log/body/{id}', [Common\SettingsController::class, 'getBody'])->name('email-log.body');

    // --------------------------------------------------------
    // Settings (System, Email, Cron, PDF, File Storage, etc.)
    // --------------------------------------------------------

    // System settings
    Route::get('settings/system-data', [Common\SettingsController::class, 'getSystemSettingsData']);
    Route::get('settings/index-data', [Common\SettingsController::class, 'getSettingsIndexData']);
    Route::patch('settings/system-data', [Common\SettingsController::class, 'updateSystemSettingsData']);
    Route::patch('settings/datetime-data', [Common\SettingsController::class, 'updateDateTimeSettingsData']);
    Route::get('settings/template', [Common\SettingsController::class, 'settingsTemplate']);
    Route::patch('settings/template', [Common\SettingsController::class, 'postSettingsTemplate']);
    Route::get('settings/error', [Common\SettingsController::class, 'getErrorSettings']);
    Route::patch('settings/error', [Common\SettingsController::class, 'postSettingsError']);

    // Email settings
    Route::get('settings/email', [Common\EmailSettingsController::class, 'settingsEmail'])->middleware('auth');
    Route::patch('settings/email', [Common\EmailSettingsController::class, 'postSettingsEmail']);
    Route::post('emailData', [Common\SettingsController::class, 'emailData']);
    Route::post('emailCheckboxData', [Common\SettingsController::class, 'emailCheckboxData']);
    Route::post('email-settings-save', [Common\SettingsController::class, 'emailSettingsSave']);
    Route::get('settings/email-validation', [Common\SettingsController::class, 'getEmailValidationSettings']);
    Route::get('settings/email-validation-logs', [Common\SettingsController::class, 'listEmailValidationLogs']);
    Route::get('get-email-validation-results', [Common\SettingsController::class, 'getEmailValidationResults']);
    Route::get('get-email-validation-user-results', [Common\SettingsController::class, 'getEmailValidationUserResults']);

    // Cron / scheduler settings
    Route::get('settings/cron-data', [Common\SettingsController::class, 'getCronSettingsData']);
    Route::patch('settings/cron-data', [Common\SettingsController::class, 'updateCronSettingsData']);
    Route::patch('settings/cron-days', [Common\SettingsController::class, 'updateCronDaysData']);
    Route::get('job-scheduler', [Common\SettingsController::class, 'getScheduler'])->name('get.job.scheduler');
    Route::patch('post-scheduler', [Common\SettingsController::class, 'postSchedular'])->name('post-scheduler');
    Route::patch('cron-days', [Common\SettingsController::class, 'saveCronDays'])->name('cron-days');
    Route::post('verify-php-path', [Common\SettingsController::class, 'checkPHPExecutablePath'])->name('verify-cron');

    // Cloud / tenancy settings
    Route::get('settings/cloud-details', [Common\SettingsController::class, 'getCloudDetails']);

    // Pipedrive settings
    Route::get('settings/pipedrive', [Common\SettingsController::class, 'getPipedriveSettings']);
    Route::patch('settings/pipedrive', [Common\SettingsController::class, 'updatePipedriveSettings']);
    Route::post('pipedrivekeys', [Common\SettingsController::class, 'pipedrivekeys']);
    Route::post('updatepipedriveDetails', [Common\BaseSettingsController::class, 'updatepipedriveDetails'])->name('updatepipedriveDetails');
    Route::get('getPipedriveFields/{group_id}', [PipedriveController::class, 'getLocalFields']);
    Route::get('pipedrive/mapping/{group_id}', [PipedriveController::class, 'getMapFields']);
    Route::post('sync/pipedrive', [PipedriveController::class, 'mappingFields']);
    Route::get('syncing/pipedriveFields', [PipedriveController::class, 'syncFields']);
    Route::post('pipedrive/get-dropdown', [PipedriveController::class, 'getDropdown']);

    // MSG91 / mobile settings
    Route::get('settings/msg91', [Common\SettingsController::class, 'getMsg91Settings']);
    Route::post('mobileData', [Common\SettingsController::class, 'mobileData']);
    Route::post('mobile-settings-save', [Common\SettingsController::class, 'mobileSettingsSave']);
    Route::post('mobileVerification', [Common\SettingsController::class, 'mobileVerification']);
    Route::get('settings/mobile-validation', [Common\SettingsController::class, 'getMobileValidationSettings']);
    Route::post('updatemobileDetails', [Common\BaseSettingsController::class, 'updateMobileDetails'])->name('updatemobileDetails');

    // GitHub settings
    Route::get('settings/github', [Common\SettingsController::class, 'getGithubSettings']);
    Route::post('githubkeys', [Common\SettingsController::class, 'githubkeys']);
    Route::post('github-setting', [Github\GithubController::class,   'postSettings']);

    // Terms settings
    Route::get('settings/terms', [Common\SettingsController::class, 'getTermsSettings']);
    Route::post('termsUrl', [Common\SettingsController::class, 'termsUrl']);
    Route::post('updateTermsDetails', [Common\BaseSettingsController::class, 'updateTermsDetails'])->name('updateTermsDetails');

    // Captcha / recaptcha
    Route::post('v3captchaDetails', [Common\SettingsController::class, 'v3captchaDetails'])->name('v3captchaDetails');

    // License / encryption
    Route::post('licenseStatus', [Common\SettingsController::class, 'licenseStatus'])->name('licenseStatus');
    Route::get('generate-keys', [HomeController::class, 'createEncryptionKeys']);
    Route::get('version', [HomeController::class, 'getVersion']);
    Route::post('verification', [HomeController::class, 'faveoVerification']);
    Route::get('encryption', [HomeController::class, 'getEncryptedData']);

    // Contact / verification settings
    Route::get('contact-option', [Common\SettingsController::class, 'contactOption'])->name('contact-option');
    Route::post('verificationSettings', [Common\SettingsController::class, 'postContactOption']);

    // System managers
    Route::get('system-managers', [Common\SystemManagerController::class, 'getSystemManagers'])->name('system-managers');
    Route::get('search-admins', [Common\SystemManagerController::class, 'searchAdmin'])->name('search-admins');
    Route::post('updateSystemManager', [Common\SystemManagerController::class, 'updateManagerSettings']);

    // File storage & PDF
    Route::get('file-storage', [Common\SettingsController::class, 'showFileStorage']);
    Route::post('file-storage-path', [Common\SettingsController::class, 'updateStoragePath']);
    Route::get('pdf-settings', [Common\SettingsController::class, 'showPdfSettings']);
    Route::post('pdf-settings', [Common\SettingsController::class, 'updatePdfSettings']);

    // Module settings / API keys
    Route::get('module-settings', [Common\SettingsController::class, 'getModuleSettings']);
    Route::patch('apikeys', [Common\SettingsController::class, 'postKeys']);

    // Debug API
    Route::get('debugg', [Common\SettingsController::class, 'debugSettings']);
    Route::post('save/debugg', [Common\SettingsController::class, 'postdebugSettings']);

    // --------------------------------------------------------
    // Activity & Payment Logs
    // --------------------------------------------------------

    Route::get('get-activity-api', [Common\SettingsController::class, 'getActivityApi']);
    Route::get('get-activity-filters', [Common\SettingsController::class, 'getActivityFilters']);
    Route::delete('activity-delete', [Common\SettingsController::class, 'destroy'])->name('activity-delete');

    Route::get('get-payment-log-api', [Common\SettingsController::class, 'getPaymentLogApi']);
    Route::delete('paymentlog-delete', [Common\SettingsController::class, 'destroyPayment'])->name('paymentlog-delete');

    // --------------------------------------------------------
    // Dashboard
    // --------------------------------------------------------

    Route::get('dashboard', [DashboardController::class, 'dashboard']);

    // --------------------------------------------------------
    // Language Management
    // --------------------------------------------------------

    Route::get('languages', [LanguageController::class, 'viewLanguage'])->middleware('auth');
    Route::post('language-toggle', [LanguageController::class, 'toggleLanguageStatus']);
    Route::post('language-set-default', [LanguageController::class, 'setDefaultLanguage']);

    // --------------------------------------------------------
    // Plugins & Payment Gateways
    // --------------------------------------------------------

    Route::get('payment-gateway-list', [Common\PaymentSettingsController::class, 'getPaymentGatewayList']);
    Route::post('updatePaymentStatus', [Common\PaymentSettingsController::class, 'updatePaymentStatus']);
    Route::post('post-plugin', [Common\PaymentSettingsController::class, 'postPlugins'])->name('post.plugin');
    Route::post('plugin/delete/{slug}', [Common\PaymentSettingsController::class, 'deletePlugin'])->name('delete.plugin');
    Route::post('plugin/status/{slug}', [Common\PaymentSettingsController::class, 'statusPlugin'])->name('status.plugin');

    // --------------------------------------------------------
    // Social Logins & Social Media
    // --------------------------------------------------------

    Route::get('social-logins', [SocialLoginsController::class, 'getSocialLogin'])->middleware('auth');
    Route::get('edit/SocialLogins/{id}', [SocialLoginsController::class, 'editSocialLogin'])->middleware('auth');
    Route::post('update-social-login', [SocialLoginsController::class, 'updateSocialLogin'])->name('update-social-login');

    Route::prefix('social-media')->group(function () {
        Route::get('list', [Common\SocialMediaController::class, 'getSocialList']);
        Route::get('show/{id}', [Common\SocialMediaController::class, 'getSocialMedia']);
        Route::post('create', [Common\SocialMediaController::class, 'createSocialMedia']);
        Route::patch('update/{id}', [Common\SocialMediaController::class, 'updateSocial']);
        Route::delete('delete', [Common\SocialMediaController::class, 'deleteSocialMedia']);
    });

    // --------------------------------------------------------
    // Third-Party Apps
    // --------------------------------------------------------

    Route::get('get-third-party-app', [ThirdPartyAppController::class, 'getThirdPartyDetails'])->name('get-third-party-app');
    Route::post('third-party-app-create', [ThirdPartyAppController::class, 'createThirdPartyApp'])->name('third-party-app-create');
    Route::put('third-party-app-update/{id}', [ThirdPartyAppController::class, 'updateThirdPartyApp'])->name('third-party-app-update');
    Route::get('get-app-key', [ThirdPartyAppController::class, 'getAppKey'])->name('get-app-key');
    Route::delete('third-party-delete', [ThirdPartyAppController::class, 'deleteThirdPartyApp'])->name('third-party-delete');

    // --------------------------------------------------------
    // Cloud / Tenancy (Admin)
    // --------------------------------------------------------

    Route::post('create/tenant', [Tenancy\TenantController::class, 'createTenant']);
    Route::get('view/tenant', [Tenancy\TenantController::class, 'viewTenant'])->middleware('admin');
    Route::get('get-tenants', [Tenancy\TenantController::class, 'getTenants'])->name('get-tenants')->middleware('admin');
    Route::delete('delete-tenant', [Tenancy\TenantController::class, 'destroyTenant'])->name('delete-tenant')->middleware('admin');
    Route::get('delete/domain/{orderNumber}/{isDelete}', [Tenancy\TenantController::class, 'DeleteCloudInstanceForClient']);
    Route::post('cloud-details', [Tenancy\TenantController::class, 'saveCloudDetails'])->name('cloud-details')->middleware('admin');
    Route::post('cloud-pop-up', [Tenancy\TenantController::class, 'cloudPopUp'])->name('cloud-pop-up')->middleware('admin');
    Route::post('cloud-product-store', [Tenancy\TenantController::class, 'cloudProductStore'])->name('cloud-product-store')->middleware('admin');
    Route::post('enable/cloud', [Tenancy\TenantController::class, 'enableCloud'])->name('enable-cloud')->middleware('admin');
    Route::get('fetch-data', [Tenancy\CloudExtraActivities::class, 'fetchData'])->name('fetch-data');
    Route::post('update-trial-status', [Tenancy\CloudExtraActivities::class, 'updateTrialStatus'])->name('update-trial-status');
    Route::delete('delete-cloud-product', [Tenancy\CloudExtraActivities::class, 'DeleteProductConfig'])->name('delete-cloud-product');
    Route::delete('remove-location', [Tenancy\CloudExtraActivities::class, 'removeLocation'])->name('remove-location');
    Route::post('cloud-data-center-store', [Tenancy\CloudExtraActivities::class, 'storeCloudDataCenter'])->middleware('admin')->name('cloud-data-center-store');
    Route::get('export-tenats', [Tenancy\TenantController::class, 'exportTenats'])->middleware('admin')->name('export-tenats');

    // --------------------------------------------------------
    // GitHub Integration
    // --------------------------------------------------------

    Route::get('github-auth-app', [Github\GithubController::class, 'authForSpecificApp']);
    Route::get('github-releases', [Github\GithubController::class, 'listRepositories']);
    Route::get('github-downloads', [Github\GithubController::class, 'getDownloadCount']);

    // --------------------------------------------------------
    // Reports
    // --------------------------------------------------------

    // RESTful report endpoints
    Route::get('reports', [ReportController::class, 'getAllReports']);
    Route::delete('reports', [ReportController::class, 'deleteBulkReports']);
    Route::get('reports/setting', [ReportController::class, 'getReportsSettings']);
    Route::patch('reports/setting', [ReportController::class, 'updateReportsSettings']);
    Route::get('download-exported-file/{id}', [User\ClientController::class, 'downloadExportedFile'])->name('download.exported.file');

    // Legacy report endpoints
    Route::get('reports/view', [ReportController::class, 'viewReports']);
    Route::delete('report-delete', [ReportController::class, 'destroyReports']);
    Route::get('records/column', [ReportController::class, 'viewRecordsColumn']);
    Route::post('add_records', [ReportController::class, 'addRecords']);

    // --------------------------------------------------------
    // MSG91 Reports
    // NOTE: 'sms/reports' was removed — Vue uses 'getMsgReports' for this endpoint.
    // --------------------------------------------------------

    Route::get('getMsgReports', [Common\Sms\MSG91Controller::class, 'getMsg91Reports']);
    Route::get('getMsgStatus', [Common\Sms\MSG91Controller::class, 'getMsgStauts']);
    Route::get('getMsgFilters', [Common\Sms\MSG91Controller::class, 'getMsgFilters']);
    Route::get('msgThirdPartyUpdate/{thirdPartyId}', [MSG91Controller::class, 'getThirdPartyMsgDetails']);

    // --------------------------------------------------------
    // Queue & Cache
    // --------------------------------------------------------

    Route::get('queue/list', [Jobs\QueueController::class, 'getQueueData']);
    Route::get('queue/{id}', [Jobs\QueueController::class, 'edit'])->name('queue.edit');
    Route::post('queue/{id}', [Jobs\QueueController::class, 'update'])->name('queue.update');
    Route::post('queue/{queue}/activate', [Jobs\QueueController::class, 'activate']);
    Route::get('queue/{id}/form', [Jobs\QueueController::class, 'getFormById'])->name('queue.form');

    Route::get('cache-settings/list', [Common\CacheSettingsController::class, 'getDriverData']);
    Route::get('cache-settings/{driver}/form', [Common\CacheSettingsController::class, 'getFormByDriver']);
    Route::post('cache-settings/{driver}', [Common\CacheSettingsController::class, 'update']);
    Route::post('cache-settings/{driver}/activate', [Common\CacheSettingsController::class, 'activate']);

    // --------------------------------------------------------
    // WhatsApp Integration (Admin)
    // --------------------------------------------------------

    Route::get('whatsapp-integration-info', [\App\Http\Controllers\WhatsappController::class, 'whatsappIntegration']);
    Route::post('whatsapp-integration-save', [\App\Http\Controllers\WhatsappController::class, 'whatsappSave']);
    Route::get('whatsapp-users-api', [\App\Http\Controllers\WhatsappController::class, 'whatsappUsersApi']);

    // --------------------------------------------------------
    // Monitoring (Pulse / Horizon)
    // --------------------------------------------------------

    Route::get('monitoring/check', [Common\Monitoring\MonitoringController::class, 'checkPulseHorizon'])
        ->name('monitoring.check');

    // --------------------------------------------------------
    // Localized License (file downloads)
    // --------------------------------------------------------

    Route::get('uploadFile', [License\LocalizedLicenseController::class, 'storeFile']);
    Route::get('downloadLicenseFile', [License\LocalizedLicenseController::class, 'downloadFile'])->name('event.rsvp')->middleware('signed');
    Route::get('downloadPrivate/{orderNo}', [License\LocalizedLicenseController::class, 'downloadPrivate']);
    Route::get('LocalizedLicense/downloadLicense/{fileName}', [License\LocalizedLicenseController::class, 'downloadFileAdmin']);
    Route::get('request', [License\LocalizedLicenseController::class, 'tempOrderLink']);
    Route::get('LocalizedLicense/downloadPrivateKey/{fileName}', [License\LocalizedLicenseController::class, 'downloadPrivateKeyAdmin']);
    Route::post('choose', [License\LocalizedLicenseController::class, 'chooseLicenseMode']);
    Route::get('LocalizedLicense/delete/{fileName}', [License\LocalizedLicenseController::class, 'deleteFile']);
    Route::get('localized-license/files', [LocalizedLicenseController::class, 'filesApi']);
    Route::delete('localized-license/files', [LocalizedLicenseController::class, 'deleteFileApi']);

    // --------------------------------------------------------
    // Product Downloads
    // --------------------------------------------------------

    Route::get('download/{uploadid}/{userid}/{invoice_number}/{versionid}',
        [Product\ProductController::class, 'userDownload']);
    Route::get('product/download/{id}/{invoice?}',
        [Product\ProductController::class, 'adminDownload']);

    // Preview image
    Route::get('preview-file', [FileManagerController::class, 'previewFile']);

    // ==========================================================
    // 4e. INTERNAL / SYSTEM APIs
    // Infrastructure helpers, CSP reporting, product callbacks.
    // ==========================================================

    Route::prefix('api')->group(function () {
        Route::get('check-url', [Api\ApiController::class, 'checkDomain']);
        Route::post('/csp-report', [Api\ApiController::class, 'logCSP']);
    });

    // API-middleware group (bypasses web session/CSRF — machine-to-machine)
    Route::prefix('api')->withoutMiddleware(['web'])->middleware(['api'])->group(function () {
        Route::post('productDownload', [Product\BaseProductController::class, 'productDownload']);
        Route::post('productExist', [Product\BaseProductController::class, 'productFileExist']);
        Route::post('updateInstallationStatus', [Product\BaseProductController::class, 'updateStatus']);
        // Receives reports from MSG91
        Route::post('msg91/reports/{app_key}/{app_secret}',
            [Common\Sms\MSG91Controller::class, 'handleReports'])
            ->withoutMiddleware(['admin', 'auth']);
    });

    // License API admin views (inside installAgora)
    Route::get('api/admin/installationCallbacks/{installation_id}',
        [\App\License\Controllers\Admin\Views\InstallationViewController::class, 'getInstallationCallBacks']);
}); // end Route::middleware('installAgora')

// ============================================================
// SECTION 5: SPA SHELL ROUTES (MUST stay last)
// These catch-all routes serve the Vue SPA HTML. They must be
// registered AFTER all API routes to avoid swallowing API 404s.
// ============================================================

// Admin SPA
Route::get('/admin/{any?}', fn () => view('admin'))
    ->where('any', '.*');

// Client SPA catch-all — Route::fallback() always matches last,
// even after routes registered by service providers.
Route::fallback(fn () => view('client'));
