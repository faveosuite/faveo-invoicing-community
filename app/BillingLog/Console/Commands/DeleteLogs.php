<?php

namespace App\BillingLog\Console\Commands;

use App\BillingLog\Controllers\LogViewController;
use App\Console\LoggableCommand;
use App\Model\Mailjob\Condition;
use Illuminate\Support\Facades\Date;

class DeleteLogs extends LoggableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes system logs older than';

    /**
     * Execute the console command.
     */
    public function handleAndLog(): void
    {
        if (! Condition::isActive('systemLogs')) {
            return;
        }

        $days = (int) Condition::dayCount('systemLogs');

        $deleteBefore = Date::now()->subDays($days)->endOfDay();

        (new LogViewController)->deleteLogsByDate(
            ['mail', 'cron', 'exception', 'failed_jobs'],
            $deleteBefore
        );
    }
}
