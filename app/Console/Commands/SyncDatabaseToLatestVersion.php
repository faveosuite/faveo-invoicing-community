<?php

namespace App\Console\Commands;

use App\Console\LoggableCommand;
use App\Http\Controllers\SyncBillingToLatestVersion;

/**
 * @codeCoverageIgnore
 */
class SyncDatabaseToLatestVersion extends LoggableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update billing database to latest version';

    /**
     * Execute the console command.
     */
    public function handleAndLog(): void
    {
        echo (new SyncBillingToLatestVersion)->sync();
    }
}
