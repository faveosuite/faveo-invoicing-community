<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Common\AuthBrandController;
use App\Http\Controllers\Common\BaseSettingsController;
use App\Http\Controllers\Common\CacheSettingsController;
use App\Http\Controllers\Common\ChatScriptController;
use App\Http\Controllers\Common\Dependency\DependencyController;
use App\Http\Controllers\Common\EmailSettingsController;
use App\Http\Controllers\Common\FileManagerController;
use App\Http\Controllers\Common\Monitoring\MonitoringController;
use App\Http\Controllers\Common\PaymentSettingsController;
use App\Http\Controllers\Common\PipedriveController;
use App\Http\Controllers\Common\SeoDefaultPageController;
use App\Http\Controllers\Common\SeoSettingsController;
use App\Http\Controllers\Common\SettingsController;
use App\Http\Controllers\Common\Sms\MSG91Controller;
use App\Http\Controllers\Common\SocialMediaController;
use App\Http\Controllers\Common\SystemManagerController;
use App\Http\Controllers\Common\TemplateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FreeTrailController;
use App\Http\Controllers\Front;
use App\Http\Controllers\Front\AccountSetupController;
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
use App\Model\Order\Order;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Every route needs a finished install (installAgora); until then all of them
| redirect to the installer.
|
| 1. External APIs   license/update callbacks and the WhatsApp webhook
| 2. Public          no login: store, pages, cart, open payment, lookups
| 3. Guest           signed-out only: login, register, password reset, verification
| 4. Two-factor      login-time 2FA check and 2FA setup
| 5. Signed-in       client panel + endpoints shared with admins
| 6. Admin           admin panel
| 7. Machine         CSP reports and MSG91 delivery webhook
| 8. SPA pages       Vue shell routes + fallback (must stay last)
|
| Middleware lives here only — controllers don't register any in constructors
| (tests/Unit/Backend/Routes/RouteMiddlewareTest.php enforces it).
|
*/

