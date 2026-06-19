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
        $this->app->bind('attachment-helper', fn (): \App\Helper\AttachmentHelper => new AttachmentHelper());
    }

    /**
     * @return array<mixed>
     */
    #[Override]
    public function provides(): array
    {
        return ['attachment-helper'];
    }
}
