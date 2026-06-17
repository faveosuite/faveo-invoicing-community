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
     */
    #[Override]
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        DB::listen(function ($query): void {
            Clockwork::info($query->sql, [$query->time]);
        });
    }
}
