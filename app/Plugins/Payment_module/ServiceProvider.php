<?php

namespace App\Plugins\Payment_module;

class ServiceProvider extends \App\Plugins\ServiceProvider
{
    public function register()
    {
        parent::register('Payment_module');
    }

    public function boot()
    {
        parent::boot('Payment_module');
    }
}
