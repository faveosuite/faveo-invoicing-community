<?php

namespace App\Listeners;

use App\Http\Controllers\Front\Cart\CartService;
use Illuminate\Auth\Events\Login;

class MergeGuestCartOnLogin
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    /**
     * When a user logs in, fold any cart they built as a guest (held in the
     * session) into their DB cart. mergeGuestCart() clears the session cart.
     */
    public function handle(Login $event): void
    {
        $this->cartService->mergeGuestCart($event->user);
    }
}
