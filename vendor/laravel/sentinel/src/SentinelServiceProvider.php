<?php

namespace Laravel\Sentinel;

use Illuminate\Support\ServiceProvider;

class SentinelServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(SentinelManager::class, fn ($app) => new SentinelManager($app));
    }
}
