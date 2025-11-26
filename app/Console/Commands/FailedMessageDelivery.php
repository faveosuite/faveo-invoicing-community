<?php

namespace App\Console\Commands;

use App\Http\Controllers\Common\CronController;
use Illuminate\Console\Command;

class FailedMessageDelivery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:failed-message-delivery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = new CronController();
        $controller->failedMessageDelivery();
        $this->info('app:failed-message-delivery Command Run successfully!');

    }
}
