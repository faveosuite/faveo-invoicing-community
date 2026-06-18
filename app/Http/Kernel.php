<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Middleware\AddJsonAcceptHeader;
use App\Http\Middleware\Admin;
use App\Http\Middleware\BlockFailedVerifications;
use App\Http\Middleware\CheckPulseEnabled;
use App\Http\Middleware\Install;
use App\Http\Middleware\IsInstalled;
use App\Http\Middleware\LanguageMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SecurityEnforcer;
use App\Http\Middleware\SessionTimeout;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\VerifyThirdPartyApps;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use PragmaRX\Google2FALaravel\Middleware;
use Spatie\Csp\AddCspHeaders;
use Spatie\Referer\CaptureReferer;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     */
    protected $middleware = [
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        // \App\Http\Middleware\TrimStrings::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        // \Voerro\Laravel\VisitorTracker\Middleware\RecordVisits::class,
        // \Torann\Currency\Middleware\CurrencyMiddleware::class,
        // \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        LanguageMiddleware::class,
        SecurityEnforcer::class,
        AddCspHeaders::class,
    ];

    /**
     * The application's route middleware groups.
     */
    protected $middlewareGroups = [
        'web' => [
            'throttle:web',
            // \App\Http\Middleware\Install::class,
            // \App\Http\Middleware\EncryptCookies::class,
            AddQueuedCookiesToResponse::class,

            CaptureReferer::class,
            //\Illuminate\Session\Middleware\AuthenticateSession::class,
            // \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,

        ],
        'installer' => [
            LanguageMiddleware::class,
        ],
        'admin' => [Admin::class],
        'guest' => [RedirectIfAuthenticated::class],
        'auth' => [Authenticate::class],
        'auth.basic' => [AuthenticateWithBasicAuth::class],
        'installAgora' => [Install::class],
        'isInstalled' => [IsInstalled::class],
        'validateThirdParty' => [VerifyThirdPartyApps::class],
        'api' => [
            'throttle:api',
            SubstituteBindings::class,

        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     */
    protected $middlewareAliases = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'throttle' => ThrottleRequests::class,
        'installAgora' => Install::class,
        'isInstalled' => IsInstalled::class,
        'signed' => ValidateSignature::class,
        '2fa' => Middleware::class,
        'pulse.enabled' => CheckPulseEnabled::class,
        'language' => LanguageMiddleware::class,
        'blockFailedVerifications' => BlockFailedVerifications::class,
        'session.timeout' => SessionTimeout::class,
        'force.json' => AddJsonAcceptHeader::class,
        'password.confirm' => RequirePassword::class,
    ];
}
