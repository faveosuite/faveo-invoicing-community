<?php

namespace App\Providers;

use App\Helper\ImageUploadHelper;
use Illuminate\Support\ServiceProvider;
use Override;

class ImageUploadHelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    #[Override]
    public function register()
    {
        $this->app->bind('ImageUpload-helper', fn () => new ImageUploadHelper());
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
