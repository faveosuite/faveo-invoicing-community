<?php

namespace App\Providers;

use Override;
use App\Helper\ImageUploadHelper;
use Illuminate\Support\ServiceProvider;

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
        $this->app->bind('ImageUpload-helper', fn() => new ImageUploadHelper());
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
