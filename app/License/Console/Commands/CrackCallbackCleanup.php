<?php

namespace App\License\Console\Commands;

use App\License\Models\LicenseCallback;
use App\Model\Mailjob\Condition;
use Illuminate\Console\Command;

class CrackCallbackCleanup extends Command
{
    protected $signature = 'app:crack-callback-cleanup';

    protected $description = 'Cleanup Callbacks';

    public function handle(): void
    {
        $days = Condition::dayCount('licenseCallbacksCleanup');
        if ($days === null) {
            return;
        }

        LicenseCallback::where('callback_date_time', '<', now()->subDays($days))->delete();
    }
}
