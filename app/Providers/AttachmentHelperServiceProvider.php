<?php

namespace App\Providers;

use App\Helper\AttachmentHelper;
use Illuminate\Support\ServiceProvider;
use Override;

class AttachmentHelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    #[Override]
    public function register(): void
    {
        $this->app->bind('attachment-helper', fn () => new AttachmentHelper());
    }

    #[Override]
    public function provides()
    {
        return ['attachment-helper'];
    }
}
