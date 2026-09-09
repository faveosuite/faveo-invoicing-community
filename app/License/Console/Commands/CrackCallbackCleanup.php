<?php

namespace App\License\Console\Commands;

use App\License\Models\LicenseCallback;
use App\Model\Mailjob\ExpiryMailDay;
use Illuminate\Console\Command;

class CrackCallbackCleanup extends Command
{
    protected $signature = 'app:crack-callback-cleanup';

    protected $description = 'Cleanup Callbacks';

    public function handle(): void
    {
        $days = ExpiryMailDay::value('license_callbacks_cleanup_days');
        if ($days === null) {
            return;
        }

        LicenseCallback::where('callback_date_time', '<', now()->subDays($days))->delete();
    }
}
