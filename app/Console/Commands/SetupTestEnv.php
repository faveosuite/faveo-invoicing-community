<?php

namespace App\Console\Commands;

use App\Console\LoggableCommand;
use Artisan;
use Config;
use DB;

class SetupTestEnv extends LoggableCommand
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
     */
    public function handleAndLog(): void
    {
        $dbUsername = $this->option('username') ?: config('database.connections.mysql.username');
        $dbPassword = $this->option('password') ?: config('database.connections.mysql.password');
        $dbName = is_string($this->option('database')) ? $this->option('database') : 'billing_testing_db';

        $dbPassword = $dbPassword ? $dbPassword : '';
        $this->setupConfig($dbUsername, $dbPassword, 'Innodb');

        echo "\nCreating database...\n";

        $this->createDB($dbName);

        echo "\nDatabase Created Successfully!\n";

        // setting up new database name
        Config::set('database.connections.mysql.database', $dbName);

        // setting up app env to testing
        Config::set('app.env', 'testing');

        // add default app key for testing
        Config::set('app.key', 'base64:G4WSQduFNvk9rYtoLS1ozg==');

        // opening a database connection
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

        // closing the database connection
        DB::disconnect('mysql');

        $this->createEnv($dbUsername, $dbPassword, $dbName);

        echo "\nTesting Database setup Successfully\n";
    }

    /**
     * Creates an env file if not exists already.
     */
    private function createEnv(string $dbUsername, string $dbPassword, string $dbName): void
    {
        $testingEnv = [
            'APP_ENV' => 'testing',
            'DB_USERNAME' => $dbUsername,
            'DB_PASSWORD' => $dbPassword,
            'DB_DATABASE' => $dbName,
            'APP_DEBUG' => 'true',
        ];

        $this->createEnvFile($testingEnv, '.env.testing');

        $duskEnv = [
            'APP_ENV' => 'testing',
            'DB_USERNAME' => $dbUsername,
            'DB_PASSWORD' => $dbPassword,
            'DB_DATABASE' => $dbName,
            'DB_INSTALL' => 1,
            'APP_KEY' => 'base64:G4WSQduFNvk9rYtoLS1ozg==',
        ];

        $this->createEnvFile($duskEnv, '.env.dusk.testing');
    }

    /**
     * @param  array<mixed>  $settings
     */
    private function createEnvFile(array $settings, string $envFile): void
    {
        $config = '';
        foreach ($settings as $key => $val) {
            $config .= sprintf('%s=%s%s', $key, $val, PHP_EOL);
        }

        $envLocation = base_path(DIRECTORY_SEPARATOR.$envFile);
        file_put_contents($envLocation, $config);
    }

    /**
     * Sets up DB config for testing.
     *
     * @param  string  $dbUsername  mysql username
     * @param  string  $dbPassword  mysql password
     */
    private function setupConfig($dbUsername, $dbPassword, string $dbengine = ''): void
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
     */
    private function createDB(string $dbName): void
    {
        DB::purge('mysql');
        // removing old db
        DB::connection('mysql')->getPdo()->exec(sprintf('DROP DATABASE IF EXISTS `%s`', $dbName));

        // Creating testing_db
        DB::connection('mysql')->getPdo()->exec(sprintf('CREATE DATABASE `%s`', $dbName));
        // disconnecting it will remove database config from the memory so that new database name can be
        // populated
        DB::disconnect('mysql');
    }

    private function handleSeeder(): void
    {
        $latestVersion = preg_replace('#v\.|v#', '', str_replace('_', '.', (string) Config::get('app.version'))) ?? '';
        $seedersPath = database_path('seeders');
        $seederVersions = scandir($seedersPath);
        $seederVersions = array_filter($seederVersions, fn ($dir): bool => (bool) preg_match('/^v[\d_]+(?:_[A-Za-z\d]+)*$/', (string) $dir));
        natsort($seederVersions);
        foreach ($seederVersions as $version) {
            if (version_compare($version, $latestVersion, '<=')) {
                $seederClass = sprintf('Database\Seeders\%s\DatabaseSeeder', $version);
                $this->runSeeder($seederClass);
            }
        }
    }

    private function runSeeder(string $seederClass): void
    {
        Artisan::call('db:seed', ['--class' => $seederClass, '--force' => true]);
        $output = Artisan::output();
        echo sprintf('Seeding for %s: %s%s', $seederClass, $output, PHP_EOL);
    }
}
