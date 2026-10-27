<?php

namespace Tests\Unit\Backend\Plugins\Mailchimp;

use App\Events\UserRegisteredEvent;
use App\Model\Common\StatusSetting;
use App\Plugins\Mailchimp\Listeners\SubscribeUserOnRegister;
use App\Plugins\Mailchimp\Services\MailchimpService;
use App\User;
use Tests\DBTestCase;

class SubscribeUserOnRegisterTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        StatusSetting::query()->update(['mailchimp_status' => 1, 'emailverification_status' => 1, 'msg91_status' => 0]);
    }

    public function test_an_unverified_user_is_not_sent_to_mailchimp_at_registration(): void
    {
        $service = $this->mock(MailchimpService::class);
        $service->shouldNotReceive('subscribe');

        $user = User::factory()->create(['email_verified' => 0]);

        (new SubscribeUserOnRegister($service))->handle(new UserRegisteredEvent($user, 'register'));
    }

    public function test_a_user_is_sent_to_mailchimp_once_verified(): void
    {
        $user = User::factory()->create(['email_verified' => 1]);

        $service = $this->mock(MailchimpService::class);
        $service->shouldReceive('subscribe')->once()->withArgs(fn (User $sent): bool => $sent->is($user));

        (new SubscribeUserOnRegister($service))->handle(new UserRegisteredEvent($user, 'verify'));
    }
}