Route::middleware('installAgora')->group(function (): void {
    // ==========================================================
    // 1. EXTERNAL APIs — Faveo installations, licensee servers, marketing site
    // ==========================================================

    Route::post('serial', [HomeController::class, 'serial']);
    Route::post('v2/serial', [HomeController::class, 'serialV2']);
    Route::get('download/faveo', [HomeController::class, 'downloadForFaveo']); // before download/{order_id} below
    Route::get('version/latest', [HomeController::class, 'latestVersion']);
    Route::post('update-latest-version', [HomeController::class, 'updateLatestVersion']);
    Route::post('v1/checkUpdatesExpiry', [HomeController::class, 'checkUpdatesExpiry']);
    Route::post('update/lic-code', [HomeController::class, 'updateLicenseCode']);
    Route::get('new-version-available', [HomeController::class, 'isNewVersionAvailable']);
    Route::post('renewurl', [HomeController::class, 'renewurl']);

    Route::get('verify/third-party-token', [TenantController::class, 'verifyThirdPartyToken']);
    Route::get('pricing/data', [HomeController::class, 'getPricingData']);
    Route::get('api/billingInfo', [HomeController::class, 'getDetailedBillingInfo']);
    Route::get('api/pluginInfo', [HomeController::class, 'getDetailsForAClient']);
    Route::get('api/billingRelease', [HomeController::class, 'getProductRelease']);
    Route::get('product/detail', [BaseProductController::class, 'getProductUsingLicenseCode']);

    // WhatsApp webhook (GET = Meta verification handshake, POST = events)
    Route::match(['get', 'post'], 'faveo-whatsapp', [WhatsappController::class, 'whatsappWebhook']);

    // ==========================================================
    // 2. PUBLIC — anyone, signed in or not
    // ==========================================================

    // Auth state and form config for the SPAs
    Route::get('honeypot', fn (): JsonResponse => successResponse('honeypot', honeypotData()));
    Route::get('auth/login-config', [LoginController::class, 'loginConfig']);
    Route::get('auth/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('api/user', fn () => auth()->check() // JSON 401 (never a redirect) for the SPA auth store
        ? successResponse('user', auth()->user())
        : response()->json(['message' => 'Unauthenticated.'], 401));

    // Lookups
    Route::get('js/lang', [LanguageController::class, 'getLanguageFile'])->name('assets.lang');
    Route::get('dependency/{type}', [DependencyController::class, 'handle']);
    Route::get('get-state/{state}', [TaxController::class, 'getState']);
    Route::get('preview-file', [FileManagerController::class, 'previewFile']);

    // Store and site pages
    Route::get('store/groups', [StoreController::class, 'getGroups'])->name('store.groups');
    Route::get('store/{groupId}/products', [StoreController::class, 'getProducts'])
        ->where('groupId', '[0-9]+')
        ->name('store.group.products');
    Route::get('store/cloud-products', [FreeTrailController::class, 'getCloudProducts'])->name('store.cloud.products');
    Route::get('page-content/{slug}', [PageController::class, 'pageBySlug']);
    Route::get('contact-us-info', [PageController::class, 'contactUsInfo']);
    Route::post('contact-us', [PageController::class, 'postContactUs'])->middleware('recaptcha:contact');
    Route::post('newsletter/subscribe', [NewsletterController::class, 'subscribe'])->middleware('recaptcha:newsletter');
    Route::post('demo-request', [PageController::class, 'postDemoReq'])->middleware('recaptcha:demo');

    // Reissue: guests are refused by OrderPolicy, not by middleware
    Route::patch('reissue-license', [ExtendedOrderController::class, 'reissueLicense']);

    // Open payment page (/pay). Runs without the web group (no session/CSRF).
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
    });

    // Cart: guests get a session cart, signed-in users a DB cart; coupon/checkout need login.
    // Prefix is 'my-cart' so it doesn't shadow the /cart SPA page.
    Route::prefix('my-cart')->name('cart.')->group(function (): void {
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

    // ==========================================================
    // 3. GUEST — signed-out users only
    // ==========================================================

    Route::middleware('guest')->group(function (): void {
        Route::post('login', [LoginController::class, 'login'])->name('login')
            ->middleware(['blockFailedVerifications:login', 'recaptcha:login']);
        Route::post('api/login', [LoginController::class, 'postLoginAndGetToken']); // token login for integrations
        Route::post('auth/register', [RegisterController::class, 'postRegister'])->name('auth/register')
            ->middleware('recaptcha:register');

        // Password reset (password.reset is the link in the reset email)
        Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email')
            ->middleware(['throttle:forgot-password', 'recaptcha:forgot']);
        Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetPage'])->name('password.reset');
        Route::get('auth/reset-validate/{token}', [ResetPasswordController::class, 'showResetForm']);
        Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update')
            ->middleware('recaptcha:reset');

        // Email / mobile verification after register or login
        Route::middleware('session.timeout:10,verify')->group(function (): void {
            Route::middleware('blockFailedVerifications:verify')->group(function (): void {
                Route::get('auth/verify-config', [AuthController::class, 'verifyConfig']);
                Route::middleware('throttle:otp-send')->group(function (): void {
                    Route::post('otp/send', [AuthController::class, 'requestOtp']);
                    Route::post('resend_otp', [AuthController::class, 'retryOTP']);
                    Route::post('send-email', [AuthController::class, 'sendEmail']);
                });
            });
            Route::post('otp/verify', [AuthController::class, 'verifyOtp'])
                ->middleware(['blockFailedVerifications:verify,mobile-verify,email-verify,email-verify-new,email-verify-old,email-verify-mobile', 'recaptcha:mobile_verify']);
            Route::post('email/verify', [AuthController::class, 'verifyEmail'])
                ->middleware(['blockFailedVerifications:verify,mobile-verify,email-verify,email-verify-new,email-verify-old,email-verify-mobile', 'recaptcha:email_verify']);
        });

        // Social login
        Route::get('auth/redirect/{provider}', [SocialAuthController::class, 'redirect']);
        Route::get('auth/callback/{provider}', [SocialAuthController::class, 'callback']);
        Route::get('auth/social-complete-config', [SocialAuthController::class, 'completeConfig']);
        Route::post('auth/social-complete', [SocialAuthController::class, 'complete']);
    });

    // ==========================================================
    // 4. TWO-FACTOR
    // ==========================================================

    // Login-time check — the user has passed the password step but isn't signed in yet.
    // No GET 'verify-2fa' route: that path is the SPA page.
    Route::middleware(['blockFailedVerifications:2fa', 'session.timeout:10,2fa'])->group(function (): void {
        Route::get('auth/2fa-check', [Google2FAController::class, 'verify2fa']);
        Route::post('2fa/loginValidate', [Google2FAController::class, 'postLoginValidateToken'])->name('2fa/loginValidate')
            ->middleware('recaptcha:login_2fa');
        Route::post('verify-recovery-code', [Google2FAController::class, 'verifyRecoveryCode'])->name('verify-recovery-code')
            ->middleware('recaptcha:login_recovery');
    });

    // Setup / disable from the profile page
    Route::middleware('auth')->group(function (): void {
        Route::middleware('password.confirm')->group(function (): void {
            Route::post('2fa/enable', [Google2FAController::class, 'enableTwoFactor']);
            Route::post('2fa-recovery-code', [Google2FAController::class, 'generateRecoveryCode']);
            Route::get('show/verify-password', [Google2FAController::class, 'showVerifyPassword']);
            Route::post('2fa/setupValidate', [Google2FAController::class, 'postSetupValidateToken']);
        });
        Route::post('2fa/disable/{userId?}', [Google2FAController::class, 'disableTwoFactor']);
        Route::post('verify-password', [Google2FAController::class, 'verifyPassword']);
    });

    // ==========================================================
    // 5. SIGNED-IN — client panel, plus endpoints admins use too
    // ==========================================================

    Route::middleware('auth')->group(function (): void {
        // Dashboard and profile
        Route::get('client-dashboard-details', [ClientController::class, 'clientDetails']);
        Route::get('get-my-profile', [ClientController::class, 'profile'])->name('get-my-profile');
        Route::patch('my-profile', [ClientController::class, 'postProfile']);
        Route::patch('my-password', [ClientController::class, 'postPassword']);

        // Billing details asked once after login (GET /account-setup is the SPA page)
        Route::get('account-setup/details', [AccountSetupController::class, 'show']);
        Route::post('account-setup', [AccountSetupController::class, 'store']);

        // Email / mobile change from the profile page (OTP)
        Route::middleware('blockFailedVerifications:verify')->group(function (): void {
            Route::post('profile/email/send-otp', [ProfileVerificationController::class, 'sendEmailOtp']);
            Route::post('profile/mobile/send-otp', [ProfileVerificationController::class, 'sendMobileOtp']);
            Route::post('profile/resend-otp', [ProfileVerificationController::class, 'resendOtp']);
        });
        Route::post('profile/email/verify-otp', [ProfileVerificationController::class, 'verifyEmailOtp'])
            ->middleware('blockFailedVerifications:verify,mobile-verify,email-verify,email-verify-new,email-verify-old,email-verify-mobile');
        Route::post('profile/mobile/verify-otp', [ProfileVerificationController::class, 'verifyMobileOtp'])
            ->middleware('blockFailedVerifications:verify,mobile-verify,email-verify,email-verify-new,email-verify-old,email-verify-mobile');

        // Invoices
        Route::get('get-my-invoices', [ClientController::class, 'getInvoices'])->name('get-my-invoices');
        Route::delete('my-invoice/{id}', [ClientController::class, 'deleteInvoice'])->name('my-invoice.delete');
        Route::get('get-credit-balance', [ClientController::class, 'getCreditBalance'])->name('get-credit-balance');
        Route::get('invoice/{id}', [InvoiceController::class, 'getInvoice']);
        Route::get('pdf', [InvoiceController::class, 'pdf']);

        // Paying an invoice
        Route::get('invoice/{invoice}/pay-init', [PaymentController::class, 'payInit'])->name('invoice.pay.init');
        Route::get('invoice/{invoice}/pay-success', [PaymentController::class, 'paySuccess'])->name('invoice.pay.success');
        Route::post('invoice/{invoice}/stripe/session', [PaymentController::class, 'stripeSession'])->name('invoice.pay.stripe.session');
        Route::post('invoice/{invoice}/stripe/confirm', [PaymentController::class, 'stripeConfirm'])->name('invoice.pay.stripe.confirm');
        Route::post('invoice/{invoice}/razorpay/order', [PaymentController::class, 'razorpayOrder'])->name('invoice.pay.razorpay.order');
        Route::post('invoice/{invoice}/razorpay/confirm', [PaymentController::class, 'razorpayConfirm'])->name('invoice.pay.razorpay.confirm');
        Route::post('invoice/{invoice}/apply-credit', [PaymentController::class, 'applyCredit'])->name('invoice.pay.apply-credit');

        // Orders
        Route::get('get-my-orders', [ClientController::class, 'getClientOrder'])->name('get-my-orders');
        Route::get('get-my-order/{id}', [ClientController::class, 'getClientOrderDetail'])->name('get-my-order');
        Route::get('get-my-invoices/{orderid}/{userid}/{admin?}', [ClientController::class, 'getInvoicesByOrderId']);
        Route::get('get-my-payment-client/{orderid}/{userid}', [ClientController::class, 'getPaymentByOrderIdClient'])->name('get-my-payment-client');
        Route::get('get-my-installations/{orderid}', [ClientController::class, 'getOrderInstallations']);
        Route::get('get-versions/{orderid}', [ClientController::class, 'getVersionList'])->name('get-versions');
        Route::post('free-trial/start', [FreeTrailController::class, 'startTrial'])->name('free-trial.start');

        // Renewal
        Route::get('renew-popup-details/{productid}', [ClientController::class, 'renewPopupVue']);
        Route::get('get-renew-cost', [RenewController::class, 'getCost']);
        Route::post('client/renew/{id}', [RenewController::class, 'renewByClient']);

        // Auto-renewal (client enables/disables; admins can disable too)
        Route::prefix('auto-renewal/{order}')->group(function (): void {
            Route::post('stripe/session', [AutoRenewalController::class, 'stripeSession']);
            Route::post('stripe/confirm', [AutoRenewalController::class, 'stripeConfirm']);
            Route::post('razorpay/order', [AutoRenewalController::class, 'razorpayOrder']);
            Route::post('razorpay/confirm', [AutoRenewalController::class, 'razorpayConfirm']);
            Route::post('disable', [AutoRenewalController::class, 'disable']);
        });

        // Downloads and licence files
        Route::get('download/{order_id}/{version_id?}', [ProductController::class, 'userDownload']);
        Route::get('downloadLicenseFile', [LocalizedLicenseController::class, 'downloadFile']);
        Route::get('LocalizedLicense/{orderNo}/plugins', [LocalizedLicenseController::class, 'pluginsForOrder']);
        Route::post('license-binding', [LocalizedLicenseController::class, 'submitLicenseBinding']);

        // Deploy
        Route::get('get-deploy-versions/{orderId}', [Front\DeployController::class, 'getVersions'])->name('get-deploy-versions');
        Route::post('deploy-product-step', [Front\DeployController::class, 'deployStep'])->name('deploy-product-step')
            ->middleware('throttle:60,1');

        // Cloud self-service
        Route::get('get-cloud-settings/{orderId}', [ClientController::class, 'getCloudSettings']);
        Route::get('api/domain', [CloudExtraActivities::class, 'domainCloudAutofill']);
        Route::post('change/domain', [CloudExtraActivities::class, 'changeDomain']);
        Route::delete('delete/domain/{orderNumber}/{isDelete}', [TenantController::class, 'DeleteCloudInstanceForClient']);
        Route::post('get-cloud-upgrade-cost', [CloudExtraActivities::class, 'getUpgradeCost']);
        Route::post('changeAgents', [CloudExtraActivities::class, 'agentAlteration']);
        Route::post('upgradeDowngradeCloud', [CloudExtraActivities::class, 'upgradeDowngradeCloud']);
        Route::post('get-agent-inc-dec-cost', [CloudExtraActivities::class, 'getThePaymentCalculationDisplay']);

        // WhatsApp
        Route::get('get-webhook-url', [WhatsappController::class, 'getWebhookUrl']);
        Route::get('whatsapp-client-numbers/{orderid}', [WhatsappController::class, 'whatsappClientNumbers']);
        Route::post('save-waba-id', [WhatsappController::class, 'saveWabaId']);
        Route::post('url-save', [WhatsappController::class, 'urlSave']);
        Route::post('webhook-url-edit', [WhatsappController::class, 'webhookUrlEdit']);
        Route::post('whatsapp-deregister', [WhatsappController::class, 'deregister']);
    });

    // ==========================================================
    // 6. ADMIN — signed-in admins only
    // ==========================================================

    Route::middleware(['auth', 'admin'])->group(function (): void {
        // Dashboard and admin profile
        Route::get('dashboard', [DashboardController::class, 'dashboard']);
        Route::get('profile', [ProfileController::class, 'profile']);
        Route::patch('profile', [ProfileController::class, 'updateProfile']);
        Route::patch('password', [ProfileController::class, 'updatePassword']);
        Route::get('profile/countries', [ProfileController::class, 'getCountries']);
        Route::get('profile/states/{countryCode}', [ProfileController::class, 'getStatesByCountry']);
        Route::get('get-country', [WelcomeController::class, 'getCountry']);

        // Users
        Route::get('users', [User\ClientController::class, 'getAllUsers']);
        Route::delete('users', [User\ClientController::class, 'deleteBulkUsers']);
        Route::post('users', [User\ClientController::class, 'userCreate']);
        Route::get('user/{id}', [User\ClientController::class, 'getEditUser']);
        Route::patch('user/{id}', [User\ClientController::class, 'userUpdate']);
        Route::get('user/{id}/summary', [User\ClientController::class, 'getUserSummary']);
        Route::get('user/{id}/invoices', [User\ClientController::class, 'getUserInvoices']);
        Route::get('user/{id}/payments', [User\ClientController::class, 'getUserPayments']);
        Route::get('user/{id}/credits', [User\ClientController::class, 'getUserCredits']);
        Route::get('user/{id}/comments', [User\ClientController::class, 'getUserComments']);
        Route::post('user/{id}/comments', [User\ClientController::class, 'storeUserComment']);
        Route::put('user/{id}/comments/{commentId}', [User\ClientController::class, 'updateUserComment']);
        Route::delete('user/{id}/comments/{commentId}', [User\ClientController::class, 'deleteUserComment']);
        Route::get('soft-delete', [SoftDeleteController::class, 'softDeletedUsers'])->name('soft-delete');
        Route::post('user/restore/{id}', [SoftDeleteController::class, 'restoreUser']);
        Route::delete('permanent-delete-client', [SoftDeleteController::class, 'permanentDeleteUser']);
        Route::post('save-columns', [User\ClientController::class, 'saveColumns'])->name('save-columns');
        Route::get('get-columns', [User\ClientController::class, 'getColumns'])->name('get-columns');
        Route::get('export-users', [User\ClientController::class, 'exportUsers'])->name('export-users');

        // Products and uploads
        Route::get('products', [ProductController::class, 'getAllProducts']);
        Route::delete('products', [ProductController::class, 'deleteBulkProducts']);
        Route::post('product', [ProductController::class, 'productCreate']);
        Route::get('product/{productId}', [ProductController::class, 'getProduct']);
        Route::patch('product/{productId}', [ProductController::class, 'updateProduct']);
        Route::get('product/{productId}/plugins', [ProductPluginController::class, 'index']);
        Route::post('product/{productId}/plugins', [ProductPluginController::class, 'sync']);
        Route::get('product/uploads/{productId}', [ProductController::class, 'getProductUploads']);
        Route::get('product/upload/{productUploadId}', [ProductController::class, 'getProductUpload']);
        Route::patch('product/upload/{productUploadId}', [ProductController::class, 'updateProductUpload']);
        Route::post('product/upload/{productId}/', [ProductController::class, 'productUploadCreate']);
        Route::post('product/upload-build/apply', [ProductController::class, 'applyBuildToProducts']);
        Route::delete('product/upload', [ProductController::class, 'deleteBulkProductUploads']);
        Route::get('product/download/{id}/{release?}', [ProductController::class, 'adminDownload']);
        Route::post('get-price', [ProductController::class, 'getPrice']);
        Route::post('chunkupload', [ProductController::class, 'uploadFile']);
        Route::patch('upload/{id}', [ProductController::class, 'uploadUpdate']);

        // Plans
        Route::get('plans', [PlanController::class, 'getAllPlans']);
        Route::post('plans', [PlanController::class, 'planCreate']);
        Route::get('plan/{planId}', [PlanController::class, 'getPlan']);
        Route::patch('plan/{planId}', [PlanController::class, 'updatePlan']);
        Route::delete('plans', [PlanController::class, 'deleteBulkPlans']);

        // Product groups
        Route::get('groups', [GroupController::class, 'getProductGroups']);
        Route::get('group/{group_id}', [GroupController::class, 'getGroup']);
        Route::patch('group/{group_id}', [GroupController::class, 'updateGroup']);
        Route::post('group', [GroupController::class, 'groupCreate']);
        Route::delete('group', [GroupController::class, 'deleteBulkGroups']);

        // Orders and licences
        Route::get('orders', [OrderController::class, 'getOrders']);
        Route::delete('orders', [OrderController::class, 'deleteBulkOrders']);
        Route::get('order/{id}', [OrderController::class, 'getOrder']);
        Route::get('getOrderPayments/{orderId}', [OrderController::class, 'getPaymentByOrderId']);
        Route::get('getOrderInvoices/{orderId}', [OrderController::class, 'getOrderInvoices']);
        Route::get('get-installation-details/{orderId}', [OrderController::class, 'getInstallationDetails']);
        Route::get('export-orders', [OrderController::class, 'exportOrders'])->name('export-orders');
        Route::post('update-license-details', [BaseOrderController::class, 'updateLicenseDetails']);
        Route::post('admin/renew/{id}', [RenewController::class, 'renew']); // extends dates immediately
        Route::post('switch-license-mode', [LocalizedLicenseController::class, 'chooseLicenseMode']);
        Route::get('LocalizedLicense/downloadLicense/{orderNo}/{productId?}', [LocalizedLicenseController::class, 'downloadFileAdmin']);
        Route::get('localized-license/orders', [LocalizedLicenseController::class, 'listFileModeOrders']);
        Route::post('localized-license/bulk-disable', [LocalizedLicenseController::class, 'bulkDisableLicenseMode']);

        // Invoices and payments
        Route::get('invoices', [InvoiceController::class, 'getInvoices']);
        Route::delete('invoices', [InvoiceController::class, 'deleteBulkInvoices']);
        Route::post('invoices/{id}/execute', [InvoiceController::class, 'executeInvoice']);
        Route::post('generate/invoice/{user_id?}', [InvoiceController::class, 'invoiceGenerateByForm']);
        Route::get('export-invoices', [InvoiceController::class, 'exportInvoices'])->name('export-invoices');
        Route::delete('payments', [InvoiceController::class, 'deleteBulkPayments']);
        Route::get('newPayment/receive', [InvoiceController::class, 'newPayment']);
        Route::post('newPayment/receive/{clientid}', [InvoiceController::class, 'postNewPayment']);
        Route::post('newMultiplePayment/receive/{clientid}', [InvoiceController::class, 'postNewMultiplePayment']);
        Route::post('newMultiplePayment/update/{clientid}', [InvoiceController::class, 'updateNewMultiplePayment']);
        Route::get('payments/{id}/edit', [InvoiceController::class, 'paymentEditById']);
        Route::post('payments/{id}/apply', [InvoiceController::class, 'applyPaymentToInvoices']);
        Route::get('pay/list', [OpenPaymentController::class, 'listOrders'])->name('open-payment.list'); // open-payment orders

        // Promotions
        Route::get('promotions', [PromotionController::class, 'getAllPromotions']);
        Route::get('promotion/{promotionId}', [PromotionController::class, 'getPromotion']);
        Route::get('getPromotionCode', [PromotionController::class, 'getCode']);
        Route::patch('updatePromotion/{promotionId}', [PromotionController::class, 'updatePromotionCode']);
        Route::post('promotionCreate', [PromotionController::class, 'promotionCodeCreate']);
        Route::delete('promotions', [PromotionController::class, 'deleteBulkPromotions']);

        // Tax and currency
        Route::get('tax-options', [TaxController::class, 'getTaxOptionsApi']);
        Route::post('taxes/option', [TaxController::class, 'saveTaxOptionSetting'])->name('taxes/option');
        Route::get('tax-tables', [TaxController::class, 'getTax']);
        Route::get('tax/edit/{id}', [TaxController::class, 'editTaxApi']);
        Route::put('tax/{id}', [TaxController::class, 'updateTaxApi']);
        Route::post('create/tax-class', [TaxController::class, 'saveTaxClassSettingApi']);
        Route::delete('tax/delete', [TaxController::class, 'deleteTax']);
        Route::prefix('currency')->group(function (): void {
            Route::get('list', [CurrencyController::class, 'getCurrencyList']);
            Route::post('update-currency', [CurrencyController::class, 'updatecurrency']);
            Route::post('dashboard-currency/{id}', [CurrencyController::class, 'setDashboardCurrency']);
            Route::post('default-currency/{id}', [CurrencyController::class, 'setDefaultCurrency']);
        });

        // Licence types and permissions
        Route::get('get-license-type', [LicenseSettingsController::class, 'getLicenseTypes'])->name('get-license-type');
        Route::get('get-license-type/{id}', [LicenseSettingsController::class, 'getLicenseTypeById']);
        Route::post('create-license-type', [LicenseSettingsController::class, 'createLicense']);
        Route::put('update-license-type/{id}', [LicenseSettingsController::class, 'updateLicense']);
        Route::delete('delete-license-type', [LicenseSettingsController::class, 'deleteLicense'])->name('license-type-delete');
        Route::get('get-license-permission', [LicensePermissionsController::class, 'getPermissions'])->name('get-license-permission');
        Route::post('add-permission', [LicensePermissionsController::class, 'addPermission'])->name('add-permission');

        // CMS: pages, widgets, chat scripts, social media, editor uploads
        Route::get('pages', [PageController::class, 'getAllPages']);
        Route::delete('pages', [PageController::class, 'deleteBulkPages']);
        Route::post('page', [PageController::class, 'createPage']);
        Route::get('page/{id}', [PageController::class, 'getPage']);
        Route::put('page/{id}', [PageController::class, 'updatePage']);
        Route::get('page-settings', [PageController::class, 'getPageSettings']);
        Route::post('page-settings', [PageController::class, 'savePageSettings']);
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
        Route::prefix('social-media')->group(function (): void {
            Route::get('list', [SocialMediaController::class, 'getSocialList']);
            Route::get('show/{id}', [SocialMediaController::class, 'getSocialMedia']);
            Route::post('create', [SocialMediaController::class, 'createSocialMedia']);
            Route::patch('update/{id}', [SocialMediaController::class, 'updateSocial']);
            Route::delete('delete', [SocialMediaController::class, 'deleteSocialMedia']);
        });
        Route::post('editor-image-upload', [FileManagerController::class, 'uploadEditorImage']);

        // SEO and auth-page branding (POST, not PATCH: they take multipart image uploads)
        Route::prefix('seo')->group(function (): void {
            Route::get('default-pages', [SeoDefaultPageController::class, 'index']);
            Route::get('default-pages/{pageKey}', [SeoDefaultPageController::class, 'show']);
            Route::patch('default-pages/{pageKey}', [SeoDefaultPageController::class, 'update']);
            Route::get('settings', [SeoSettingsController::class, 'show']);
            Route::post('settings', [SeoSettingsController::class, 'update']);
        });
        Route::get('auth-brand', [AuthBrandController::class, 'show']);
        Route::post('auth-brand', [AuthBrandController::class, 'update']);

        // Email templates
        Route::prefix('template')->group(function (): void {
            Route::get('list', [TemplateController::class, 'getTemplates']);
            Route::get('edit/{id}', [TemplateController::class, 'showTemplate']);
            Route::put('update/{id}', [TemplateController::class, 'updateTemplate']);
        });

        // Settings: system, email, cron
        Route::get('settings/system-data', [SettingsController::class, 'getSystemSettingsData']);
        Route::get('settings/index-data', [SettingsController::class, 'getSettingsIndexData']);
        Route::patch('settings/system-data', [SettingsController::class, 'updateSystemSettingsData']);
        Route::patch('settings/datetime-data', [SettingsController::class, 'updateDateTimeSettingsData']);
        Route::get('settings/template', [SettingsController::class, 'settingsTemplate']);
        Route::patch('settings/template', [SettingsController::class, 'postSettingsTemplate']);
        Route::get('settings/error', [SettingsController::class, 'getErrorSettings']);
        Route::patch('settings/error', [SettingsController::class, 'postSettingsError']);
        Route::get('settings/email', [EmailSettingsController::class, 'settingsEmail']);
        Route::patch('settings/email', [EmailSettingsController::class, 'postSettingsEmail']);
        Route::post('email-settings-save', [SettingsController::class, 'emailSettingsSave']);
        Route::get('settings/email-validation', [SettingsController::class, 'getEmailValidationSettings']);
        Route::get('settings/email-validation-logs', [SettingsController::class, 'listEmailValidationLogs']);
        Route::get('get-email-validation-results', [SettingsController::class, 'getEmailValidationResults']);
        Route::get('settings/cron-data', [SettingsController::class, 'getCronSettingsData']);
        Route::patch('settings/cron-data', [SettingsController::class, 'updateCronSettingsData']);
        Route::patch('settings/cron-days', [SettingsController::class, 'updateCronDaysData']);
        Route::post('verify-php-path', [SettingsController::class, 'checkPHPExecutablePath'])->name('verify-cron');

        // Settings: integrations (Pipedrive, MSG91, GitHub)
        Route::get('settings/pipedrive', [SettingsController::class, 'getPipedriveSettings']);
        Route::patch('settings/pipedrive', [SettingsController::class, 'updatePipedriveSettings']);
        Route::post('settings/pipedrive/disconnect', [SettingsController::class, 'disconnectPipedrive']);
        Route::get('getPipedriveFields/{group_id}', [PipedriveController::class, 'getLocalFields']);
        Route::get('pipedrive/mapping/{group_id}', [PipedriveController::class, 'getMapFields']);
        Route::post('sync/pipedrive', [PipedriveController::class, 'mappingFields']);
        Route::post('syncing/pipedriveFields', [PipedriveController::class, 'syncFields']);
        Route::post('pipedrive/get-dropdown', [PipedriveController::class, 'getDropdown']);
        Route::post('pipedrive/test', [PipedriveController::class, 'sendTest']);
        Route::get('settings/msg91', [SettingsController::class, 'getMsg91Settings']);
        Route::post('mobile-settings-save', [SettingsController::class, 'mobileSettingsSave']);
        Route::get('settings/mobile-validation', [SettingsController::class, 'getMobileValidationSettings']);
        Route::post('updatemobileDetails', [BaseSettingsController::class, 'updateMobileDetails'])->name('updatemobileDetails');
        Route::get('settings/github', [SettingsController::class, 'getGithubSettings']);
        Route::post('github-setting', [GithubController::class, 'postSettings']);

        // Settings: other
        Route::get('settings/terms', [SettingsController::class, 'getTermsSettings']);
        Route::post('updateTermsDetails', [BaseSettingsController::class, 'updateTermsDetails'])->name('updateTermsDetails');
        Route::get('settings/deployment', [SettingsController::class, 'getDeploymentSettings'])->name('getDeploymentSettings');
        Route::post('settings/deployment', [SettingsController::class, 'saveDeploymentSettings'])->name('saveDeploymentSettings');
        Route::get('settings/cloud-details', [SettingsController::class, 'getCloudDetails']);
        Route::post('licenseStatus', [SettingsController::class, 'licenseStatus'])->name('licenseStatus');
        Route::get('contact-option', [SettingsController::class, 'contactOption'])->name('contact-option');
        Route::post('verificationSettings', [SettingsController::class, 'postContactOption']);
        Route::get('system-managers', [SystemManagerController::class, 'getSystemManagers'])->name('system-managers');
        Route::get('search-admins', [SystemManagerController::class, 'searchAdmin'])->name('search-admins');
        Route::post('updateSystemManager', [SystemManagerController::class, 'updateManagerSettings']);
        Route::get('file-storage', [SettingsController::class, 'showFileStorage']);
        Route::post('file-storage-path', [SettingsController::class, 'updateStoragePath']);
        Route::get('pdf-settings', [SettingsController::class, 'showPdfSettings']);
        Route::post('pdf-settings', [SettingsController::class, 'updatePdfSettings']);
        Route::get('module-settings', [SettingsController::class, 'getModuleSettings']);
        Route::get('debugg', [SettingsController::class, 'debugSettings']);
        Route::post('save/debugg', [SettingsController::class, 'postdebugSettings']);

        // Languages
        Route::get('languages', [LanguageController::class, 'viewLanguage']);
        Route::post('language-toggle', [LanguageController::class, 'toggleLanguageStatus']);
        Route::post('language-set-default', [LanguageController::class, 'setDefaultLanguage']);

        // Payment gateways and social logins
        Route::get('payment-gateway-list', [PaymentSettingsController::class, 'getPaymentGatewayList']);
        Route::post('updatePaymentStatus', [PaymentSettingsController::class, 'updatePaymentStatus']);
        Route::get('social-logins', [SocialLoginsController::class, 'getSocialLogin']);
        Route::get('edit/SocialLogins/{id}', [SocialLoginsController::class, 'editSocialLogin']);
        Route::post('update-social-login', [SocialLoginsController::class, 'updateSocialLogin'])->name('update-social-login');

        // Third-party apps
        Route::get('get-third-party-app', [ThirdPartyAppController::class, 'getThirdPartyDetails'])->name('get-third-party-app');
        Route::post('third-party-app-create', [ThirdPartyAppController::class, 'createThirdPartyApp'])->name('third-party-app-create');
        Route::put('third-party-app-update/{id}', [ThirdPartyAppController::class, 'updateThirdPartyApp'])->name('third-party-app-update');
        Route::get('get-app-key', [ThirdPartyAppController::class, 'getAppKey'])->name('get-app-key');
        Route::delete('third-party-delete', [ThirdPartyAppController::class, 'deleteThirdPartyApp'])->name('third-party-delete');

        // Cloud / tenants
        Route::get('get-tenants', [TenantController::class, 'getTenants'])->name('get-tenants');
        Route::delete('delete-tenant', [TenantController::class, 'destroyTenant'])->name('delete-tenant');
        Route::get('export-tenats', [TenantController::class, 'exportTenats'])->name('export-tenats');
        Route::post('cloud-details', [TenantController::class, 'saveCloudDetails'])->name('cloud-details');
        Route::post('cloud-pop-up', [TenantController::class, 'cloudPopUp'])->name('cloud-pop-up');
        Route::post('cloud-product-store', [TenantController::class, 'cloudProductStore'])->name('cloud-product-store');
        Route::post('enable/cloud', [TenantController::class, 'enableCloud'])->name('enable-cloud');
        Route::get('fetch-data', [CloudExtraActivities::class, 'fetchData'])->name('fetch-data');
        Route::post('update-trial-status', [CloudExtraActivities::class, 'updateTrialStatus'])->name('update-trial-status');
        Route::delete('delete-cloud-product', [CloudExtraActivities::class, 'DeleteProductConfig'])->name('delete-cloud-product');
        Route::delete('remove-location', [CloudExtraActivities::class, 'removeLocation'])->name('remove-location');
        Route::post('cloud-data-center-store', [CloudExtraActivities::class, 'storeCloudDataCenter'])->name('cloud-data-center-store');

        // WhatsApp integration
        Route::get('whatsapp-integration-info', [WhatsappController::class, 'whatsappIntegration']);
        Route::post('whatsapp-integration-save', [WhatsappController::class, 'whatsappSave']);
        Route::get('whatsapp-users-api', [WhatsappController::class, 'whatsappUsersApi']);

        // Reports and logs
        Route::get('reports', [ReportController::class, 'getAllReports']);
        Route::delete('reports', [ReportController::class, 'deleteBulkReports']);
        Route::get('reports/setting', [ReportController::class, 'getReportsSettings']);
        Route::patch('reports/setting', [ReportController::class, 'updateReportsSettings']);
        Route::get('download-exported-file/{id}', [User\ClientController::class, 'downloadExportedFile'])->name('download.exported.file');
        Route::get('get-activity-api', [SettingsController::class, 'getActivityApi']);
        Route::get('get-activity-filters', [SettingsController::class, 'getActivityFilters']);
        Route::get('get-payment-log-api', [SettingsController::class, 'getPaymentLogApi']);
        Route::delete('paymentlog-delete', [SettingsController::class, 'destroyPayment'])->name('paymentlog-delete');
        Route::get('getMsgReports', [MSG91Controller::class, 'getMsg91Reports']);
        Route::get('getMsgFilters', [MSG91Controller::class, 'getMsgFilters']);

        // Queues, cache, monitoring
        Route::get('queue/list', [QueueController::class, 'getQueueData']); // before queue/{id}
        Route::get('queue/{id}', [QueueController::class, 'edit'])->name('queue.edit');
        Route::patch('queue/{id}', [QueueController::class, 'update'])->name('queue.update');
        Route::post('queue/{queue}/activate', [QueueController::class, 'activate']);
        Route::get('queue/{id}/form', [QueueController::class, 'getFormById'])->name('queue.form');
        Route::get('cache-settings/list', [CacheSettingsController::class, 'getDriverData']);
        Route::get('cache-settings/{driver}/form', [CacheSettingsController::class, 'getFormByDriver']);
        Route::patch('cache-settings/{driver}', [CacheSettingsController::class, 'update']);
        Route::post('cache-settings/{driver}/activate', [CacheSettingsController::class, 'activate']);
        Route::get('monitoring/check', [MonitoringController::class, 'checkPulseHorizon'])->name('monitoring.check');
    });

    // ==========================================================
    // 7. MACHINE — called by browsers/services, not people
    // ==========================================================

    Route::post('api/csp-report', [ApiController::class, 'logCSP']);

    // MSG91 delivery reports: api middleware instead of web (no session/CSRF)
    Route::post('api/msg91/reports/{app_key}/{app_secret}', [MSG91Controller::class, 'handleReports'])
        ->withoutMiddleware('web')->middleware('api');

    // ==========================================================
    // 8. SPA PAGES — must stay last
    // ==========================================================

    // Admin SPA: every page needs an admin, so guests never get the shell.
    Route::get('admin/{any?}', fn (): Factory|\Illuminate\Contracts\View\View => view('admin'))
        ->middleware(['auth', 'admin'])
        ->where('any', '.*');

    // Client SPA pages that need login — the server redirects to /login instead of
    // the Vue guard bouncing after load. Keep in sync with clientRouter.js
    // (routes without requiresAuth:false).
    $clientShell = fn (): Factory|\Illuminate\Contracts\View\View => view('client');
    // Root: signed-in clients land on their dashboard, guests (coming from the
    // marketing site) land on the store.
    Route::get('/', fn () => redirect(auth()->check() ? 'client-dashboard' : 'store'));
    Route::middleware('auth')->group(function () use ($clientShell): void {
        Route::get('client-dashboard', $clientShell);
        Route::get('my-orders', $clientShell);
        Route::get('my-order/{id}', $clientShell);
        Route::get('my-invoices', $clientShell);
        Route::get('my-invoice/{id}', $clientShell);
        Route::get('my-profile', $clientShell);
        Route::get('my-profile/change-password', $clientShell);
        Route::get('my-profile/2fa', $clientShell);
        Route::get('account-setup', $clientShell);
        Route::get('checkout', $clientShell);
        Route::get('place-order', $clientShell);
        Route::get('payment-success', $clientShell);
    });

    // A page with an external URL redirects there instead of rendering a copy.
    Route::get('pages/{slug}', [PageController::class, 'showPage']);

    // Every other URL (login, store, pay, verify, ...) gets the client SPA;
    // clientRouter.js handles guest vs signed-in from there.
    Route::fallback($clientShell);
});
