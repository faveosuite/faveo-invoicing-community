<?php

namespace App\Plugins\Mailchimp\Providers;

use App\Contracts\NewsletterProvider;
use App\Model\Common\StatusSetting;
use App\Plugins\Mailchimp\Services\MailchimpService;

class MailchimpNewsletterProvider implements NewsletterProvider
{
    public function __construct(private readonly MailchimpService $service)
    {
    }

    public function name(): string
    {
        return 'Mailchimp';
    }

    public function isEnabled(): bool
    {
        return (bool) StatusSetting::value('mailchimp_status');
    }

    public function subscribeEmail(string $email): void
    {
        $this->service->subscribeEmail($email);
    }
}
