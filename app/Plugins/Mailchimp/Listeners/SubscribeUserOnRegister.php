<?php

namespace App\Plugins\Mailchimp\Listeners;

use App\Listeners\BaseExternalSyncListener;
use App\Model\Common\StatusSetting;
use App\Plugins\Mailchimp\Services\MailchimpService;
use App\User;
use Override;

class SubscribeUserOnRegister extends BaseExternalSyncListener
{
    public function __construct(private readonly MailchimpService $service)
    {
    }

    protected function serviceKey(): string
    {
        return 'mailchimp';
    }

    /** Mailchimp only ever receives verified users (no per-service toggle like Pipedrive's). */
    #[Override]
    protected function requiresVerification(): bool
    {
        return true;
    }

    protected function isEnabled(): bool
    {
        return (bool) StatusSetting::value('mailchimp_status');
    }

    protected function sync(User $user): void
    {
        $this->service->subscribe($user);
    }
}
