<?php

namespace App\Jobs;

use Illuminate\Foundation\Queue\Queueable;
use App\Events\UserRegisteredEvent;
use App\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddUserToExternalService implements ShouldQueue
{
    use Queueable;

    protected string $trigger;

    public function __construct(protected User $user, string|bool $trigger = 'register')
    {
        // false was the old default — treat it as admin_create so newsletters don't fire
        $this->trigger = is_string($trigger) ? $trigger : 'admin_create';
    }

    public function handle(): void
    {
        event(new UserRegisteredEvent($this->user, $this->trigger));
    }
}
