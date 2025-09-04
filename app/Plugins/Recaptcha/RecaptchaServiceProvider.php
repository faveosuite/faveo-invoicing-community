<?php

namespace App\Plugins\Recaptcha;

use Illuminate\Support\ServiceProvider;

class RecaptchaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load plugin migrations
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        // Publish plugin assets
        $this->publishes([
            __DIR__.'/resources/assets' => public_path('plugins/recaptcha'),
        ], 'recaptcha-assets');

        // Register plugin routes
        $this->loadRoutesFrom(__DIR__.'/routes/routes.php');

        $this->loadViewsFrom(__DIR__.'/resources/views', 'recaptcha');

        // Register plugin middleware if needed
        $this->registerMiddleware();
    }

    /**
     * Register plugin middleware.
     */
    protected function registerMiddleware(): void
    {
        // Register recaptcha middleware
        $this->app['router']->aliasMiddleware('recaptcha', \App\Plugins\Recaptcha\Middleware\RecaptchaMiddleware::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['recaptcha'];
    }
}
