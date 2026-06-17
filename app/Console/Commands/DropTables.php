<?php

namespace App\Console\Commands;

use App\Console\LoggableCommand;
use DB;
use Exception;
use Schema;

class DropTables extends LoggableCommand
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
     * Execute the console command.
     */
    public function handleAndLog(): void
    {
        $droplist = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
        $droplist = implode(',', array_map(fn (string $table): string => sprintf('`%s`', $table), $droplist));

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            // Drop all tables
            DB::statement('DROP TABLE '.$droplist);

            DB::statement('SET FOREIGN_KEY_CHECKS = 1');

            $this->comment(PHP_EOL.'All tables were dropped successfully.'.PHP_EOL);
        } catch (Exception $exception) {
            // Log the error or handle it accordingly
            $this->error($exception->getMessage());
        }
    }
}
