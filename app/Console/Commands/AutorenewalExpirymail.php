<?php

namespace App\Console\Commands;

use App\Http\Controllers\Common\CronController;
use Illuminate\Console\Command;

class AutorenewalExpirymail extends Command
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
    public function handle()
    {
        $controller = new CronController();
        $controller->autoRenewalExpiryNotify();
        $this->info('renewal:notification Command Run successfully!');
    }
}
