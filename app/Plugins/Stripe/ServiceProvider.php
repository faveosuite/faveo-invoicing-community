<?php

namespace App\Plugins\Stripe;

class ServiceProvider extends \App\Plugins\ServiceProvider
{
    public function register()
    {
        parent::register('Stripe');
    }

    public function boot()
    {
        parent::boot('Stripe');
    }
}
