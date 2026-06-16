<?php

namespace App\Providers;

use App\Facades\Cart;
use Illuminate\Support\ServiceProvider;
use Override;

class CartServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton('user-cart', fn () => new Cart());
    }

    #[Override]
    public function provides()
    {
        return ['user-cart'];
    }
}
