<?php

namespace App\Console\Commands;

use App\Console\LoggableCommand;
use App\Http\Controllers\BillingInstaller\InstallerController;
use App\Http\Controllers\SyncBillingToLatestVersion;
use App\Model\Common\Setting;
use App\User;
use Artisan;
use Config;
use DB;
use Dotenv\Dotenv;
use Exception;
use Hash;

class InstallDB extends LoggableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:db 
                            {--migrate= : Run database migrations}
                            {--env= : Set the application environment (production, development, testing)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'installing database';

    protected \App\Http\Controllers\BillingInstaller\InstallerController $install;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        $this->install = new InstallerController();
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handleAndLog(): void
    {
        try {
            $migrateOption = $this->option('migrate');
            $envOption = $this->option('env');

            $envFilePath = base_path().DIRECTORY_SEPARATOR.'.env';

            if (! is_file($envFilePath)) {
                throw new Exception("Please run 'php artisan install:agora'");
            }

            $shouldMigrate = filter_var(
                $migrateOption ?? $this->confirm('Do you want to migrate tables now?'),
                FILTER_VALIDATE_BOOLEAN
            );
            if (! $shouldMigrate) {
                return;
            }

            $this->runArtisanSetup();
            $this->checkDBVersion();

            $this->info('');
            $this->info('Database setup in progress...');
            (new SyncBillingToLatestVersion)->sync();

            $this->info('');
            $this->info('Database setup completed successfully.');

            $this->createAdmin();
            $environment = $envOption ?? $this->choice('Select application environment', ['production', 'development', 'testing']);
            $this->install->updateInstallEnv($environment);
            $this->showAdminInfo();
            $this->info('');

            $this->warn('Please update your email and change the password immediately'.PHP_EOL);
            $url = Config::get('app.url');
            $this->info(sprintf('Agora has been installed successfully. Please visit %s to login', $url).PHP_EOL);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
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
            if (! str_contains((string) $version, 'Maria')) {
                $this->checkMySQLVersion($version);

                return;
            }

            $this->checkMariaDBVersion($version);
        } catch (Exception $exception) {
            if ($exception->getCode() != 1049) {
                throw $exception;
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
    private function compareVersion(string $version, string $min, string $db = 'MySQL'): void
    {
        if (version_compare($version, $min) < 0) {
            throw new Exception(sprintf('Please update your %s database version to %s or greater', $db, $min));
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
        $this->info(sprintf('You are running %s database on version %s', $db, $version));
        preg_match("/^[0-9\.]+/", $version, $match);

        return $match[0];
    }

    public function createAdmin()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $admin = User::create([
            'first_name' => 'Demo',
            'last_name' => 'Admin',
            'user_name' => 'demo',
            'email' => 'demo@admin.com',
            'password' => Hash::make('Demo@1234'),
            'mobile_verified' => 1,
            'email_verified' => 1,
            'currency' => 'INR',
        ]);
        $admin->role = 'admin';
        $admin->active = 1;
        $admin->save();

        // Update settings
        Setting::where('id', 1)
            ->update([
                'title' => 'Agora Invoicing',
                'favicon_title' => 'Agora Invoicing',
                'favicon_title_client' => 'Agora Invoicing',
                'admin_logo' => null,
                'logo' => null,
                'fav_icon' => null,
            ]);

        return $admin;
    }

    /**
     * Run artisan commands to set up the application environment.
     */
    protected function runArtisanSetup()
    {
        $dotenv = Dotenv::createImmutable(base_path());
        $dotenv->load();

        config([
            'database.connections.mysql.password' => $_SERVER['DB_PASSWORD'] ?? null,
            'database.connections.mysql.username' => $_SERVER['DB_USERNAME'] ?? null,
            'database.connections.mysql.host' => $_SERVER['DB_HOST'] ?? null,
            'database.connections.mysql.database' => $_SERVER['DB_DATABASE'] ?? null,
            'database.connections.mysql.port' => $_SERVER['DB_PORT'] ?? null,
            'app.url' => $_SERVER['APP_URL'] ?? null,
            'app.key' => $_SERVER['APP_KEY'] ?? null,
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');

        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('key:generate', ['--force' => true]);
        $this->info(Artisan::output());
    }

    /**
     * Display admin user information in a table format.
     */
    protected function showAdminInfo()
    {
        $this->table(['email', 'password'], [
            [
                'email' => 'demo@admin.com',
                'password' => 'Demo@1234',
            ],
        ]);
    }
}
