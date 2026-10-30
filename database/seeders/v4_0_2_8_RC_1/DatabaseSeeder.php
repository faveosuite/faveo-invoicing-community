<?php

declare(strict_types=1);

namespace Database\Seeders\v4_0_2_8_RC_1;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

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
            'darryldecode/cart',         // no Laravel 13 support; cart lives in App\Model\Cart\*
            'driftingly/rector-laravel', // Rector dropped as a dev tool
            'rector/rector',             // pulled in by driftingly/rector-laravel
            'webmozart/assert',          // only Rector used it
        ];

        $configs = [
            'shopping_cart.php', // belonged to darryldecode/cart
        ];

        foreach ($packages as $package) {

            $packagePath = base_path('vendor/'.$package);

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
