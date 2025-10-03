<?php

namespace Database\Seeders\v4_0_2_5_RC_1;


use App\Plugins\Recaptcha\Model\RecaptchaSetting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createRecaptcha();
    }

    public function createRecaptcha()
    {
        RecaptchaSetting::firstOrCreate([]);
    }
}