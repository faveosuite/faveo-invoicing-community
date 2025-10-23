<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Facades\Cart;

class CartServiceProvider extends ServiceProvider
{
    public function register():void
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