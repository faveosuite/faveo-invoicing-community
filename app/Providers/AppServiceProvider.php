<?php

namespace App\Providers;

use App\Events\UserOrderDelete;
use App\Listeners\CloudDeletion;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Validator::extend('no_http', function ($attribute, $value, $parameters, $validator) {
            return strpos($value, 'http://') === false && strpos($value, 'https://') === false;
        });

        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            return new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });

        Event::listen(UserOrderDelete::class, CloudDeletion::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->alias('bugsnag.logger', \Illuminate\Contracts\Logging\Log::class);
        $this->app->alias('bugsnag.logger', \Psr\Log\LoggerInterface::class);

        $this->app->bind('\Symfony\Component\Mailer\MailerInterface::class', 'ProviderRepository');

        // $this->app->bind('\Symfony\Component\Mailer\MailerInterface::class',  'Illuminate\Foundation\ProviderRepository::class');
    }
}
