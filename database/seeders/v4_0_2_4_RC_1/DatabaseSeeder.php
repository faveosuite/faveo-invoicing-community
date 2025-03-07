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
        setEnvValue('APP_PREVIOUS_KEYS', 'SomeRandomString');
    }
}
