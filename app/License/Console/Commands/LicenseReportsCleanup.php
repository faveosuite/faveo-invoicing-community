<?php

namespace App\License\Console\Commands;

use App\License\Models\LicenseReport;
use App\Model\Mailjob\ExpiryMailDay;
use Illuminate\Console\Command;

class LicenseReportsCleanup extends Command
{
    protected $signature = 'app:license-reports-cleanup';

    protected $description = 'License Reports Cleanup';

    public function handle(): void
    {
        $days = ExpiryMailDay::value('license_reports_cleanup_days');
        if ($days === null) {
            return;
        }

        LicenseReport::whereNotNull('license_code')
            ->where('license_code', '!=', '')
            ->where('report_date_time', '<', now()->subDays($days))
            ->delete();
    }
}
