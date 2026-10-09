<?php

namespace App\Plugins\Zoho\Listeners;

use App\Listeners\BaseExternalSyncListener;
use App\Model\Common\StatusSetting;
use App\Plugins\Zoho\Integrations\Crm\Controllers\ZohoCrmController;
use App\User;

class SyncUserToZohoCrm extends BaseExternalSyncListener
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
        resolve(ZohoCrmController::class)->addUserDataToCrm($user->email);
    }
}
