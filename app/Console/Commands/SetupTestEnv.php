<?php

namespace App\Console\Commands;

use Artisan;
use Config;
use DB;
use Illuminate\Console\Command;

class SetupTestEnv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testing-setup {--username=} {--password=} {--database=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a testing_db, runs migration and seeder for testing';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dbUsername = $this->option('username') ? $this->option('username') : env('DB_USERNAME');
        $dbPassword = $this->option('password') ? $this->option('password') : env('DB_PASSWORD');
        $dbName = $this->option('database') ? $this->option('database') : 'billing_testing_db';

        $dbPassword = ! $dbPassword ? '' : $dbPassword;
        $this->setupConfig($dbUsername, $dbPassword, '', 'Innodb');

        echo "\nCreating database...\n";

        $this->createDB($dbName);

        echo "\nDatabase Created Successfully!\n";

        //setting up new database name
        Config::set('database.connections.mysql.database', $dbName);

        //setting up app env to testing
        Config::set('app.env', 'testing');

        //add default app key for testing
        Config::set('app.key', 'base64:G4WSQduFNvk9rYtoLS1ozg==');

        //opening a database connection
        DB::purge('mysql');

        echo "\nRunning migrations!\n";

        Artisan::call('migrate', ['--force' => true]);

        echo Artisan::output();

        echo "\nMigrations completed!\n";

        echo "\nRunning seeders!\n";

        // Run seeders
        $this->handleSeeder();

        echo Artisan::output();

        echo "\nSeeders ran successfully!\n";

        //closing the database connection
        DB::disconnect('mysql');

        $this->createEnv($dbUsername, $dbPassword, $dbName);

        echo "\nTesting Database setup Successfully\n";
    }

    /**
     * Creates an env file if not exists already.
     *
     * @param  string  $dbUsername
     * @param  string  $dbPassword
     * @return null
     */
    private function createEnv(string $dbUsername, string $dbPassword, string $dbName)
    {
        $testingEnv = [
            'APP_ENV' => 'testing',
            'DB_USERNAME' => $dbUsername,
            'DB_PASSWORD' => $dbPassword,
            'DB_DATABASE' => $dbName,
        ];

        $this->createEnvFile($testingEnv, '.env.testing');

        $duskEnv = [
            'APP_ENV' => 'testing',
            'DB_USERNAME' => $dbUsername,
            'DB_PASSWORD' => $dbPassword,
            'DB_DATABASE' => $dbName,
            'DB_INSTALL' => 1,
        ];

        $this->createEnvFile($duskEnv, '.env.dusk.testing');
    }

    private function createEnvFile(array $settings, string $envFile)
    {
        $config = '';
        foreach ($settings as $key => $val) {
            $config .= "{$key}={$val}\n";
        }

        $envLocation = base_path(DIRECTORY_SEPARATOR.$envFile);
        file_put_contents($envLocation, $config);
    }

    /**
     * Sets up DB config for testing.
     *
     * @param  string  $dbUsername  mysql username
     * @param  string  $dbPassword  mysql password
     * @return null
     */
    private function setupConfig($dbUsername, $dbPassword, $port = '', $dbengine = '')
    {
        Config::set('app.env', 'development');
        Config::set('database.connections.mysql.port', '');
        Config::set('database.connections.mysql.database', null);
        Config::set('database.connections.mysql.username', $dbUsername);
        Config::set('database.connections.mysql.password', $dbPassword);
        Config::set('database.connections.mysql.engine', $dbengine);
        Config::set('database.install', 0);
    }

    /**
     * Creates an empty DB with given name.
     *
     * @param  string  $dbName  name of the DB
     * @return null
     */
    private function createDB(string $dbName)
    {
        \DB::purge('mysql');
        // removing old db
        \DB::connection('mysql')->getPdo()->exec("DROP DATABASE IF EXISTS `{$dbName}`");

        // Creating testing_db
        \DB::connection('mysql')->getPdo()->exec("CREATE DATABASE `{$dbName}`");
        //disconnecting it will remove database config from the memory so that new database name can be
        // populated
        \DB::disconnect('mysql');
    }

    private function handleSeeder()
    {
        $latestVersion = preg_replace('#v\.|v#', '', str_replace('_', '.', Config::get('app.version')));
        $seedersPath = database_path('seeders');
        $seederVersions = scandir($seedersPath);
        $seederVersions = array_filter($seederVersions, function ($dir) {
            return preg_match('/^v[\d_]+(?:_[A-Za-z\d]+)*$/', $dir);
        });
        natsort($seederVersions);
        foreach ($seederVersions as $version) {
            if (version_compare($version, $latestVersion, '<=')) {
                $seederClass = "Database\\Seeders\\$version\\DatabaseSeeder";
                $this->runSeeder($seederClass);
            }
        }
    }

    private function runSeeder($seederClass)
    {
        Artisan::call('db:seed', ['--class' => $seederClass, '--force' => true]);
        $output = Artisan::output();
        echo "Seeding for $seederClass: $output\n";
    }
}
