<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Jobs;

use App\Events\UserRegisteredEvent;
use App\Jobs\AddUserToExternalService;
use App\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AddUserToExternalServiceTest extends TestCase
{
    use DatabaseTransactions;

    // --- Contract ---

    public function test_implements_should_queue(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(ShouldQueue::class, new AddUserToExternalService($user));
    }

    // --- Constructor: trigger normalisation ---

    public function test_string_trigger_is_passed_to_event(): void
    {
        // Verify 'register' trigger reaches the event — tests storage indirectly via behaviour
        Event::fake();
        $user = User::factory()->create();

        (new AddUserToExternalService($user, 'register'))->handle();

        Event::assertDispatched(UserRegisteredEvent::class, fn (UserRegisteredEvent $e): bool => $e->trigger === 'register'
        );
    }

    public function test_boolean_false_trigger_normalised_to_admin_create(): void
    {
        // false was the old default — must be treated as 'admin_create' so newsletters don't fire
        Event::fake();
        $user = User::factory()->create();

        (new AddUserToExternalService($user, false))->handle();

        Event::assertDispatched(UserRegisteredEvent::class, fn (UserRegisteredEvent $e): bool => $e->trigger === 'admin_create'
        );
    }

    public function test_default_trigger_is_register(): void
    {
        Event::fake();
        $user = User::factory()->create();

        (new AddUserToExternalService($user))->handle();

        Event::assertDispatched(UserRegisteredEvent::class, fn (UserRegisteredEvent $e): bool => $e->trigger === 'register'
        );
    }

    // --- handle(): fires UserRegisteredEvent with correct args ---

    public function test_handle_fires_user_registered_event(): void
    {
        Event::fake();
        $user = User::factory()->create();
        $job = new AddUserToExternalService($user, 'register');

        $job->handle();

        Event::assertDispatched(UserRegisteredEvent::class, function (UserRegisteredEvent $event) use ($user): bool {
            return $event->user->id === $user->id && $event->trigger === 'register';
        });
    }

    public function test_handle_passes_admin_create_trigger_to_event(): void
    {
        Event::fake();
        $user = User::factory()->create();

        (new AddUserToExternalService($user, false))->handle();

        Event::assertDispatched(UserRegisteredEvent::class, fn (UserRegisteredEvent $e): bool => $e->trigger === 'admin_create'
        );
    }

    public function test_handle_called_twice_dispatches_event_twice(): void
    {
        Event::fake();
        $user = User::factory()->create();
        $job = new AddUserToExternalService($user, 'register');

        $job->handle();
        $job->handle();

        // Job has no idempotency guard — both calls fire the event
        Event::assertDispatchedTimes(UserRegisteredEvent::class, 2);
    }
}
