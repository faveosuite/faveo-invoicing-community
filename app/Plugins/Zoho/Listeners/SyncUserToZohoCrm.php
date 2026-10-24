<?php

namespace App\Plugins\Zoho\Listeners;

use App\Listeners\BaseExternalSyncListener;
use App\Plugins\Zoho\Integrations\Crm\Controllers\ZohoCrmController;
use App\User;
use Override;

class SyncUserToZohoCrm extends BaseExternalSyncListener
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
        return zohoIntegrationActive('crm');
    }

    protected function sync(User $user): void
    {
        resolve(ZohoCrmController::class)->addUserDataToCrm($user->email);
    }
}
