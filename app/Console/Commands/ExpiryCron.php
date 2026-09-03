<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\LoggableCommand;
use App\Http\Controllers\Common\CronController;

class ExpiryCron extends LoggableCommand
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
     * Execute the console command.
     */
    public function handleAndLog(): void
    {
        $controller = new CronController;
        $controller->eachSubscription();
        $this->info('expiry:notification Command Run successfully!');
    }
}
