<?php

namespace App\Providers;

use App\Helper\ImageUploadHelper;
use Illuminate\Support\ServiceProvider;

class ImageUploadHelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('ImageUpload-helper', function () {
            return new ImageUploadHelper();
        });
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
