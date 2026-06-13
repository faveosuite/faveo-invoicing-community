<?php

namespace App\Jobs;

use App\Events\UserRegisteredEvent;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddUserToExternalService implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;

    protected string $trigger;

    public function __construct(User $user, string|bool $trigger = 'register')
    {
        $this->user = $user;
        // false was the old default — treat it as admin_create so newsletters don't fire
        $this->trigger = is_string($trigger) ? $trigger : 'admin_create';
    }

    public function handle(): void
    {
        event(new UserRegisteredEvent($this->user, $this->trigger));
    }
}
