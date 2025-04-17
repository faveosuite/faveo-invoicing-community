<?php

namespace App\Console\Commands;

use App\Http\Controllers\Common\CronController;
use App\Model\Common\MsgDeliveryReports;
use App\Model\Mailjob\ExpiryMailDay;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupMsg91Reports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:msg-reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup MSG91 reports older than a certain number of days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = new CronController();
        $controller->msgDeletions();
        $this->info("MSG91 reports have been deleted.");
    }
}
