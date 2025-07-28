<?php

namespace App\Console\Commands;

use App\Http\Controllers\Common\CronController;
use Illuminate\Console\Command;

class PostExpiryCron extends Command
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
    public function handle()
    {
        $controller = new CronController();
        $controller->postRenewalNotify();
        $this->info('postexpiry:notification Command Run successfully!');
    }
}
