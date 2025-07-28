<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class DropTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'droptables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drops all tables';

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
        $database = env('DB_DATABASE');
        $droplist = \Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
        $droplist = implode(',', array_map(function ($table) {
            return "`$table`";
        }, $droplist));

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            // Drop all tables
            DB::statement("DROP TABLE $droplist");

            DB::statement('SET FOREIGN_KEY_CHECKS = 1');

            $this->comment(PHP_EOL.'All tables were dropped successfully.'.PHP_EOL);
        } catch (\Exception $e) {
            // Log the error or handle it accordingly
            $this->error($e->getMessage());
        }
    }
}
