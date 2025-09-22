<?php

namespace App\Console\Commands;

use App\Console\LoggableCommand;
use App\Http\Controllers\Common\CronController;

class AutorenewalExpirymail extends LoggableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'renewal:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renewal notifications for auto renewal enabled orders';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handleAndLog()
    {
        $controller = new CronController();
        $controller->autoRenewalExpiryNotify();
        $this->info('renewal:notification Command Run successfully!');
    }
}
