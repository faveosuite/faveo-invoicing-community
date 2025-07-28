<?php

namespace App\Providers;

use App\Helper\AttachmentHelper;
use Illuminate\Support\ServiceProvider;

class AttachmentHelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('attachment-helper', function () {
            return new AttachmentHelper();
        });
    }

    public function provides()
    {
        return ['attachment-helper'];
    }
}
