<?php

namespace App\Console\Commands;

use App\Http\Controllers\BillingInstaller\InstallerController;
use App\Http\Controllers\SyncBillingToLatestVersion;
use Config;
use DB;
use Illuminate\Console\Command;

class InstallDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'installing database';

    protected $install;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->install = new InstallerController();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $env = base_path().DIRECTORY_SEPARATOR.'.env';
            if (! is_file($env)) {
                throw new \Exception("Please run 'php artisan install:agora'");
            }
            if (! $this->confirm('Do you want to migrate tables now?')) {
                return;
            }
            $this->call('key:generate', ['--force' => true]);
            $this->checkDBVersion();
            $this->info('');
            $this->info('Database setup in progress...');
            (new SyncBillingToLatestVersion)->sync();
            $this->info('');
            $this->info('Database setup completed successfully.');
            $this->createAdmin();
            $headers = ['email', 'password'];
            $data = [
                [
                    'email' => 'demo@admin.com',
                    'password' => 'Demo@1234',
                ],
            ];
            $env = $this->choice(
                'Select application environment', ['production', 'development', 'testing']
            );
            $this->install->updateInstallEnv($env);
            $this->table($headers, $data);
            $this->info('');
            $this->warn('Please update your email and change the password immediately'.PHP_EOL);
            $url = Config::get('app.url');
            $this->info("Agora has been installed successfully. Please visit $url to login".PHP_EOL);
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
    }

    /**
     * Function fetches database version from connection a $this->info('');nd compares it with
     * minimum required verion.
     */
    private function checkDBVersion(): void
    {
        try {
            $pdo = DB::connection()->getPdo();
            $version = $pdo->query('select version()')->fetchColumn();
            if (strpos($version, 'Maria') === false) {
                $this->checkMySQLVersion($version);

                return;
            }
            $this->checkMariaDBVersion($version);
        } catch (\Exception $e) {
            if ($e->getCode() != 1049) {
                throw $e;
            }
            $database = config('database.connections.mysql.database');
            config(['database.connections.mysql.database' => null]);
            createDB($database);
            config(['database.connections.mysql.database' => $database]);
            DB::reconnect();
            DB::purge();
            $this->checkDBVersion();
        }
    }

    /**
     * Function to check version requirement for MariaDB.
     */
    private function checkMariaDBVersion(string $version): void
    {
        $this->compareVersion($this->printAndFormatVersion($version, 'MariaDB'), '10.3', 'MariaDB');
    }

    /**
     * Function to check version requirement for MySQL.
     */
    private function checkMySQLVersion(string $version): void
    {
        $this->compareVersion($this->printAndFormatVersion($version, 'MySQL'), '5.6', 'MySQL');
    }

    /**
     * Function compares database version with minimum required version.
     *
     * @param  string  $version  unfomatted version string
     * @param  string  $min  minimum required version for database
     * @param  string  $db  database name
     *
     * @throws Exception
     */
    private function compareVersion($version, $min, $db = 'MySQL'): void
    {
        if (version_compare($version, $min) < 0) {
            throw new \Exception("Please update your $db database version to $min or greater");
        }
    }

    /**
     * Function prints database version and returns formatted version string.
     *
     * @param  string  $version  unfomatted version string
     * @param  string  $db  database name
     * @return string formatted version string
     */
    private function printAndFormatVersion(string $version, string $db = 'MySQL'): string
    {
        $this->info("You are running $db database on version $version");
        preg_match("/^[0-9\.]+/", $version, $match);

        return $match[0];
    }

    public function createAdmin()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('users')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return \App\User::create([
            'first_name' => 'Demo',
            'last_name' => 'Admin',
            'user_name' => 'demo',
            'email' => 'demo@admin.com',
            'role' => 'admin',
            'password' => \Hash::make('Demo@1234'),
            'active' => 1,
            'mobile_verified' => 1,
            'currency' => 'INR',
        ]);
    }
}
