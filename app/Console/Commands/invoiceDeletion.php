<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\LoggableCommand;
use App\Http\Controllers\Common\CronController;

class invoiceDeletion extends LoggableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old invoices';

    /**
     * Execute the console command.
     */
    public function handleAndLog(): void
    {
        $controller = new CronController();
        $controller->invoicesDeletion();
        $this->info('invoices:delete Command Run successfully!');
    }
}
