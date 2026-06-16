<?php

namespace App\Providers;

use Clockwork;
use DB;
use Illuminate\Support\ServiceProvider;
use Override;

class QueryListenerProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    #[Override]
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
        DB::listen(function ($query): void {
            Clockwork::info($query->sql, [$query->time]);
        });

        $this->app['router']->aliasMiddleware('clockwork', ClockworkMiddleware::class);
    }
}
