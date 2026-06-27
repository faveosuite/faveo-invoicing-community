<?php

// ============================================================
// SECTION 1: USE IMPORTS
// ============================================================
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Common;
use App\Http\Controllers\Common\BaseSettingsController;
use App\Http\Controllers\Common\CacheSettingsController;
use App\Http\Controllers\Common\ChatScriptController;
use App\Http\Controllers\Common\Dependency\DependencyController;
use App\Http\Controllers\Common\EmailSettingsController;
use App\Http\Controllers\Common\FileManagerController;
use App\Http\Controllers\Common\Monitoring\MonitoringController;
use App\Http\Controllers\Common\PaymentSettingsController;
use App\Http\Controllers\Common\PipedriveController;
use App\Http\Controllers\Common\SettingsController;
use App\Http\Controllers\Common\Sms\MSG91Controller;
use App\Http\Controllers\Common\SocialMediaController;
use App\Http\Controllers\Common\SystemManagerController;
use App\Http\Controllers\Common\TemplateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FreeTrailController;
use App\Http\Controllers\Front;
use App\Http\Controllers\Front\AutoRenewalController;
use App\Http\Controllers\Front\Cart\CartApiController;
use App\Http\Controllers\Front\ClientController;
use App\Http\Controllers\Front\NewsletterController;
use App\Http\Controllers\Front\PageController;
use App\Http\Controllers\Front\PaymentController;
use App\Http\Controllers\Front\ProfileVerificationController;
use App\Http\Controllers\Front\StoreController;
use App\Http\Controllers\Front\WidgetController;
use App\Http\Controllers\Github\GithubController;
use App\Http\Controllers\Google2FAController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Jobs\QueueController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\License\LicensePermissionsController;
use App\Http\Controllers\License\LicenseSettingsController;
use App\Http\Controllers\License\LocalizedLicenseController;
use App\Http\Controllers\Order\BaseOrderController;
use App\Http\Controllers\Order\ExtendedOrderController;
use App\Http\Controllers\Order\InvoiceController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\RenewController;
use App\Http\Controllers\Payment\CurrencyController;
use App\Http\Controllers\Payment\OpenPaymentController;
use App\Http\Controllers\Payment\PromotionController;
use App\Http\Controllers\Payment\TaxController;
use App\Http\Controllers\Product\BaseProductController;
use App\Http\Controllers\Product\GroupController;
use App\Http\Controllers\Product\PlanController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\ProductPluginController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\SocialLoginsController;
use App\Http\Controllers\Tenancy\CloudExtraActivities;
use App\Http\Controllers\Tenancy\TenantController;
use App\Http\Controllers\ThirdPartyAppController;
use App\Http\Controllers\User;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\SoftDeleteController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\WhatsappController;
use App\License\Controllers\Admin\Views\InstallationViewController;
use App\Model\Order\Order;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
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
Route::get('verify/third-party-token', [TenantController::class, 'verifyThirdPartyToken']);

// --- Billing info / release info (queried by external dashboards) ---
Route::get('api/billingInfo', [HomeController::class, 'getDetailedBillingInfo']);
Route::get('api/pluginInfo', [HomeController::class, 'getDetailsForAClient']);
Route::get('api/billingRelease', [HomeController::class, 'getProductRelease']);

Route::get('/product/detail', [BaseProductController::class, 'getProductUsingLicenseCode']);

// ============================================================
// SECTION 3: WEBHOOKS
// CSRF-exempt external POST — WhatsApp, Stripe, Razorpay.
// These are registered early so VerifyCsrfToken can exclude them.
// ============================================================

Route::match(['get', 'post'], 'faveo-whatsapp',
    [WhatsappController::class, 'whatsappWebhook']);

// ============================================================
// SECTION 4: installAgora MIDDLEWARE GROUP
// All application routes live here. The installAgora middleware
// ensures the app is fully set up before any feature is accessible.
// ============================================================

