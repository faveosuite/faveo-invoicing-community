<?php

namespace App\Plugins\Stripe;

use Override;

class ServiceProvider extends \App\Plugins\ServiceProvider
{
    #[Override]
    public function register()
    {
        parent::register('Stripe');
    }

    #[Override]
    public function boot()
    {
        parent::boot('Stripe');
    }
}
