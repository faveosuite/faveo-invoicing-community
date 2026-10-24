<?php

namespace App\Plugins\Zoho\Integrations\Campaigns\Providers;

use App\Contracts\NewsletterProvider;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\ZohoCampaignsController;

class ZohoCampaignsNewsletterProvider implements NewsletterProvider
{
    public function name(): string
    {
        return 'Zoho Campaigns';
    }

    public function isEnabled(): bool
    {
        return zohoIntegrationActive('campaigns');
    }

    public function subscribeEmail(string $email): void
    {
        resolve(ZohoCampaignsController::class)->subscribe($email, 'newsletter');
    }
}
