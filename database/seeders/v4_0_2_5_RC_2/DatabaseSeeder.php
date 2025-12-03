<?php

namespace Database\Seeders\v4_0_2_5_RC_2;

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

    public function packageRemoval()
    {
        $paths = [
            base_path('vendor' . DIRECTORY_SEPARATOR . 'arcanedev'),
            base_path('vendor' . DIRECTORY_SEPARATOR . 'shvetsgroup'),
            config_path('log-viewer.php')
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
        if (!is_dir($dir)) {
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