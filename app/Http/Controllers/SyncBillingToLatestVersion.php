<?php

namespace App\Http\Controllers;

use App\Model\Common\Setting;
use App\Model\Mailjob\QueueService;
use Artisan;
use Cache;
use Config;
use DB;
use Exception;

class SyncBillingToLatestVersion
{
    private $log = '';

    public function sync()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');
        set_time_limit(0);

        // in case where isInstall is false(in case of new install) version number should be zero
        $latestVersion = $this->getPHPCompatibleVersionString(Config::get('app.version'));
        $olderVersion = $this->getOlderVersion();
        (env('DB_ENGINE') == 'InnoDB') ?: $this->forceInnodbOnUpdate();

        try {
            if (version_compare($latestVersion, $olderVersion) == 1) {
                $this->updateToLatestVersion($latestVersion, $olderVersion);
            }

            // Setting::first()->update(['version'=> 'v'.$latestVersion]);
            DB::table('settings')->update(['version' => 'v'.$latestVersion]);

            $this->cacheDbVersion();
            $this->clearViewCache();
            $this->clearConfig();
            isInstall() && $this->restartHorizon();
            $this->storageLink();
        } catch (Exception $ex) {
            if (! isInstall()) {
                //if system is not installed chances are logs tables are not present
                throw $ex;
            }

            $this->log = $this->log."\n".$ex->getMessage();
        }

        return $this->log;
    }

    private function forceInnodbOnUpdate()
    {
        try {
            if (isInstall()) {
                $this->writeToEnvAndRunConfigClear('DB_ENGINE', 'InnoDB');
                $tables = DB::select('SHOW TABLES');
                foreach ($tables as $table) {
                    foreach ($table as $value) {
                        DB::statement('ALTER TABLE '.$value.' ENGINE = InnoDB');
                    }
                }
            }
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    private function writeToEnvAndRunConfigClear($key, $value)
    {
        try {
            $path = app()->environmentFilePath();

            $escaped = preg_quote('='.env($key), '/');
            file_put_contents($path, preg_replace(
                "/^{$key}{$escaped}/m",
                "{$key}={$value}",
                file_get_contents($path)
            ));
            Artisan::call('config:clear');
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    private function cacheDbVersion()
    {
        $filesystemVersion = Config::get('app.version');
        Cache::forget($filesystemVersion);
        $dbversion = Cache::remember($filesystemVersion, 3600,
            //Caching version for 1 hr
            fn () => Setting::first()->value('version'));
    }

    private function getPHPCompatibleVersionString(string $version): string
    {
        return preg_replace('#v\.|v#', '', str_replace('_', '.', $version));
    }

    private function getOlderVersion(): string
    {
        if (! isInstall()) {
            return $this->getPHPCompatibleVersionString('v0.0.0');
        }

        $olderVersion = Setting::first()->version;
        $olderVersion = $olderVersion ?: 'v0.0.0';

        return $this->getPHPCompatibleVersionString($olderVersion);
    }

    public function updateToLatestVersion(string $latestVersion, string $olderVersion)
    {
        $this->updateMigrationTable($olderVersion);

        // after older version is updated, update to the latest version in which seeder versioning is implemented
        Artisan::call('migrate', ['--force' => true]);

        $this->handleArtisanLogs();

        // getting seeder base path
        $seederBasePath = base_path().DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'seeders';

        // get all directories inside seeder folder
        // sort versions from oldest to latest
        if (file_exists($seederBasePath)) {
            $seederVersions = scandir($seederBasePath);

            natsort($seederVersions);
            // convert older and newer version into underscore format
            $formattedOlderVersion = $olderVersion;
            foreach ($seederVersions as $version) {
                if (version_compare($this->getPHPCompatibleVersionString($version), $formattedOlderVersion) == 1) {
                    // scan for $version directory and get file names
                    $this->log = $this->log."\n"."Running Seeder for version $version";

                    Artisan::call('db:seed', ['--class' => "Database\Seeders\\$version\DatabaseSeeder", '--force' => true]);
                    $this->handleArtisanLogs();
                }
            }
        }
    }

    private function updateMigrationTable(string $olderVersion)
    {
        if ($olderVersion != '0.0.0') {
            Artisan::call('migrate', ['--force' => true]);
        }
    }

    private function handleArtisanLogs()
    {
        $this->log = $this->log."\n\n".Artisan::output();
    }

    private function clearViewCache()
    {
        Artisan::call('view:clear');
        $this->handleArtisanLogs();
    }

    private function clearConfig()
    {
        Artisan::call('config:clear');
        $this->handleArtisanLogs();
    }

    private function restartHorizon()
    {
        if (QueueService::where('status', 1)->value('short_name') != 'redis') {
            return;
        }

        Artisan::call('horizon:terminate');
        $this->handleArtisanLogs();
    }

    private function storageLink()
    {
        Artisan::call('storage:link');
        $this->handleArtisanLogs();
    }
}
