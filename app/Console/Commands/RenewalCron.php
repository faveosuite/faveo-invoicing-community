<?php

namespace App\Console\Commands;

use App\Http\Controllers\ConcretePostSubscriptionHandleController;
use App\Http\Controllers\Subscription\SubscriptionController; // Import the concrete controller
use Illuminate\Console\Command;

class RenewalCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'renewal:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for auto renewal';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Create an instance of ConcretePostSubscriptionHandleController
        $concreteController = app()->make(ConcretePostSubscriptionHandleController::class);

        // Pass the concrete controller instance to CronController constructor
        $controller = new SubscriptionController($concreteController);

        // Call the method to perform auto-renewal
        $controller->autoRenewal();

        // Output success message
        $this->info('renewal:cron Command Run successfully!');
    }
}
