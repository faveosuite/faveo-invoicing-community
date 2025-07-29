<?php

namespace App\Providers;

use App;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class LogServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        App::bind('Log', function () {
            return new App\BillingLog\Controllers\LogWriteController;
        });
    }

    public function provides(): array
    {
        return ['Log'];
    }
}
