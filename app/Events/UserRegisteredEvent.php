<?php

namespace App\Events;

use App\User;

class UserRegisteredEvent
{
    public function __construct(
        public readonly User $user,
        public readonly string $trigger = 'register'
    ) {}
}
