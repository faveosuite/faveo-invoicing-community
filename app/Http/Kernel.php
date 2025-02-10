<?php

namespace App\Http;

use App\Http\Middleware\SecurityEnforcer;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        // \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        // \Voerro\Laravel\VisitorTracker\Middleware\RecordVisits::class,
        // \Torann\Currency\Middleware\CurrencyMiddleware::class,
        // \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        // \App\Http\Middleware\LanguageMiddleware::class,
        SecurityEnforcer::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            // \App\Http\Middleware\Install::class,
            // \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,

            \Spatie\Referer\CaptureReferer::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            // \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\LanguageMiddleware::class,

        ],
        'installer' => [
            \App\Http\Middleware\LanguageMiddleware::class,
        ],
        'admin' => [\App\Http\Middleware\Admin::class],
        'guest' => [\App\Http\Middleware\RedirectIfAuthenticated::class],
        'auth' => [\Illuminate\Auth\Middleware\Authenticate::class],
        'auth.basic' => [\Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class],
        'installAgora' => [\App\Http\Middleware\Install::class],
        'isInstalled' => [\App\Http\Middleware\IsInstalled::class],
        'validateThirdParty' => [\App\Http\Middleware\VerifyThirdPartyApps::class],
        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'installAgora' => \App\Http\Middleware\Install::class,
        'isInstalled' => \App\Http\Middleware\IsInstalled::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        '2fa' => \PragmaRX\Google2FALaravel\Middleware::class,

    ];
}
