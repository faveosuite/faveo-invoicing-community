<?php

namespace App\Providers;

use Override;
use App\BillingLog\Controllers\LogWriteController;
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
    #[Override]
    public function register()
    {
        App::bind('Log', fn() => new LogWriteController);
    }

    #[Override]
    public function provides(): array
    {
        return ['Log'];
    }
}
