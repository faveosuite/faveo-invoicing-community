<?php

namespace App\Plugins\Zoho\Integrations\Campaigns\Providers;

use App\Contracts\NewsletterProvider;
use App\Model\Common\StatusSetting;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\ZohoCampaignsController;

class ZohoCampaignsNewsletterProvider implements NewsletterProvider
{
    public function name(): string
    {
        return 'Zoho Campaigns';
    }

    public function isEnabled(): bool
    {
        return (bool) StatusSetting::value('zoho_status');
    }

    public function subscribeEmail(string $email): void
    {
        app(ZohoCampaignsController::class)->subscribe($email, 'newsletter');
    }
}
