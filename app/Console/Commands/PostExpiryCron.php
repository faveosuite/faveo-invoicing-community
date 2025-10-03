<?php

namespace App\Console\Commands;

use App\Console\LoggableCommand;
use App\Http\Controllers\Common\CronController;

class PostExpiryCron extends LoggableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postexpiry:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renewal notification for all expired orders';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handleAndLog()
    {
        $controller = new CronController();
        $controller->postRenewalNotify();
        $this->info('postexpiry:notification Command Run successfully!');
    }
}
