<?php

namespace App\BillingLog;

use App\BillingLog\Console\Commands\DeleteLogs;
use Blade;
use Illuminate\Support\ServiceProvider;

class LaravelLogViewerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        $basePath = app_path('BillingLog');

        $this->loadMigrationsFrom("{$basePath}/database/migrations");
        $this->loadViewsFrom("{$basePath}/views", 'log');
        $this->loadTranslationsFrom("{$basePath}/lang", 'log');

        Blade::component('log::components.dynamic-table', 'log-dynamic-table');

        $this->commands([
            DeleteLogs::class
        ]);

        // Load breadcrumbs if the package exists
        if (class_exists('Breadcrumbs')) {
            require "{$basePath}/breadcrumbs.php";
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $routesFile = app_path('BillingLog/routes.php');
        if (file_exists($routesFile)) {
            require $routesFile;
        }
    }
}
