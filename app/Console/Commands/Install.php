<?php

namespace App\Console\Commands;

use App\Console\LoggableCommand;
use App\Http\Controllers\BillingInstaller\BillingDependencyController;
use App\Http\Controllers\BillingInstaller\InstallerController;
use Cache;
use Exception;
use Illuminate\Support\Str;

class Install extends LoggableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:agora
                            {--appurl= : Application URL (must be HTTPS)}
                            {--sqlengine= : SQL Engine (e.g., mysql)}
                            {--sqlhost= : SQL Host}
                            {--dbname= : Database Name}
                            {--dbuser= : Database Username}
                            {--dbpass= : Database Password (optional)}
                            {--sqlport= : SQL Port (optional)}
                            {--securecon= : Secure Connection}
                            {--sslkey= : SSL Key}
                            {--sslcert= : SSL Certificate}
                            {--sslca= : SSL Ca}
                            {--sslverify= : SSl Verify}
                            {--migrate= : Database Migrations}
                            {--dummy= : Dummy Data}
                            {--env= : Application Environment (production, development, testing)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installing Agora billing App';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected \App\Http\Controllers\BillingInstaller\InstallerController $install;

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
            $this->displayArtLogo();

            if (! $this->appEnv()) {
                $this->info('Agora cannot be installed on your server. Please configure your server to meet the requirements and try again.');

                return;
            }

            //Form application URL
            $this->handleAppUrl();

            // Check if the URL is valid
            if (! $this->appReq($this->appUrl)) { // @phpstan-ignore property.notFound
                $this->info('Agora cannot be installed on your server. Please configure your server to meet the requirements and try again.');

                return;
            }

            //Check database credentials
            $this->collectDatabaseCredentials();

            //Check ssl options
            $this->configureSslOptions();

            Cache::put('search-driver', 'database');

            // Create .env
            $this->install->env(
                $this->default, // @phpstan-ignore property.notFound
                $this->host, // @phpstan-ignore property.notFound
                $this->port, // @phpstan-ignore property.notFound
                $this->dbname, // @phpstan-ignore property.notFound
                $this->dbuser, // @phpstan-ignore property.notFound
                $this->dbpass, // @phpstan-ignore property.notFound
                $this->appUrl, // @phpstan-ignore property.notFound
                $this->sslKey, // @phpstan-ignore property.notFound
                $this->sslCert, // @phpstan-ignore property.notFound
                $this->sslCa, // @phpstan-ignore property.notFound
                $this->sslVerify // @phpstan-ignore property.notFound
            );

            $this->info('.env file has been created');
            $this->info('');
            $this->call('preinstall:check');
            $this->maybeInstallDb();
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * Removes trailing slash from the url.
     */
    public function formatAppUrl(string $url): string
    {
        if (Str::finish($url, '/')) {
            return rtrim($url, '/ ');
        }

        return $url;
    }

    /**
     * Check whether extensions are there or not.
     *
     * @return bool
     */
    public function appEnv()
    {
        // Load extension details from billing-dependencies.json
        $dependencies = json_decode((string) file_get_contents(storage_path('billing-dependencies.json')), associative: true);
        $requiredExtensions = $dependencies['extensions']['required'];
        $minPhpVersion = $dependencies['min_php_version'];

        $result = [];
        $can_install = true;

        foreach ($requiredExtensions as $key => $extension) {
            $result[$key]['extension'] = $extension;
            if (! extension_loaded($extension)) {
                $result[$key]['status'] = "Not Loading, Please open '".php_ini_loaded_file()."' and add 'extension = ".$extension."'";
                $can_install = false;
            } else {
                $result[$key]['status'] = 'Loading';
            }
        }

        $result['php']['extension'] = 'PHP';
        if (version_compare(phpversion(), $minPhpVersion, '>=')) {
            $result['php']['status'] = 'PHP version supports';
        } else {
            $can_install = false;
            $result['php']['status'] = "PHP version doesn't support, please upgrade to ".$minPhpVersion.' or higher.';
        }

        $headers = ['Extension', 'Status'];
        $this->table($headers, $result);

        return $can_install;
    }

    /**
     * it checks the url whether the ssl certificate is installed or not.
     *
     * @param  $appUrl
     * @return bool
     */
    public function appReq(mixed $appUrl)
    {
        $canInstall = true;
        $arrayOfRequisites = [];
        $errorCount = 0;
        $connectionStatus = new BillingDependencyController('probe')->checkSSLCertificateOnDomain($arrayOfRequisites, $errorCount, $appUrl)[0]['connection'];
        if ($connectionStatus != 'Valid SSL certificate found, application can be served securely over HTTPS') {
            $canInstall = false;
        }

        $this->table(['Requisites', 'Status'], [['requisite' => 'ssl_certificate', 'status' => $connectionStatus]]);

        return $canInstall;
    }

    /**
     * Display Faveo's ASCII art logo in CLI.
     */
    public function displayArtLogo(): void
    {
        $this->line("
                                 _____                 _      _             
    /\                          |_   _|               (_)    (_)            
   /  \   __ _  ___  _ __ __ _    | |  _ ____   _____  _  ___ _ _ __   __ _ 
  / /\ \ / _` |/ _ \| '__/ _` |   | | | '_ \ \ / / _ \| |/ __| | '_ \ / _` |
 / ____ \ (_| | (_) | | | (_| |  _| |_| | | \ V / (_) | | (__| | | | | (_| |
/_/    \_\__, |\___/|_|  \__,_| |_____|_| |_|\_/ \___/|_|\___|_|_| |_|\__, |
          __/ |                                                        __/ |
         |___/                                                        |___/ 
");
    }

    /**
     * Handle the application URL input.
     */
    public function handleAppUrl(): void
    {
        $url = $this->option('appurl') ?: $this->ask('Enter your app URL (with only https)');
        $this->appUrl = $this->formatAppUrl($url); // @phpstan-ignore property.notFound
    }

    /**
     * Collect database credentials from user input or command options.
     */
    public function collectDatabaseCredentials(): void
    {
        $allowedEngines = ['mysql'];
        $this->default = in_array($this->option('sqlengine'), $allowedEngines) // @phpstan-ignore property.notFound
            ? $this->option('sqlengine')
            : $this->choice('Which SQL engine would you like to use?', $allowedEngines, 0);
        $this->host = $this->option('sqlhost') ?: $this->ask('Enter your SQL host'); // @phpstan-ignore property.notFound
        $this->dbname = $this->option('dbname') ?: $this->ask('Enter your database name'); // @phpstan-ignore property.notFound
        $this->dbuser = $this->option('dbuser') ?: $this->ask('Enter your database username'); // @phpstan-ignore property.notFound
        $this->dbpass = $this->option('dbpass') ?: $this->ask('Enter your database password'); // @phpstan-ignore property.notFound
        $this->port = $this->option('sqlport') ?? $this->ask('Enter your SQL port (leave blank if none)'); // @phpstan-ignore property.notFound
    }

    /**
     *  Configure SSL options for the database connection.
     */
    public function configureSslOptions(): void
    {
        $this->sslKey = null; // @phpstan-ignore property.notFound
        $this->sslCert = null; // @phpstan-ignore property.notFound
        $this->sslCa = null; // @phpstan-ignore property.notFound
        $this->sslVerify = false; // @phpstan-ignore property.notFound

        //If want ssl connection enabled then uncomment below code

//        $securecon = filter_var($this->option('securecon') ?? $this->confirm('Does your database allows secure connection? If yes then make sure you have all required files available on the server as pem bundle. (yes/no)'), FILTER_VALIDATE_BOOLEAN);

//        if ($securecon) {
//            $this->sslKey = $this->option('sslkey') ?: $this->ask('Full path to SSL key file in PEM format (Leave blank if not available)');
//            $this->sslCert = $this->option('sslcert') ?: $this->ask('Full path to SSL certificate file in PEM format (Leave blank if not available)');
//            $this->sslCa = $this->option('sslca') ?: $this->ask('Full path to Certificate Authority file in PEM format (Leave blank if not available)');
//            $this->sslVerify = filter_var($this->option('sslverify') ?? $this->confirm('Verify SSL Peer\'s Certificate?'), FILTER_VALIDATE_BOOLEAN);
//        }
    }

    /**
     *  Maybe install the database and run migrations.
     */
    public function maybeInstallDb(): void
    {
        $options = [
            'migrate', 'dummy', 'env',
        ];

        if (array_filter($options, fn ($o): bool => (bool) $this->option($o))) {
            $migrateOpt = $this->option('migrate');
            $migrateOption = trim(is_string($migrateOpt) ? $migrateOpt : '');

            $migrate = filter_var($migrateOption === '' ? $this->confirm('Do you want to migrate tables now?') : $migrateOption, FILTER_VALIDATE_BOOLEAN);
            $allowedEnvs = ['production', 'development', 'testing'];
            $env = $this->option('env');

            if (! in_array($env, $allowedEnvs)) {
                $env = $this->choice('Select application environment', $allowedEnvs);
            }

            $this->call('install:db', [
                '--migrate' => $migrate,
                '--env' => $env,
            ]);
        } else {
            $this->alert("Please run 'php artisan install:db' to complete the installation.");
        }
    }
}
