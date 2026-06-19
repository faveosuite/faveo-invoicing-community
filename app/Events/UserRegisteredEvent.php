<?php

declare(strict_types=1);

namespace App\Events;

use App\User;

class UserRegisteredEvent
{
    public function __construct(
        public readonly User $user,
        public readonly string $trigger = 'register'
    ) {}
}
