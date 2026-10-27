<?php

namespace Tests\Unit\Backend\Plugins\Zoho;

use App\Plugins\Zoho\Integrations\Campaigns\Controllers\ZohoCampaignsController;
use App\Plugins\Zoho\Integrations\Campaigns\Facades\ZohoCampaigns;
use Mockery;
use Tests\DBTestCase;

class ZohoCampaignsSubscribeTest extends DBTestCase
{
    public function test_an_email_without_an_account_is_still_subscribed_to_the_newsletter(): void
    {
        // swap() rather than shouldReceive(): resolving the real Campaigns needs a connected Zoho account.
        $campaigns = Mockery::mock();
        ZohoCampaigns::swap($campaigns);
        $campaigns->shouldReceive('subscribe')
            ->once()
            ->withArgs(fn ($email, $info, $list, $topic): bool => $email === 'guest-newsletter@example.com' && $topic === 'Newsletter');

        resolve(ZohoCampaignsController::class)->subscribe('guest-newsletter@example.com', 'newsletter');
    }
}
