<?php

namespace App\Listeners;

use Throwable;
use Logger;
use App\ApiKey;
use App\Events\UserRegisteredEvent;
use App\Model\Common\StatusSetting;
use App\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

abstract class BaseExternalSyncListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public int $tries = 3;

    public int $backoff = 60;

    abstract protected function serviceKey(): string;

    abstract protected function isEnabled(): bool;

    abstract protected function sync(User $user): void;

    public function handle(UserRegisteredEvent $event): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        if ($this->requiresVerification()) {
            if (! $this->isUserFullyVerified($event->user)) {
                return;
            }
        } else {
            if ($event->trigger !== 'register') {
                return;
            }
        }

        $this->sync($event->user);
    }

    public function failed(UserRegisteredEvent $event, Throwable $exception): void
    {
        Logger::exception($exception);
    }

    protected function requiresVerification(): bool
    {
        try {
            return (bool) (ApiKey::value('require_'.$this->serviceKey().'_user_verification') ?? false);
        } catch (Throwable) {
            return false;
        }
    }

    private function isUserFullyVerified(User $user): bool
    {
        $settings = StatusSetting::select('emailverification_status', 'msg91_status')->first();

        $isEmailVerified = ! $settings->emailverification_status || $user->email_verified;
        $isMobileVerified = ! $settings->msg91_status || $user->mobile_verified;

        return $isEmailVerified && $isMobileVerified;
    }
}
