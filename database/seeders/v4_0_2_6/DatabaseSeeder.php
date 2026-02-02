<?php

namespace Database\Seeders\v4_0_2_6;

use App\Model\Common\FaveoCloud;
use App\Model\Order\InstallationDetail;
use App\Model\Product\Subscription;
use App\ThirdPartyApp;
use File;
use GuzzleHttp\Client;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->packageRemoval();
    }

    public function packageRemoval(): void
    {
        $packages = [
            'simplesoftwareio/simple-qrcode',
            'swiftmailer/swiftmailer',
            'rachidlaasri/laravel-installer',
            'anhskohbo/no-captcha',
            'torann/currency',
            'devio/pipedrive'
        ];

        $configs = [
            'currency.php',
            'log-viewer.php'
        ];

        foreach ($packages as $package) {

            $packagePath = base_path("vendor/{$package}");

            if (! File::exists($packagePath)) {
                continue;
            }

            File::deleteDirectory($packagePath);

            $authorPath = dirname($packagePath);

            if (
                File::exists($authorPath)
                && File::isDirectory($authorPath)
                && count(File::files($authorPath)) === 0
                && count(File::directories($authorPath)) === 0
            ) {
                File::deleteDirectory($authorPath);
            }
        }

        foreach ($configs as $config) {

            $configPath = config_path($config);

            if (File::exists($configPath)) {
                File::delete($configPath);
            }
        }
    }
}