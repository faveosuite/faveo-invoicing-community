<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Listeners;

use App\Http\Controllers\Front\Cart\CartService;
use App\Listeners\MergeGuestCartOnLogin;
use App\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class MergeGuestCartOnLoginTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // --- handle(): delegates to CartService::mergeGuestCart() ---

    public function test_handle_calls_merge_guest_cart_with_logged_in_user(): void
    {
        $user = User::factory()->create();

        /** @var CartService&MockInterface $cartService */
        $cartService = Mockery::mock(CartService::class);
        $cartService->shouldReceive('mergeGuestCart')
            ->once()
            ->with(Mockery::on(fn ($u): bool => $u->id === $user->id));

        $listener = new MergeGuestCartOnLogin($cartService);
        $listener->handle(new Login('web', $user, false));
        $this->addToAssertionCount(1); // Mockery ->once() verified in tearDown
    }

    // --- handle(): guest cart empty → mergeGuestCart still called (CartService decides) ---

    public function test_handle_always_calls_merge_regardless_of_cart_state(): void
    {
        // MergeGuestCartOnLogin itself has no empty-cart guard — CartService owns that logic.
        $user = User::factory()->create();

        /** @var CartService&MockInterface $cartService */
        $cartService = Mockery::mock(CartService::class);
        $cartService->shouldReceive('mergeGuestCart')->once();

        (new MergeGuestCartOnLogin($cartService))->handle(new Login('web', $user, false));
        $this->addToAssertionCount(1);
    }

    // --- handle() called twice (same login event) → mergeGuestCart called twice ---

    public function test_handle_called_twice_invokes_merge_twice(): void
    {
        // No idempotency guard in the listener — both calls reach CartService
        $user = User::factory()->create();

        /** @var CartService&MockInterface $cartService */
        $cartService = Mockery::mock(CartService::class);
        $cartService->shouldReceive('mergeGuestCart')->twice();

        $listener = new MergeGuestCartOnLogin($cartService);
        $event = new Login('web', $user, false);
        $listener->handle($event);
        $listener->handle($event);
        $this->addToAssertionCount(1);
    }

    // --- Listener receives the right user from the Login event ---

    public function test_handle_passes_event_user_not_a_different_user(): void
    {
        $targetUser = User::factory()->create();
        $otherUser = User::factory()->create();

        /** @var CartService&MockInterface $cartService */
        $cartService = Mockery::mock(CartService::class);
        $cartService->shouldReceive('mergeGuestCart')
            ->once()
            ->with(Mockery::on(fn ($u): bool => $u->id === $targetUser->id && $u->id !== $otherUser->id));

        (new MergeGuestCartOnLogin($cartService))->handle(new Login('web', $targetUser, false));
        $this->addToAssertionCount(1);
    }
}
