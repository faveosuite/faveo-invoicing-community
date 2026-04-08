<?php

namespace App\Modules\License;

use App\Modules\License\Helpers\LicenseValidator;
use App\Modules\License\Services\CallbackService;
use App\Modules\License\Services\InstallationService;
use App\Modules\License\Services\LicenseService;
use App\Modules\License\Services\VersionService;
use Illuminate\Support\ServiceProvider;

class LicenseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LicenseValidator::class, function ($app) {
            return new LicenseValidator();
        });

        $this->app->singleton(LicenseService::class, function ($app) {
            return new LicenseService();
        });

        $this->app->singleton(InstallationService::class, function ($app) {
            return new InstallationService();
        });

        $this->app->singleton(VersionService::class, function ($app) {
            return new VersionService();
        });

        $this->app->singleton(CallbackService::class, function ($app) {
            return new CallbackService(
                $app->make(LicenseService::class),
                $app->make(InstallationService::class),
                $app->make(VersionService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/Routes/license.php');

        // Load migrations from module's Database/Migrations directory
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }
}
