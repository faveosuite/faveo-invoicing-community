<?php

namespace App\Providers;

use Config;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Override;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    #[Override]
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function (): void {
            $this->mapApiRoutes();

            $this->mapWebRoutes();

            $this->mapThirdPartyRoutes();

            $this->installer();

            //
        });
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        $routeConfig = ['namespace' => $this->namespace];

        $middlewares = [];

        if (isV3Api()) {
            $this->setV3ApiConfiguration();
            $routeConfig['prefix'] = 'v3';
            $middlewares[] = 'api';
            $middlewares[] = 'force.json';
        } else {
            $middlewares[] = 'web';
        }

        $routeConfig['middleware'] = $middlewares;

        Route::group($routeConfig, function (): void {
            require base_path('routes/web.php');
        });
    }

    /**
     * Sets up version 3 authentication coonfiguration.
     */
    private function setV3ApiConfiguration(): void
    {
        // if v3 is given, we will set a api guard
        Config::set('auth.defaults.guard', 'api');

        // Since existing APIs uses the same guard, so
        // it cannot be changed manually.
        // creating a new guard is not available in passport for now,
        // overriding their class in much more complicated than simply changing the
        // configuration and run time
        Config::set('auth.guards.api.driver', 'passport');
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "third party" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapThirdPartyRoutes()
    {
        Route::middleware('validateThirdParty')
            ->group(base_path('routes/thirdparty.php'));
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));

        // Verification codes (email/SMS): 10 sends per hour per IP across all accounts, on top of
        // the per-user 60s resend wait (AuthController) and 3-per-10-minutes lockout.
        RateLimiter::for('otp-send', fn (Request $request) => Limit::perHour(10)
            ->by($request->ip())
            ->response(fn (Request $request, array $headers) => errorResponse(
                __('message.too_many_attempts'),
                429,
                ['retry_after' => (int) ($headers['Retry-After'] ?? 0)]
            )));

        // Forgot password: 3 requests per 6 minutes per email + IP (the broker also allows
        // only one new link per email per auth.passwords.users.throttle seconds).
        RateLimiter::for('forgot-password', fn (Request $request) => Limit::perMinutes(6, 3)
            ->by(strtolower((string) $request->input('email')).'|'.$request->ip())
            ->response(fn (Request $request, array $headers) => errorResponse(
                __('message.too_many_forgot_attempts', ['time' => formatDuration((int) ($headers['Retry-After'] ?? 0))]),
                429
            )));

        // Web Rate Limiting
        RateLimiter::for('web', function (Request $request): array {
            $maxAttempts = 600;
            $limits = [];

            $customResponse = function () {
                if (request()->expectsJson()) {
                    return errorResponse(__('message.too_many_attempts'), 429);
                }

                abort(429);
            };

            if ($ip = $request->ip()) {
                $limits[] = Limit::perMinute($maxAttempts)
                    ->by('web:ip:'.$ip)
                    ->response($customResponse);
            }

            if ($userId = $request->user()?->id) {
                $limits[] = Limit::perMinute($maxAttempts)
                    ->by('web:user:'.$userId)
                    ->response($customResponse);
            }

            if ($sessionId = $request->session()->getId()) {
                $limits[] = Limit::perMinute($maxAttempts)
                    ->by('web:session:'.$sessionId)
                    ->response($customResponse);
            }

            return $limits;
        });
    }

    protected function installer(): void
    {
        Route::middleware('isInstalled')
            ->namespace($this->namespace)
            ->group(base_path('routes/installer.php'));
    }
}
