<?php

namespace App\Plugins\Stripe;

use Override;

class ServiceProvider extends \App\Plugins\ServiceProvider
{
    #[Override]
    public function register(): void
    {
        parent::register('Stripe');
    }

    #[Override]
    public function boot(): void
    {
        parent::boot('Stripe');
    }
}
