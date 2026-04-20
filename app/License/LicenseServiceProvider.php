<?php

namespace App\License;

use App\License\Console\Commands\CrackCallbackCleanup;
use App\License\Console\Commands\CrackReportsCleanup;
use App\License\Console\Commands\InstallationLogsCommand;
use App\License\Console\Commands\LicenseDataMigration;
use App\License\Console\Commands\LicenseReportsCleanup;
use App\License\Console\Commands\LinkLicenseToPlugin;
use App\License\Console\Commands\SystemReportsCleanup;
use App\License\Console\Commands\VersionsCleanup;
use App\License\Helpers\LicenseValidator;
use App\License\Services\CallbackService;
use App\License\Services\InstallationService;
use App\License\Services\LicenseService;
use App\License\Services\VersionService;
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

        // Load views from module's Resources/Views directory
        $this->loadViewsFrom(__DIR__.'/Resources/Views', 'license');

        // Load translations from module's Lang directory
        $this->loadTranslationsFrom(__DIR__.'/Lang', 'license');

        // Load migrations from module's Database/Migrations directory
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                LicenseDataMigration::class,
                InstallationLogsCommand::class,
                LicenseReportsCleanup::class,
                CrackCallbackCleanup::class,
                CrackReportsCleanup::class,
                SystemReportsCleanup::class,
                VersionsCleanup::class,
                LinkLicenseToPlugin::class,
            ]);
        }
    }
}
