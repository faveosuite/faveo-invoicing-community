<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Events;

use App\Events\UserRegisteredEvent;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserRegisteredEventTest extends TestCase
{
    use DatabaseTransactions;

    public function test_stores_user_and_default_register_trigger(): void
    {
        $user = User::factory()->create();

        $event = new UserRegisteredEvent($user);

        $this->assertSame($user->id, $event->user->id);
        $this->assertSame('register', $event->trigger);
    }

    public function test_stores_custom_trigger(): void
    {
        $user  = User::factory()->create();
        $event = new UserRegisteredEvent($user, 'verify');

        $this->assertSame('verify', $event->trigger);
    }

    public function test_stores_admin_create_trigger(): void
    {
        $user  = User::factory()->create();
        $event = new UserRegisteredEvent($user, 'admin_create');

        $this->assertSame('admin_create', $event->trigger);
    }

    public function test_user_and_trigger_are_readonly(): void
    {
        $user  = User::factory()->create();
        $event = new UserRegisteredEvent($user, 'register');

        try {
            // @phpstan-ignore-next-line
            $event->trigger = 'admin_create'; // @phpstan-ignore assign.propertyReadOnly
            $this->fail('Expected Error for readonly property reassignment');
        } catch (\Error) {
            $this->assertTrue(true);
        }
    }

    
}
