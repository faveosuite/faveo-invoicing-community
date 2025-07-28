<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class QueryListenerProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        \DB::listen(function ($query) {
            \Clockwork::info($query->sql, [$query->time]);
        });

        $this->app['router']->aliasMiddleware('clockwork', ClockworkMiddleware::class);
    }
}
