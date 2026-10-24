<?php

namespace App\Plugins\Zoho\Listeners;

use App\Listeners\BaseExternalSyncListener;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\ZohoCampaignsController;
use App\User;
use Override;

class SyncUserToZohoCampaigns extends BaseExternalSyncListener
{
    protected function serviceKey(): string
    {
        return 'zoho';
    }

    /** Zoho only ever receives verified users (no per-service toggle like Pipedrive's). */
    #[Override]
    protected function requiresVerification(): bool
    {
        return true;
    }

    protected function isEnabled(): bool
    {
        return zohoIntegrationActive('campaigns');
    }

    protected function sync(User $user): void
    {
        resolve(ZohoCampaignsController::class)->subscribe($user->email, 'newsletter');
    }
}
