<?php

declare(strict_types=1);

namespace App\Events;

use App\User;
use Logger;
use Throwable;

class UserRegisteredEvent
{
    public function __construct(
        public readonly User $user,
        public readonly string $trigger = 'register'
    ) {
    }

    /**
     * Dispatch the event without letting an external-sync failure escape.
     *
     * The listeners push the user to third-party services (Mailchimp, Zoho, Pipedrive). On the
     * `sync` queue driver they run inside the HTTP request, so an API error from one of them
     * would otherwise bubble into the registration / verification handler and turn an already
     * completed step into an error for the user. The failure is logged and the caller continues.
     */
    public static function dispatchSafely(User $user, string $trigger = 'register'): void
    {
        try {
            event(new self($user, $trigger));
        } catch (Throwable $exception) {
            Logger::exception($exception);
        }
    }
}
