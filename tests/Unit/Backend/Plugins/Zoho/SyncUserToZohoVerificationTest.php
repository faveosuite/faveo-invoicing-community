<?php

namespace Tests\Unit\Backend\Plugins\Zoho;

use App\Events\UserRegisteredEvent;
use App\Model\Common\StatusSetting;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\ZohoCampaignsController;
use App\Plugins\Zoho\Listeners\SyncUserToZohoCampaigns;
use App\Plugins\Zoho\Models\ZohoIntegration;
use App\User;
use Tests\DBTestCase;

class SyncUserToZohoVerificationTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ZohoIntegration::where('platform', 'campaigns')->update(['is_active' => true]);
        if (! ZohoIntegration::where('platform', 'campaigns')->exists()) {
            $this->markTestSkipped('No Zoho Campaigns integration row to activate.');
        }
        StatusSetting::query()->update(['emailverification_status' => 1, 'msg91_status' => 0]);
    }

    public function test_an_unverified_user_is_not_sent_to_zoho_at_registration(): void
    {
        $this->mock(ZohoCampaignsController::class)->shouldNotReceive('subscribe');

        $user = User::factory()->create(['email_verified' => 0]);

        (new SyncUserToZohoCampaigns)->handle(new UserRegisteredEvent($user, 'register'));
    }

    public function test_a_user_is_sent_to_zoho_once_verified(): void
    {
        $user = User::factory()->create(['email_verified' => 1]);

        $this->mock(ZohoCampaignsController::class)
            ->shouldReceive('subscribe')->once()->with($user->email, 'newsletter');

        (new SyncUserToZohoCampaigns)->handle(new UserRegisteredEvent($user, 'verify'));
    }
}
