<?php

namespace App\Console\Commands;

use App\Http\Controllers\Common\CronController;
use Illuminate\Console\Command;

class ExpiryCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expiry:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renewal notification for disabled the auto subscription orders';

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
    public function handle()
    {
        $controller = new CronController();
        $controller->eachSubscription();
        $this->info('expiry:notification Command Run successfully!');
    }
}
