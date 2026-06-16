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
use App\License\Services\InstallationService;
use App\License\Services\LicenseService;
use App\License\Services\VersionService;
use Illuminate\Support\ServiceProvider;
use Override;

class LicenseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        $this->app->singleton(fn ($app): LicenseValidator => new LicenseValidator());

        $this->app->singleton(fn ($app): LicenseService => new LicenseService());

        $this->app->singleton(fn ($app): InstallationService => new InstallationService());

        $this->app->singleton(fn ($app): VersionService => new VersionService());
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
