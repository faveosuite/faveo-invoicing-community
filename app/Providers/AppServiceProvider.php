<?php

namespace App\Providers;

use App\Events\UserOrderDelete;
use App\Helper\PdfManager\FaveoBrowserShot;
use App\Listeners\CloudDeletion;
use App\Services\NewsletterManager;
use File;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Override;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Validator::extend('no_http', fn ($attribute, $value, $parameters, $validator): bool => ! str_contains((string) $value, 'http://') && ! str_contains((string) $value, 'https://'));

        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page'): \Illuminate\Pagination\LengthAwarePaginator {
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

        FaveoBrowserShot::bootForLaravelPdf();
    }

    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        $this->app->singleton(NewsletterManager::class);
    }

    /**
     * Register custom file macros for session management.
     */
    public function fileMacros(): void
    {
        // Clean directory except specified files and folders
        File::macro('cleanDirectoryFiles', function (
            string $directory,
            array $excludedFiles = [],
            array $excludedFolders = []
        ): void {
            if (! File::isDirectory($directory)) {
                return;
            }

            $excludedFiles = array_map(basename(...), $excludedFiles);
            $excludedFolders = array_map(basename(...), $excludedFolders);

            // Remove files
            foreach (File::files($directory) as $file) {
                if (! in_array($file->getFilename(), $excludedFiles, strict: true)) {
                    File::delete($file->getPathname());
                }
            }

            // Remove directories
            foreach (File::directories($directory) as $folder) {
                if (! in_array(basename($folder), $excludedFolders, strict: true)) {
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

            return $unserialize ? @unserialize($content) : $content; // nosemgrep: php.lang.security.unserialize-use.unserialize-use
        });
    }
}
