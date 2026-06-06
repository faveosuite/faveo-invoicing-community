<?php

namespace App\Plugins\Mailchimp\Listeners;

use App\Plugins\Mailchimp\Exceptions\MailchimpApiException;
use App\Plugins\Mailchimp\Exceptions\MailchimpRateLimitException;
use App\Plugins\Mailchimp\Services\MailchimpService;
use App\User;

class SubscribeUserOnRegister
{
    public function __construct(private readonly MailchimpService $service) {}

    public function handle(User $user): void
    {
        try {
            $this->service->subscribe($user);
        } catch (MailchimpRateLimitException $e) {
            \Logger::exception($e);
        } catch (MailchimpApiException $e) {
            \Logger::exception($e);
        } catch (\Throwable $e) {
            \Logger::exception($e);
        }
    }
}
