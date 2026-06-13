<?php

namespace App\Listeners;

use App\Http\Controllers\Common\PipedriveController;
use App\Model\Common\StatusSetting;
use App\User;

class SyncUserToPipedrive extends BaseExternalSyncListener
{
    protected function serviceKey(): string
    {
        return 'pipedrive';
    }

    protected function isEnabled(): bool
    {
        return (bool) StatusSetting::value('pipedrive_status');
    }

    protected function sync(User $user): void
    {
        app(PipedriveController::class)->addUserToPipedrive($user);
    }
}
