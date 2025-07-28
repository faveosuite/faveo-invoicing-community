<?php

namespace App\Plugins\Razorpay;

class ServiceProvider extends \App\Plugins\ServiceProvider
{
    public function register()
    {
        parent::register('Razorpay');
    }

    public function boot()
    {
        parent::boot('Razorpay');
    }
}
