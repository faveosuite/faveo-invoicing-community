<?php

namespace App\License\Console\Commands;

use App\License\Models\LicenseReport;
use App\Model\Mailjob\ExpiryMailDay;
use Illuminate\Console\Command;

class CrackReportsCleanup extends Command
{
    protected $signature = 'app:crack-reports-cleanup';

    protected $description = 'Crack Reports Cleanup';

    public function handle(): void
    {
        $days = ExpiryMailDay::value('license_crack_reports_cleanup_days');
        if ($days === null) {
            return;
        }

        LicenseReport::whereNull('product_id')
            ->where('report_system', 0)
            ->where('report_date_time', '<', now()->subDays($days))
            ->delete();
    }
}
