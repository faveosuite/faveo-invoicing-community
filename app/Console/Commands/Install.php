<?php

namespace App\Console\Commands;

use App\Http\Controllers\BillingInstaller\BillingDependencyController;
use App\Http\Controllers\BillingInstaller\InstallerController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:agora';

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
    protected $install;

    public function __construct()
    {
        $this->install = new InstallerController();
        parent::__construct();
    }

    public function handle()
    {
        try {
            $this->displayArtLogo();

            if (! $this->appEnv()) {
                $this->info('Agora cannot be installed on your server. Please configure your server to meet the requirements and try again.');

                return;
            }

            $appUrl = $this->ask('Enter your app url (with only https)');

            if (! $this->appReq($appUrl)) {
                $this->info('Agora cannot be installed on your server. Please configure your server to meet the requirements and try again.');

                return;
            }

            if (! $this->confirm('Do you want to install Agora?')) {
                $this->info('We hope you will try next time.');

                return;
            }

            $defaultSqlEngine = $this->choice('Which SQL engine would you like to use?', ['mysql']);
            $formattedAppUrl = $this->formatAppUrl($appUrl);
            $host = $this->ask('Enter your SQL host');
            $database = $this->ask('Enter your database name');
            $dbusername = $this->ask('Enter your database username');
            $dbpassword = $this->ask('Enter your database password (leave blank if none)', false);
            $port = $this->ask('Enter your SQL port (leave blank if none)', false);

            \Cache::put('search-driver', 'database');

//            $response = $this->install->configurationcheck(new Request([
//                'host' => $host,
//                'databasename' => $database,
//                'username' => $dbusername,
//                'password' => $dbpassword,
//                'port' => $port,
//            ]));
//
//            $responseData = json_decode($response->getContent(), true);
//
//            foreach ($responseData['results'] as $result) {
//                if ($result['status'] === 'Error') {
//                    if ($result['message'] === \Lang::get('installer_messages.database_not_empty')) {
//                        if ($this->confirm("This {$database} database contains data. Do you want to drop the tables?")) {
//                            $this->call('droptables');
//                        } else {
//                            $this->error("Installation aborted. The {$database} database contains existing data, and the tables were not dropped.");
//
//                            return;
//                        }
//                    } else {
//                        $this->error($result['message']);
//
//                        return;
//                    }
//                } else {
//                    $this->info($result['message']);
//                }
//                $this->info('');
//            }

            $this->install->env($defaultSqlEngine, $host, $port, $database, $dbusername, $dbpassword, $formattedAppUrl);
            $this->info('.env file has been created');
            $this->call('preinstall:check');
            $this->info('');
            $this->alert("Please run 'php artisan install:db'");
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
    }

    public function formatAppUrl(string $url): string
    {
        if (str_finish($url, '/')) {
            $url = rtrim($url, '/ ');
        }

        return $url;
    }

    public function appEnv()
    {
        $extensions = ['curl', 'ctype', 'imap', 'mbstring', 'openssl', 'tokenizer', 'pdo_mysql', 'zip', 'pdo', 'mysqli', 'iconv', 'XML', 'json', 'fileinfo', 'gd'];
        $result = [];
        $can_install = true;
        foreach ($extensions as $key => $extension) {
            $result[$key]['extension'] = $extension;
            if (! extension_loaded($extension)) {
                $result[$key]['status'] = "Not Loading, Please open please open '".php_ini_loaded_file()."' and add 'extension = ".$extension;
                $can_install = false;
            } else {
                $result[$key]['status'] = 'Loading';
            }
        }
        $result['php']['extension'] = 'PHP';
        if (phpversion() >= '8.2.0') {
            $result['php']['status'] = 'PHP version supports';
        } else {
            $can_install = false;
            $result['php']['status'] = "PHP version doesn't supports please upgrade to 8.2.0 +";
        }

        $headers = ['Extension', 'Status'];
        $this->table($headers, $result);

        return $can_install;
    }

    public function appReq($appUrl)
    {
        $canInstall = true;
        $arrayOfRequisites = [];
        $errorCount = 0;
        $connectionStatus = (new BillingDependencyController('probe'))->checkSSLCertificateOnDomain($arrayOfRequisites, $errorCount, $appUrl)[0]['connection'];
        if ($connectionStatus != 'Valid SSL certificate found, application can be served securely over HTTPS') {
            $canInstall = false;
        }
        $this->table(['Requisites', 'Status'], [['requisite' => 'ssl_certificate', 'status' => $connectionStatus]]);

        return $canInstall;
    }

    private function displayArtLogo()
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
}
