<?php

namespace App\Providers;

use Override;
use App\Facades\Cart;
use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton('user-cart', fn() => new Cart());
    }

    #[Override]
    public function provides()
    {
        return ['user-cart'];
    }
}
