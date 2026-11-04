<?php

namespace App\Listeners;

use App\User;
use Illuminate\Auth\Events\Login;

/**
 * Activity log entry for every completed login (password, 2FA, social): Laravel fires
 * Login only once the user is actually signed in, so no path can forget to log it.
 */
class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        if (! $user instanceof User) {
            return;
        }

        $userUrl = url('admin/users/'.$user->id);
        $name = e($user->first_name.' '.$user->last_name);

        logActivity(
            'User <a href="'.e($userUrl).'"><strong>'.$name.'</strong></a> logged in successfully.',
            'login',
            'Authentication',
            $user,
        );
    }
}
