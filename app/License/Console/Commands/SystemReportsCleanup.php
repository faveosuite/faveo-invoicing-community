<?php

namespace App\License\Console\Commands;

use App\License\Models\LicenseReport;
use App\Model\Mailjob\Condition;
use Illuminate\Console\Command;

class SystemReportsCleanup extends Command
{
    protected $signature = 'app:system-reports-cleanup';

    protected $description = 'System Report Cleanup';

    public function handle(): void
    {
        $days = Condition::dayCount('licenseSystemReportsCleanup');
        if ($days === null) {
            return;
        }

        LicenseReport::where('report_system', 1)
            ->where('report_date_time', '<', now()->subDays($days))
            ->delete();
    }
}
