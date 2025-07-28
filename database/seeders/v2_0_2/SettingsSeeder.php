<?php

namespace Database\Seeders\v2_0_2;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Model\Common\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Model\Common\Setting::where('id',1)->update([
            'autosubscription_going_to_end' => 12,
            'payment_successfull' => 13,
            'payment_failed' => 14,
            'card_failed' => 15,

        ]);
    }
}