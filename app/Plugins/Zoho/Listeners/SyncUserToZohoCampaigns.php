<?php

namespace App\Plugins\Zoho\Listeners;

use App\Listeners\BaseExternalSyncListener;
use App\Model\Common\StatusSetting;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\ZohoCampaignsController;
use App\User;

class SyncUserToZohoCampaigns extends BaseExternalSyncListener
{
    protected function serviceKey(): string
    {
        return 'zoho';
    }

    protected function isEnabled(): bool
    {
        return (bool) StatusSetting::value('zoho_status');
    }

    protected function sync(User $user): void
    {
        resolve(ZohoCampaignsController::class)->subscribe($user->email, 'newsletter');
    }
}
