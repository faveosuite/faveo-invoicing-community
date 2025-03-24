<?php

namespace Database\Seeders\v4_0_2_4_RC_1;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->updateAppKey();
    }

    private function updateAppKey()
    {
        $env = base_path().DIRECTORY_SEPARATOR.'.env';

        if (is_file($env) && config('app.env') !== 'testing') {

            setEnvValue(['APP_PREVIOUS_KEYS' => 'SomeRandomString']);

            \Artisan::call('key:generate', ['--force' => true]);

        }
    }
}
