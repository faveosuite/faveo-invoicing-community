<?php

namespace App\Providers;

use App;
use App\BillingLog\Controllers\LogWriteController;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Override;

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
        App::bind('Log', fn () => new LogWriteController);
    }

    #[Override]
    public function provides(): array
    {
        return ['Log'];
    }
}
