<?php

namespace Database\Seeders\v4_0_2_7_RC_1;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->removeBugsnag();
    }

    public function removeBugsnag()
    {
        $paths = [
            base_path('vendor' . DIRECTORY_SEPARATOR . 'bugsnag' . DIRECTORY_SEPARATOR . 'bugsnag'),
            base_path('vendor' . DIRECTORY_SEPARATOR . 'bugsnag' . DIRECTORY_SEPARATOR . 'bugsnag-laravel'),
            base_path('vendor' . DIRECTORY_SEPARATOR . 'bugsnag'),
            config_path('bugsnag.php'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                if (is_dir($path)) {
                    $this->deleteDirectory($path);
                } else {
                    @unlink($path);
                }
            }
        }
    }

    private function deleteDirectory($dir)
    {
        if (! is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        if ($items === false) {
            return;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}