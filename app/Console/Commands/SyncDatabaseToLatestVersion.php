<?php

namespace App\Console\Commands;

use App\Http\Controllers\SyncBillingToLatestVersion;
use Illuminate\Console\Command;

class SyncDatabaseToLatestVersion extends Command
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
     * @return int
     */
    public function handle()
    {
        echo (new SyncBillingToLatestVersion)->sync();
    }
}
