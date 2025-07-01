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
        // Clean directory except specified files and folders
        File::macro('cleanDirectoryFiles', function (
            string $directory,
            array $excludedFiles = [],
            array $excludedFolders = []
        ) {
            if (! File::isDirectory($directory)) {
                return;
            }

            $excludedFiles = array_map('basename', $excludedFiles);
            $excludedFolders = array_map('basename', $excludedFolders);

            // Remove files
            foreach (File::files($directory) as $file) {
                if (! in_array($file->getFilename(), $excludedFiles, true)) {
                    File::delete($file->getPathname());
                }
            }

            // Remove directories
            foreach (File::directories($directory) as $folder) {
                if (! in_array(basename($folder), $excludedFolders, true)) {
                    File::deleteDirectory($folder);
                }
            }
        });

        // Filter files based on callback condition
        File::macro('filterFiles', function (string $directory, callable $callback) {
            if (! File::isDirectory($directory)) {
                return collect();
            }

            return collect(File::files($directory))->filter($callback);
        });

        // Get file data safely with optional unserialization
        File::macro('safeGet', function (string $filePath, bool $unserialize = false) {
            if (! File::exists($filePath)) {
                return null;
            }

            $content = @File::get($filePath);

            return $unserialize ? @unserialize($content) : $content;
        });
    }
}
