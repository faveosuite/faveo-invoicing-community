<?php

namespace App\Providers;

use App\Helper\ImageUploadHelper;
use Illuminate\Support\ServiceProvider;
use Override;

class ImageUploadHelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    #[Override]
    public function register(): void
    {
        $this->app->bind('ImageUpload-helper', fn (): \App\Helper\ImageUploadHelper => new ImageUploadHelper());
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
