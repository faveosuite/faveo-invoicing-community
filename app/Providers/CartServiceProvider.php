<?php

namespace App\Providers;

use App\Facades\Cart;
use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('user-cart', function () {
            return new Cart();
        });
    }

    public function provides()
    {
        return ['user-cart'];
    }
}
