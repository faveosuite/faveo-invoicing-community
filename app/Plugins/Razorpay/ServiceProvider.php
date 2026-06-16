<?php

namespace App\Plugins\Razorpay;

use Override;

class ServiceProvider extends \App\Plugins\ServiceProvider
{
    #[Override]
    public function register()
    {
        parent::register('Razorpay');
    }

    #[Override]
    public function boot()
    {
        parent::boot('Razorpay');
    }
}
