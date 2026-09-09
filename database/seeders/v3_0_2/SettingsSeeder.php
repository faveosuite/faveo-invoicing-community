<?php

namespace Database\Seeders\v3_0_2;

use App\Model\Common\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::where('id', 1)->update([
            'free_trail_expired' => 17,
            'Free_trail_gonna_expired' => 18,
        ]);
    }
}
