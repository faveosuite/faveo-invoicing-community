<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
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
        Route::middleware('web')
             ->group(base_path('routes/web.php'));
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
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Web Rate Limiting
        RateLimiter::for('web', function (Request $request) {
            $maxAttempts = 60;
            $limits = [];

            $customResponse = function ($request) {
                if (request()->expectsJson()) {
                    return errorResponse(__('message.too_many_attempts'), 429);
                }
                abort(429);
            };

            if ($ip = $request->ip()) {
                $limits[] = Limit::perMinute($maxAttempts)
                    ->by("web:ip:{$ip}")
                    ->response($customResponse);
            }

            if ($userId = $request->user()?->id) {
                $limits[] = Limit::perMinute($maxAttempts)
                    ->by("web:user:{$userId}")
                    ->response($customResponse);
            }

            if ($sessionId = $request->session()->getId()) {
                $limits[] = Limit::perMinute($maxAttempts)
                    ->by("web:session:{$sessionId}")
                    ->response($customResponse);
            }

            return $limits;
        });
    }

    protected function installer()
    {
        Route::middleware('isInstalled')
            ->namespace($this->namespace)
            ->group(base_path('routes/installer.php'));
    }
}
