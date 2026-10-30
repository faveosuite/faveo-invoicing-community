<?php

namespace App\Plugins\Mailchimp\Listeners;

use App\Plugins\Mailchimp\Exceptions\MailchimpApiException;
use App\Plugins\Mailchimp\Services\MailchimpService;
use Logger;
use Throwable;

class UnsubscribeOnUserDeleted
{
    public function __construct(private readonly MailchimpService $service) {}

    public function handle(string $email): void
    {
        try {
            $this->service->unsubscribe($email);
        } catch (MailchimpApiException|Throwable $e) { // @phpstan-ignore catch.neverThrown
            Logger::exception($e);
        }
    }
}
