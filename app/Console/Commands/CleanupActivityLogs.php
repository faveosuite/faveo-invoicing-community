<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\LoggableCommand;
use App\Model\Mailjob\Condition;
use Spatie\Activitylog\Support\Config;

class CleanupActivityLogs extends LoggableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-logs:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete activity logs older than the "delete logs after N days" cron setting (0 deletes all)';

    /**
     * Execute the console command.
     */
    public function handleAndLog(): void
    {
        $days = $this->maxAgeInDays();

        // Called directly rather than via activitylog:clean, which rejects 0 ("delete all").
        $deleted = Config::cleanActivityLogAction()->execute($days);

        $this->info("Deleted {$deleted} activity log record(s) older than {$days} day(s).");
    }

    /**
     * The admin's setting, or activitylog.clean_after_days when it was never saved.
     */
    private function maxAgeInDays(): int
    {
        return Condition::dayCount('deleteLogs') ?? (int) config('activitylog.clean_after_days');
    }
}
