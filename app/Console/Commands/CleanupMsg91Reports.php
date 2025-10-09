<?php

namespace App\Console\Commands;

use App\Console\LoggableCommand;
use App\Http\Controllers\Common\CronController;

class CleanupMsg91Reports extends LoggableCommand
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
    public function handleAndLog()
    {
        $controller = new CronController();
        $controller->msgDeletions();
        $this->info('MSG91 reports have been deleted.');
    }
}
