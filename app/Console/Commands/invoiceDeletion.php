<?php

namespace App\Console\Commands;

use App\Http\Controllers\Common\CronController;
use Illuminate\Console\Command;

class invoiceDeletion extends Command
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
     *
     * @return int
     */
    public function handle()
    {
        $controller = new CronController();
        $controller->invoicesDeletion();
        $this->info('invoices:delete Command Run successfully!');
    }
}
