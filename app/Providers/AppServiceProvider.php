<?php

namespace App\Providers;

use App\Events\UserOrderDelete;
use App\Listeners\CloudDeletion;
use File;
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
        $this->fileMacros();
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

    /**
     * Register custom file macros for session management.
     *
     * @return void
     */
    public function fileMacros()
    {
        // Delete specific session files
        File::macro('deleteSessionFiles', function ($filenames) {
            $filenames = collect($filenames)->all();
            $sessionPath = storage_path('framework/sessions');

            foreach ($filenames as $filename) {
                $fullPath = $sessionPath.DIRECTORY_SEPARATOR.$filename;
                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }
            }
        });

        // Get session files of a specific userId, excluding some sessionIds
        File::macro('getUserSessionFiles', function (int $userId, array $exceptSessionIds = []) {
            $sessionPath = storage_path('framework/sessions');

            return collect(File::files($sessionPath))
                ->reject(function ($file) use ($exceptSessionIds) {
                    return $file->getFilename() === '.gitignore' ||
                        in_array($file->getFilename(), $exceptSessionIds, true) ||
                        ! $file->isReadable();
                })
                ->filter(function ($file) use ($userId) {
                    $content = @file_get_contents($file->getRealPath());
                    $sessionData = @unserialize($content);

                    if (! is_array($sessionData)) {
                        return false;
                    }

                    foreach ($sessionData as $key => $value) {
                        if (str_starts_with($key, 'login_web_') && $value == $userId) {
                            return true;
                        }
                    }

                    return false;
                })
                ->map(fn ($file) => $file->getFilename())
                ->values();
        });
    }
}
