<?php

namespace App\BillingLog\Console\Commands;

use App\BillingLog\Controllers\LogViewController;
use App\Console\LoggableCommand;
use App\Model\Common\StatusSetting;
use App\Model\Mailjob\ExpiryMailDay;
use Carbon\Carbon;
use Illuminate\Console\Command;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handleAndLog()
    {
        if (StatusSetting::value('system_log_status') != 1) {
            return;
        }

        $days = ExpiryMailDay::value('system_logs_days');

        $deleteBefore = Carbon::now()->subDays($days)->endOfDay();

        (new LogViewController)->deleteLogsByDate(
            ['mail', 'cron', 'exception'],
            $deleteBefore
        );
    }
}
