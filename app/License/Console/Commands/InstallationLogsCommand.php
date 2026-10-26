<?php

namespace App\License\Console\Commands;

use App\License\Models\InstallationLog;
use App\Model\Mailjob\Condition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class InstallationLogsCommand extends Command
{
    protected $signature = 'installation:logs';

    protected $description = 'Logs every minute for installation status';

    public function handle(): void
    {
        $days = Condition::dayCount('installationLogs') ?? 5;
        $expireDate = Date::now()->subDays($days)->toDateString();

        InstallationLog::where('installation_last_active_date', '<', $expireDate)
            ->update(['installation_status' => 0]);
    }
}