Route::middleware('installAgora')->group(function (): void {
    // ==========================================================
    // 4a. AUTH APIs
    // Guest-accessible within installAgora (login, register, 2FA,
    // password reset, OTP, social auth). No `auth` middleware here.
    // ==========================================================

    // SPA login-form config (honeypot metadata, captcha settings, etc.)
    Route::get('honeypot', fn (): JsonResponse => successResponse('honeypot', honeypotData()));
    Route::get('auth/login-config', [LoginController::class,        'loginConfig']);
    Route::get('auth/reset-validate/{token}', [ResetPasswordController::class, 'showResetForm']);
    Route::get('auth/verify-config', [AuthController::class,          'verifyConfig']);

    Route::post('login', [LoginController::class, 'login'])
        ->name('login')
        ->middleware(['blockFailedVerifications:login']);

    Route::post('auth/register', [RegisterController::class, 'postRegister'])->name('auth/register');
    Route::get('auth/logout', [LoginController::class,    'logout'])->name('logout');

    // Password reset (GET kept for `password.reset` name used by reset emails)
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

    // 2FA — login-time validation
    Route::middleware(['blockFailedVerifications:2fa', 'session.timeout:10,2fa'])->group(function (): void {
        Route::get('verify-2fa', [Google2FAController::class, 'verify2fa']);
        Route::get('auth/2fa-check', [Google2FAController::class, 'verify2fa']);
        Route::post('2fa/loginValidate', [Google2FAController::class, 'postLoginValidateToken'])->name('2fa/loginValidate');
        Route::post('verify-recovery-code', [Google2FAController::class, 'verifyRecoveryCode'])->name('verify-recovery-code');
    });

    // 2FA — setup (requires password confirmation)
    Route::middleware(['password.confirm'])->group(function (): void {
        Route::get('show/verify-password', [Google2FAController::class, 'showVerifyPassword']);
        Route::post('/2fa/enable', [Google2FAController::class, 'enableTwoFactor']);
        Route::post('2fa-recovery-code', [Google2FAController::class, 'generateRecoveryCode']);
        Route::post('2fa/setupValidate', [Google2FAController::class, 'postSetupValidateToken']);
    });
    Route::post('2fa/disable/{userId?}', [Google2FAController::class, 'disableTwoFactor']);
    Route::post('verify-password', [Google2FAController::class, 'verifyPassword']);

    // OTP / email verification
    Route::middleware(['blockFailedVerifications:verify', 'session.timeout:10,verify'])->group(function (): void {
        Route::post('otp/send', [AuthController::class, 'requestOtp']);
        Route::post('resend_otp', [AuthController::class, 'retryOTP']);
        Route::post('send-email', [AuthController::class, 'sendEmail']);
    });
    Route::middleware(['session.timeout:10,verify'])->group(function (): void {
        Route::post('otp/verify', [AuthController::class, 'verifyOtp']);
        Route::post('email/verify', [AuthController::class, 'verifyEmail']);
    });

    // Social / OAuth logins
    Route::get('/auth/redirect/{provider}', [LoginController::class, 'redirectToGithub']);
    Route::get('/auth/callback/{provider}', [LoginController::class, 'handler']);

    // API token login (used by third-party integrations)
    Route::post('api/login', [LoginController::class, 'postLoginAndGetToken']);

    // SPA auth check — both SPAs call GET /api/user to hydrate auth state.
    // Returns JSON (never HTML) so auth store works correctly.
    Route::get('api/user', fn () => auth()->check()
        ? successResponse('user', auth()->user())
        : response()->json(['message' => 'Unauthenticated.'], 401));

    // ==========================================================
    // 4b. SHARED APIs
    // Used by BOTH the Client Panel and Admin Panel.
    // ==========================================================

    // Language / locale
    Route::get('js/lang', [LanguageController::class, 'getLanguageFile'])->name('assets.lang');

    // Dependency lookups (countries, states, timezones, products, plans, etc.)
    // Used heavily by both panels for dropdowns / form options.
    Route::get('dependency/{type}', [DependencyController::class, 'handle']);

    // PDF invoice download (client views their invoice; admin generates them)
    Route::get('pdf', [InvoiceController::class, 'pdf']);

    // Invoice by ID — both panels fetch a single invoice
    Route::get('invoice/{id}', [InvoiceController::class, 'getInvoice']);

    // Order reissue — called from client OrderShow AND admin OrderShow
    Route::patch('reissue-license', [ExtendedOrderController::class, 'reissueLicense']);

    // 2FA management (shared — both panels have a 2FA settings page)
    // (Already registered in 4a above under password.confirm middleware)

    // Verify password (shared profile guard — both panels)
    // (Already registered in 4a above)

    // WhatsApp shared actions — client can save WABA details; admin manages integration
    Route::post('save-waba-id', [WhatsappController::class, 'saveWabaId']);
    Route::post('url-save', [WhatsappController::class, 'urlSave']);
    Route::post('webhook-url-edit', [WhatsappController::class, 'webhookUrlEdit']);
    Route::post('whatsapp-deregister', [WhatsappController::class, 'deregister']);

    // Contact us (client contact page + admin test)
    Route::post('contact-us', [PageController::class, 'postContactUs']);

    // Domain management (client can change domain; admin manages cloud)
    Route::post('change/domain', [CloudExtraActivities::class, 'changeDomain']);

    // ==========================================================
    // 4c. CLIENT PANEL APIs
    // Consumed primarily by pages/client/* Vue pages.
    // ==========================================================

    // --- Dashboard ---
    Route::get('client-dashboard-details', [ClientController::class, 'clientDetails']);

    // --- Store / Public pages (withoutMiddleware so guests can browse) ---
    Route::get('store/groups', [StoreController::class, 'getGroups'])->name('store.groups');
    Route::get('store/{groupId}/products', [StoreController::class, 'getProducts'])
        ->where('groupId', '[0-9]+')
        ->name('store.group.products');
    Route::get('store/cloud-products', [FreeTrailController::class, 'getCloudProducts'])->name('store.cloud.products');
    Route::post('free-trial/start', [FreeTrailController::class, 'startTrial'])->name('free-trial.start');
    Route::post('newsletter/subscribe', [NewsletterController::class, 'subscribe'])
        ->middleware('recaptcha:newsletter');
    Route::post('demo-request', [PageController::class, 'postDemoReq'])
        ->withoutMiddleware(['auth']);
    // Published pages / contact info / demo status (public, no auth)
    Route::get('page-content/{slug}', [PageController::class, 'pageBySlug']);
    Route::get('contact-us-info', [PageController::class, 'contactUsInfo']);
    Route::get('demo', [PageController::class, 'getDemoStatus']);

    // Open Payment (pay/*) — public payment page, no auth required
    Route::prefix('pay')->withoutMiddleware(['auth', 'web'])->group(function (): void {
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
        Route::post('webhook/stripe', [PaymentController::class, 'stripeWebhook'])->name('webhook.stripe');
        Route::post('webhook/razorpay', [PaymentController::class, 'razorpayWebhook'])->name('webhook.razorpay');
        // Admin-only open-payment views
        Route::get('list', [OpenPaymentController::class, 'listOrders'])->name('open-payment.list');
    });

    // DB-backed shopping cart (Vue SPA)
    Route::prefix('cart')->name('cart.')->group(function (): void {
        Route::get('/', [CartApiController::class, 'show'])->name('show');
        Route::post('items', [CartApiController::class, 'addItem'])->name('items.add');
        Route::put('items/{item}', [CartApiController::class, 'updateItem'])->name('items.update');
        Route::delete('items/{item}', [CartApiController::class, 'removeItem'])->name('items.remove');
        Route::delete('/', [CartApiController::class, 'clear'])->name('clear');
        Route::middleware('auth')->group(function (): void {
            Route::post('coupon', [CartApiController::class, 'applyCoupon'])->name('coupon.apply');
            Route::delete('coupon', [CartApiController::class, 'removeCoupon'])->name('coupon.remove');
            Route::get('checkout', [CartApiController::class, 'checkout'])->name('checkout');
            Route::post('place-order', [CartApiController::class, 'placeOrder'])->name('place-order');
        });
    });

    // --- Invoices (client) ---
    Route::get('get-my-invoices', [ClientController::class, 'getInvoices'])->name('get-my-invoices');
    Route::delete('my-invoice/{id}', [ClientController::class, 'deleteInvoice'])->name('my-invoice.delete');

    // --- Orders (client) ---
    Route::get('get-my-orders', [ClientController::class, 'getClientOrder'])->name('get-my-orders');
    Route::get('renew-popup-details/{productid}', [ClientController::class, 'renewPopupVue']);
    Route::get('get-cloud-settings/{orderId}', [ClientController::class, 'getCloudSettings']);
    Route::get('get-my-invoices/{orderid}/{userid}/{admin?}', [ClientController::class, 'getInvoicesByOrderId']);
    Route::get('get-my-payment-client/{orderid}/{userid}', [ClientController::class, 'getPaymentByOrderIdClient'])->name('get-my-payment-client');
    Route::get('get-my-installations/{orderid}', [ClientController::class, 'getOrderInstallations']);
    Route::get('get-versions/{orderid}', [ClientController::class, 'getVersionList'])->name('get-versions');
    Route::get('get-deploy-versions/{orderId}', [Front\DeployController::class, 'getVersions'])->name('get-deploy-versions');
    Route::post('deploy-product-step', [Front\DeployController::class, 'deployStep'])->name('deploy-product-step');

    // --- Renew (client) ---
    Route::get('get-renew-cost', [RenewController::class, 'getCost']);
    Route::post('client/renew/{id}', [RenewController::class, 'renewByClient']);

    // --- Renew (admin) ---
    Route::post('admin/renew/{id}', [RenewController::class, 'renew']);

    // --- Payments (client) ---
    Route::middleware('auth')->group(function (): void {
        Route::get('invoice/{invoice}/pay-init', [PaymentController::class, 'payInit'])->name('invoice.pay.init');
        Route::get('invoice/{invoice}/pay-success', [PaymentController::class, 'paySuccess'])->name('invoice.pay.success');
        Route::post('invoice/{invoice}/stripe/session', [PaymentController::class, 'stripeSession'])->name('invoice.pay.stripe.session');
        Route::post('invoice/{invoice}/stripe/confirm', [PaymentController::class, 'stripeConfirm'])->name('invoice.pay.stripe.confirm');
        Route::post('invoice/{invoice}/razorpay/order', [PaymentController::class, 'razorpayOrder'])->name('invoice.pay.razorpay.order');
    });

    // --- Auto-renewal (client) ---
    Route::prefix('auto-renewal/{order}')->group(function (): void {
        Route::post('stripe/session', [AutoRenewalController::class, 'stripeSession']);
        Route::post('stripe/confirm', [AutoRenewalController::class, 'stripeConfirm']);
        Route::post('razorpay/order', [AutoRenewalController::class, 'razorpayOrder']);
        Route::post('razorpay/confirm', [AutoRenewalController::class, 'razorpayConfirm']);
        Route::post('disable', [AutoRenewalController::class, 'disable']);
    });

    // --- Profile (client) ---
    Route::get('get-my-profile', [ClientController::class, 'profile'])->name('get-my-profile');
    Route::patch('my-profile', [ClientController::class, 'postProfile']);
    Route::patch('my-password', [ClientController::class, 'postPassword']);

    // Profile OTP verification
    Route::middleware(['blockFailedVerifications:verify'])->group(function (): void {
        Route::post('profile/email/send-otp', [ProfileVerificationController::class, 'sendEmailOtp']);
        Route::post('profile/mobile/send-otp', [ProfileVerificationController::class, 'sendMobileOtp']);
        Route::post('profile/resend-otp', [ProfileVerificationController::class, 'resendOtp']);
    });
    Route::post('profile/email/verify-otp', [ProfileVerificationController::class, 'verifyEmailOtp']);
    Route::post('profile/mobile/verify-otp', [ProfileVerificationController::class, 'verifyMobileOtp']);

    // --- Cloud (client self-service) ---
    Route::post('get-cloud-upgrade-cost', [CloudExtraActivities::class, 'getUpgradeCost']);
    Route::post('changeAgents', [CloudExtraActivities::class, 'agentAlteration']);
    Route::post('upgradeDowngradeCloud', [CloudExtraActivities::class, 'upgradeDowngradeCloud']);
    Route::post('get-agent-inc-dec-cost', [CloudExtraActivities::class, 'getThePaymentCalculationDisplay']);
    Route::get('api/domain', [CloudExtraActivities::class, 'domainCloudAutofill']);

    // --- WhatsApp (client webhook info) ---
    Route::get('get-webhook-url', [WhatsappController::class, 'getWebhookUrl']);
    Route::get('whatsapp-client-numbers/{orderid}', [WhatsappController::class, 'whatsappClientNumbers']);

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
    Route::get('soft-delete', [SoftDeleteController::class, 'softDeletedUsers'])->name('soft-delete');
    Route::get('user/restore/{id}', [SoftDeleteController::class, 'restoreUser']);
    Route::delete('permanent-delete-client', [SoftDeleteController::class, 'permanentDeleteUser']);

    Route::post('/save-columns', [User\ClientController::class, 'saveColumns'])->name('save-columns');
    Route::get('/get-columns', [User\ClientController::class, 'getColumns'])->name('get-columns');
    Route::get('export-users', [User\ClientController::class, 'exportUsers'])->name('export-users');

    // Admin profile
    Route::get('profile', [ProfileController::class, 'profile']);
    Route::patch('profile', [ProfileController::class, 'updateProfile']);
    Route::patch('password', [ProfileController::class, 'updatePassword']);
    Route::get('profile/countries', [ProfileController::class, 'getCountries']);
    Route::get('profile/states/{countryCode}', [ProfileController::class, 'getStatesByCountry']);

    Route::get('get-country', [WelcomeController::class, 'getCountry'])->middleware('admin');

    // --------------------------------------------------------
    // Products, Uploads, Plans, Groups, Categories
    // --------------------------------------------------------

    // RESTful product endpoints
    Route::get('products', [ProductController::class, 'getAllProducts']);
    Route::delete('products', [ProductController::class, 'deleteBulkProducts']);
    Route::put('product', [ProductController::class, 'productCreate']);
    Route::get('product/{productId}', [ProductController::class, 'getProduct']);
    Route::patch('product/{productId}', [ProductController::class, 'updateProduct']);
    Route::get('product/{productId}/plugins', [ProductPluginController::class, 'index']);
    Route::post('product/{productId}/plugins', [ProductPluginController::class, 'sync']);
    Route::get('product/uploads/{productId}', [ProductController::class, 'getProductUploads']);
    Route::get('product/upload/{productUploadId}', [ProductController::class, 'getProductUpload']);
    Route::patch('product/upload/{productUploadId}', [ProductController::class, 'updateProductUpload']);
    Route::put('product/upload/{productId}/', [ProductController::class, 'productUploadCreate']);

    Route::post('get-price', [ProductController::class, 'getPrice']);
    Route::post('chunkupload', [ProductController::class, 'uploadFile']);
    Route::patch('upload/{id}', [ProductController::class, 'uploadUpdate']);

    // RESTful plan endpoints
    Route::get('plans', [PlanController::class, 'getAllPlans']);
    Route::put('plans', [PlanController::class, 'planCreate']);
    Route::get('plan/{planId}', [PlanController::class, 'getPlan']);
    Route::patch('plan/{planId}', [PlanController::class, 'updatePlan']);
    Route::delete('plans', [PlanController::class, 'deleteBulkPlans']);

    // RESTful group endpoints
    Route::get('groups', [GroupController::class, 'getProductGroups']);
    Route::get('group/{group_id}', [GroupController::class, 'getGroup']);
    Route::patch('group/{group_id}', [GroupController::class, 'updateGroup']);
    Route::put('group', [GroupController::class, 'groupCreate']);
    Route::delete('group', [GroupController::class, 'deleteBulkGroups']);

    // --------------------------------------------------------
    // Orders
    // --------------------------------------------------------

    // RESTful order endpoints
    Route::get('orders', [OrderController::class, 'getOrders']);
    Route::delete('orders', [OrderController::class, 'deleteBulkOrders']);
    Route::get('order/{id}', [OrderController::class, 'getOrder']);
    Route::get('getOrderPayments/{orderId}', [OrderController::class, 'getPaymentByOrderId']);
    Route::get('getOrderInvoices/{orderId}', [OrderController::class, 'getOrderInvoices']);

    // Legacy order endpoints
    Route::post('update-license-details', [BaseOrderController::class, 'updateLicenseDetails']);
    Route::get('get-installation-details/{orderId}', [OrderController::class, 'getInstallationDetails']);
    Route::get('export-orders', [OrderController::class, 'exportOrders'])->name('export-orders');

    Route::post('switch-license-mode', [LocalizedLicenseController::class, 'chooseLicenseMode']);

    // --------------------------------------------------------
    // Invoices & Payments
    // --------------------------------------------------------

    // RESTful invoice endpoints
    Route::get('invoices', [InvoiceController::class, 'getInvoices']);
    Route::delete('invoices', [InvoiceController::class, 'deleteBulkInvoices']);
    Route::delete('payments', [InvoiceController::class, 'deleteBulkPayments']);

    Route::post('invoices/{id}/execute', [InvoiceController::class, 'executeInvoice']);
    Route::post('generate/invoice/{user_id?}', [InvoiceController::class, 'invoiceGenerateByForm']);
    Route::get('export-invoices', [InvoiceController::class, 'exportInvoices'])->name('export-invoices');

    // Admin payment forms (legacy blade views — still referenced from admin OrderShow)
    Route::get('newPayment/receive', [InvoiceController::class, 'newPayment']);
    Route::post('newPayment/receive/{clientid}', [InvoiceController::class, 'postNewPayment']);
    Route::post('newMultiplePayment/receive/{clientid}', [InvoiceController::class, 'postNewMultiplePayment']);
    Route::post('newMultiplePayment/update/{clientid}', [InvoiceController::class, 'updateNewMultiplePayment']);

    // --------------------------------------------------------
    // Pages & Widgets (Admin CMS)
    // --------------------------------------------------------

    // RESTful page endpoints
    Route::get('pages', [PageController::class, 'getAllPages']);
    Route::delete('pages', [PageController::class, 'deleteBulkPages']);
    Route::post('page', [PageController::class, 'createPage']);
    Route::get('page/{id}', [PageController::class, 'getPage']);
    Route::put('page/{id}', [PageController::class, 'updatePage']);

    // Legacy page admin endpoints
    Route::post('save/demo', [PageController::class, 'saveDemoPage']);

    // Widget management
    Route::prefix('widgets')->group(function (): void {
        Route::get('list', [WidgetController::class, 'getWidgetList']);
        Route::get('show/{id}', [WidgetController::class, 'getWidget']);
        Route::put('update/{id}', [WidgetController::class, 'updateWidget']);
        Route::delete('delete', [WidgetController::class, 'deleteWidget']);
        Route::post('create', [WidgetController::class, 'createWidget']);
    });

    Route::prefix('chat')->group(function (): void {
        Route::get('list', [ChatScriptController::class, 'getScriptList']);
        Route::get('show/{id}', [ChatScriptController::class, 'getScript']);
        Route::post('create', [ChatScriptController::class, 'createScript']);
        Route::put('update/{id}', [ChatScriptController::class, 'updateScript']);
        Route::delete('delete', [ChatScriptController::class, 'deleteScript']);
    });

    // --------------------------------------------------------
    // Promotions & Coupons
    // --------------------------------------------------------

    // RESTful promotion endpoints
    Route::get('promotions', [PromotionController::class, 'getAllPromotions']);
    Route::get('promotion/{promotionId}', [PromotionController::class, 'getPromotion']);
    Route::get('getPromotionCode', [PromotionController::class, 'getCode']);
    Route::patch('updatePromotion/{promotionId}', [PromotionController::class, 'updatePromotionCode']);
    Route::put('promotionCreate', [PromotionController::class, 'promotionCodeCreate']);
    Route::delete('promotions', [PromotionController::class, 'deleteBulkPromotions']);

    // --------------------------------------------------------
    // Tax & Currency
    // --------------------------------------------------------

    Route::prefix('currency')->group(function (): void {
        Route::get('list', [CurrencyController::class, 'getCurrencyList']);
        Route::post('update-currency', [CurrencyController::class, 'updatecurrency']);
        Route::post('dashboard-currency/{id}', [CurrencyController::class, 'setDashboardCurrency']);
        Route::post('default-currency/{id}', [CurrencyController::class, 'setDefaultCurrency']);
    });

    Route::get('tax-options', [TaxController::class, 'getTaxOptionsApi']);
    Route::post('taxes/option', [TaxController::class, 'saveTaxOptionSetting'])->name('taxes/option');
    Route::get('tax-tables', [TaxController::class, 'getTax']);
    Route::get('tax/edit/{id}', [TaxController::class, 'editTaxApi']);
    Route::put('tax/{id}', [TaxController::class, 'updateTaxApi']);
    Route::post('create/tax-class', [TaxController::class, 'saveTaxClassSettingApi']);
    Route::delete('tax/delete', [TaxController::class, 'deleteTax']);
    Route::get('get-state/{state}', [TaxController::class, 'getState']);

    // --------------------------------------------------------
    // License Types & Permissions
    // --------------------------------------------------------

    Route::get('get-license-type', [LicenseSettingsController::class, 'getLicenseTypes'])->name('get-license-type');
    Route::get('get-license-type/{id}', [LicenseSettingsController::class, 'getLicenseTypeById']);
    Route::post('create-license-type', [LicenseSettingsController::class, 'createLicense']);
    Route::put('update-license-type/{id}', [LicenseSettingsController::class, 'updateLicense']);
    Route::delete('delete-license-type', [LicenseSettingsController::class, 'deleteLicense'])->name('license-type-delete');

    Route::get('get-license-permission', [LicensePermissionsController::class, 'getPermissions'])->name('get-license-permission');
    Route::post('add-permission', [LicensePermissionsController::class, 'addPermission'])->name('add-permission');

    // --------------------------------------------------------
    // Email Templates
    // --------------------------------------------------------

    Route::prefix('template')->group(function (): void {
        Route::get('list', [TemplateController::class, 'getTemplates']);
        Route::get('edit/{id}', [TemplateController::class, 'showTemplate']);
        Route::put('update/{id}', [TemplateController::class, 'updateTemplate']);
    });
    Route::get('/email-log/body/{id}', [SettingsController::class, 'getBody'])->name('email-log.body');

    // --------------------------------------------------------
    // Settings (System, Email, Cron, PDF, File Storage, etc.)
    // --------------------------------------------------------

    // System settings
    Route::get('settings/system-data', [SettingsController::class, 'getSystemSettingsData']);
    Route::get('settings/index-data', [SettingsController::class, 'getSettingsIndexData']);
    Route::patch('settings/system-data', [SettingsController::class, 'updateSystemSettingsData']);
    Route::patch('settings/datetime-data', [SettingsController::class, 'updateDateTimeSettingsData']);
    Route::get('settings/template', [SettingsController::class, 'settingsTemplate']);
    Route::patch('settings/template', [SettingsController::class, 'postSettingsTemplate']);
    Route::get('settings/error', [SettingsController::class, 'getErrorSettings']);
    Route::patch('settings/error', [SettingsController::class, 'postSettingsError']);

    // Email settings
    Route::get('settings/email', [EmailSettingsController::class, 'settingsEmail'])->middleware('auth');
    Route::patch('settings/email', [EmailSettingsController::class, 'postSettingsEmail']);
    Route::post('email-settings-save', [SettingsController::class, 'emailSettingsSave']);
    Route::get('settings/email-validation', [SettingsController::class, 'getEmailValidationSettings']);
    Route::get('settings/email-validation-logs', [SettingsController::class, 'listEmailValidationLogs']);
    Route::get('get-email-validation-results', [SettingsController::class, 'getEmailValidationResults']);

    // Cron / scheduler settings
    Route::get('settings/cron-data', [SettingsController::class, 'getCronSettingsData']);
    Route::patch('settings/cron-data', [SettingsController::class, 'updateCronSettingsData']);
    Route::patch('settings/cron-days', [SettingsController::class, 'updateCronDaysData']);
    Route::post('verify-php-path', [SettingsController::class, 'checkPHPExecutablePath'])->name('verify-cron');

    // Cloud / tenancy settings
    Route::get('settings/cloud-details', [SettingsController::class, 'getCloudDetails']);

    // Pipedrive settings
    Route::get('settings/pipedrive', [SettingsController::class, 'getPipedriveSettings']);
    Route::patch('settings/pipedrive', [SettingsController::class, 'updatePipedriveSettings']);
    Route::post('updatepipedriveDetails', [BaseSettingsController::class, 'updatepipedriveDetails'])->name('updatepipedriveDetails');
    Route::get('getPipedriveFields/{group_id}', [PipedriveController::class, 'getLocalFields']);
    Route::get('pipedrive/mapping/{group_id}', [PipedriveController::class, 'getMapFields']);
    Route::post('sync/pipedrive', [PipedriveController::class, 'mappingFields']);
    Route::get('syncing/pipedriveFields', [PipedriveController::class, 'syncFields']);
    Route::post('pipedrive/get-dropdown', [PipedriveController::class, 'getDropdown']);

    // MSG91 / mobile settings
    Route::get('settings/msg91', [SettingsController::class, 'getMsg91Settings']);
    Route::post('mobile-settings-save', [SettingsController::class, 'mobileSettingsSave']);
    Route::get('settings/mobile-validation', [SettingsController::class, 'getMobileValidationSettings']);
    Route::post('updatemobileDetails', [BaseSettingsController::class, 'updateMobileDetails'])->name('updatemobileDetails');

    // GitHub settings
    Route::get('settings/github', [SettingsController::class, 'getGithubSettings']);
    Route::post('github-setting', [GithubController::class,   'postSettings']);

    // Terms settings
    Route::get('settings/terms', [SettingsController::class, 'getTermsSettings']);
    Route::post('updateTermsDetails', [BaseSettingsController::class, 'updateTermsDetails'])->name('updateTermsDetails');
    Route::get('settings/deployment', [Common\SettingsController::class, 'getDeploymentSettings'])->name('getDeploymentSettings');
    Route::post('settings/deployment', [Common\SettingsController::class, 'saveDeploymentSettings'])->name('saveDeploymentSettings');

    // License / encryption
    Route::post('licenseStatus', [SettingsController::class, 'licenseStatus'])->name('licenseStatus');

    // Contact / verification settings
    Route::get('contact-option', [SettingsController::class, 'contactOption'])->name('contact-option');
    Route::post('verificationSettings', [SettingsController::class, 'postContactOption']);

    // System managers
    Route::get('system-managers', [SystemManagerController::class, 'getSystemManagers'])->name('system-managers');
    Route::get('search-admins', [SystemManagerController::class, 'searchAdmin'])->name('search-admins');
    Route::post('updateSystemManager', [SystemManagerController::class, 'updateManagerSettings']);

    // File storage & PDF
    Route::get('file-storage', [SettingsController::class, 'showFileStorage']);
    Route::post('file-storage-path', [SettingsController::class, 'updateStoragePath']);
    Route::get('pdf-settings', [SettingsController::class, 'showPdfSettings']);
    Route::post('pdf-settings', [SettingsController::class, 'updatePdfSettings']);

    // Module settings / API keys
    Route::get('module-settings', [SettingsController::class, 'getModuleSettings']);

    // Debug API
    Route::get('debugg', [SettingsController::class, 'debugSettings']);
    Route::post('save/debugg', [SettingsController::class, 'postdebugSettings']);

    // --------------------------------------------------------
    // Activity & Payment Logs
    // --------------------------------------------------------

    Route::get('get-activity-api', [SettingsController::class, 'getActivityApi']);
    Route::get('get-activity-filters', [SettingsController::class, 'getActivityFilters']);

    Route::get('get-payment-log-api', [SettingsController::class, 'getPaymentLogApi']);
    Route::delete('paymentlog-delete', [SettingsController::class, 'destroyPayment'])->name('paymentlog-delete');

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

    Route::get('payment-gateway-list', [PaymentSettingsController::class, 'getPaymentGatewayList']);
    Route::post('updatePaymentStatus', [PaymentSettingsController::class, 'updatePaymentStatus']);
    Route::post('plugin/status/{slug}', [PaymentSettingsController::class, 'statusPlugin'])->name('status.plugin');

    // --------------------------------------------------------
    // Social Logins & Social Media
    // --------------------------------------------------------

    Route::get('social-logins', [SocialLoginsController::class, 'getSocialLogin'])->middleware('auth');
    Route::get('edit/SocialLogins/{id}', [SocialLoginsController::class, 'editSocialLogin'])->middleware('auth');
    Route::post('update-social-login', [SocialLoginsController::class, 'updateSocialLogin'])->name('update-social-login');

    Route::prefix('social-media')->group(function (): void {
        Route::get('list', [SocialMediaController::class, 'getSocialList']);
        Route::get('show/{id}', [SocialMediaController::class, 'getSocialMedia']);
        Route::post('create', [SocialMediaController::class, 'createSocialMedia']);
        Route::patch('update/{id}', [SocialMediaController::class, 'updateSocial']);
        Route::delete('delete', [SocialMediaController::class, 'deleteSocialMedia']);
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

    Route::get('get-tenants', [TenantController::class, 'getTenants'])->name('get-tenants')->middleware('admin');
    Route::delete('delete-tenant', [TenantController::class, 'destroyTenant'])->name('delete-tenant')->middleware('admin');
    Route::get('delete/domain/{orderNumber}/{isDelete}', [TenantController::class, 'DeleteCloudInstanceForClient']);
    Route::post('cloud-details', [TenantController::class, 'saveCloudDetails'])->name('cloud-details')->middleware('admin');
    Route::post('cloud-pop-up', [TenantController::class, 'cloudPopUp'])->name('cloud-pop-up')->middleware('admin');
    Route::post('cloud-product-store', [TenantController::class, 'cloudProductStore'])->name('cloud-product-store')->middleware('admin');
    Route::post('enable/cloud', [TenantController::class, 'enableCloud'])->name('enable-cloud')->middleware('admin');
    Route::get('fetch-data', [CloudExtraActivities::class, 'fetchData'])->name('fetch-data');
    Route::post('update-trial-status', [CloudExtraActivities::class, 'updateTrialStatus'])->name('update-trial-status');
    Route::delete('delete-cloud-product', [CloudExtraActivities::class, 'DeleteProductConfig'])->name('delete-cloud-product');
    Route::delete('remove-location', [CloudExtraActivities::class, 'removeLocation'])->name('remove-location');
    Route::post('cloud-data-center-store', [CloudExtraActivities::class, 'storeCloudDataCenter'])->middleware('admin')->name('cloud-data-center-store');
    Route::get('export-tenats', [TenantController::class, 'exportTenats'])->middleware('admin')->name('export-tenats');

    // --------------------------------------------------------
    // Reports
    // --------------------------------------------------------

    // RESTful report endpoints
    Route::get('reports', [ReportController::class, 'getAllReports']);
    Route::delete('reports', [ReportController::class, 'deleteBulkReports']);
    Route::get('reports/setting', [ReportController::class, 'getReportsSettings']);
    Route::patch('reports/setting', [ReportController::class, 'updateReportsSettings']);
    Route::get('download-exported-file/{id}', [User\ClientController::class, 'downloadExportedFile'])->name('download.exported.file');

    // --------------------------------------------------------
    // MSG91 Reports
    // NOTE: 'sms/reports' was removed — Vue uses 'getMsgReports' for this endpoint.
    // --------------------------------------------------------

    Route::get('getMsgReports', [MSG91Controller::class, 'getMsg91Reports']);
    Route::get('getMsgFilters', [MSG91Controller::class, 'getMsgFilters']);

    // --------------------------------------------------------
    // Queue & Cache
    // --------------------------------------------------------

    Route::get('queue/list', [QueueController::class, 'getQueueData']);
    Route::get('queue/{id}', [QueueController::class, 'edit'])->name('queue.edit');
    Route::post('queue/{id}', [QueueController::class, 'update'])->name('queue.update');
    Route::post('queue/{queue}/activate', [QueueController::class, 'activate']);
    Route::get('queue/{id}/form', [QueueController::class, 'getFormById'])->name('queue.form');

    Route::get('cache-settings/list', [CacheSettingsController::class, 'getDriverData']);
    Route::get('cache-settings/{driver}/form', [CacheSettingsController::class, 'getFormByDriver']);
    Route::post('cache-settings/{driver}', [CacheSettingsController::class, 'update']);
    Route::post('cache-settings/{driver}/activate', [CacheSettingsController::class, 'activate']);

    // --------------------------------------------------------
    // WhatsApp Integration (Admin)
    // --------------------------------------------------------

    Route::get('whatsapp-integration-info', [WhatsappController::class, 'whatsappIntegration']);
    Route::post('whatsapp-integration-save', [WhatsappController::class, 'whatsappSave']);
    Route::get('whatsapp-users-api', [WhatsappController::class, 'whatsappUsersApi']);

    // --------------------------------------------------------
    // Monitoring (Pulse / Horizon)
    // --------------------------------------------------------

    Route::get('monitoring/check', [MonitoringController::class, 'checkPulseHorizon'])
        ->name('monitoring.check');

    // --------------------------------------------------------
    // Localized License (file downloads)
    // --------------------------------------------------------

    Route::get('downloadLicenseFile', [LocalizedLicenseController::class, 'downloadFile'])->name('event.rsvp')->middleware('signed');
    Route::get('downloadPrivate/{orderNo}', [LocalizedLicenseController::class, 'downloadPrivate']);
    Route::get('LocalizedLicense/downloadLicense/{fileName}', [LocalizedLicenseController::class, 'downloadFileAdmin']);
    Route::get('LocalizedLicense/downloadPrivateKey/{fileName}', [LocalizedLicenseController::class, 'downloadPrivateKeyAdmin']);
    Route::get('localized-license/files', [LocalizedLicenseController::class, 'filesApi']);
    Route::delete('localized-license/files', [LocalizedLicenseController::class, 'deleteFileApi']);

    // --------------------------------------------------------
    // Product Downloads
    // --------------------------------------------------------

    Route::get('download/{order_id}/{version_id?}',
        [ProductController::class, 'userDownload']);
    Route::get('product/download/{id}/{release?}',
        [ProductController::class, 'adminDownload']);

    // Preview image
    Route::get('preview-file', [FileManagerController::class, 'previewFile']);

    // ==========================================================
    // 4e. INTERNAL / SYSTEM APIs
    // Infrastructure helpers, CSP reporting, product callbacks.
    // ==========================================================

    Route::prefix('api')->group(function (): void {
        Route::post('/csp-report', [ApiController::class, 'logCSP']);
    });

    // API-middleware group (bypasses web session/CSRF — machine-to-machine)
    Route::prefix('api')->withoutMiddleware(['web'])->middleware(['api'])->group(function (): void {
        // Receives reports from MSG91
        Route::post('msg91/reports/{app_key}/{app_secret}',
            [MSG91Controller::class, 'handleReports'])
            ->withoutMiddleware(['admin', 'auth']);
    });

    // License API admin views (inside installAgora)
    Route::get('api/admin/installationCallbacks/{installation_id}',
        [InstallationViewController::class, 'getInstallationCallBacks']);

    // ==========================================================
    // SECTION 5: SPA SHELL ROUTES (MUST stay last inside this group)
    // These catch-all routes serve the Vue SPA HTML. They must be
    // registered AFTER all API routes to avoid swallowing API 404s.
    // ==========================================================

    // Admin SPA
    Route::get('/admin/{any?}', fn (): Factory|\Illuminate\Contracts\View\View => view('admin'))
        ->where('any', '.*');

    // Client SPA catch-all — Route::fallback() always matches last,
    // even after routes registered by service providers.
    Route::fallback(fn (): Factory|\Illuminate\Contracts\View\View => view('client'));
}); // end Route::middleware('installAgora')
