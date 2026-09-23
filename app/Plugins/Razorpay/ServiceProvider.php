<?php

namespace App\Plugins\Razorpay;

use Override;

class ServiceProvider extends \App\Plugins\ServiceProvider
{
    #[Override]
    public function register(): void
    {
        parent::register('Razorpay');
    }

    #[Override]
    public function boot(): void
    {
        parent::boot('Razorpay');
    }
}
