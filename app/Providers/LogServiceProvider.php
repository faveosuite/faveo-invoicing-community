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
     */
    #[Override]
    public function register(): void
    {
        App::bind('Log', fn (): \App\BillingLog\Controllers\LogWriteController => new LogWriteController);
    }

    /**
     * @return array<mixed>
     */
    #[Override]
    public function provides(): array
    {
        return ['Log'];
    }
}
