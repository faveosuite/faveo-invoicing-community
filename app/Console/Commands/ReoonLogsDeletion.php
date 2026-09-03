<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Controllers\Common\CronController;
use Illuminate\Console\Command;

class ReoonLogsDeletion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reoon:logs-deletion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reoon Email Verifier Logs Deletion';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $controller = new CronController;
        $controller->reoonLogsDeletion();
        $this->info('reoon:logs-deletion Command Run successfully!');
    }
}
