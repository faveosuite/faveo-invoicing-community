<?php

namespace CacheDriver\Providers;

use Carbon\Laravel\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([__DIR__.'/../../config/cache.php' => config_path('cache.php')], 'advance-cache');
    }
}