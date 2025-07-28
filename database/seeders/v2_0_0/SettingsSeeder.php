<?php

namespace Database\Seeders\v2_0_0;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('settings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        \App\Model\Common\Setting::create([
            'company' => '',
            'city' => 'Bangalore',
            'state' => 'IN-KA',
            'country' => 'IN',
            'default_currency' => 'USD',
            'default_symbol' => '$',
            'website' => '',
            'logo' => 'faveo1.png',
            'admin_logo' => 'faveo1.png',
            'fav_icon' => 'faveo.png',
            'title' => 'Billing Application',
            'error_log' => 1,
            'invoice' => 8,
            'subscription_over' => 7,
            'subscription_going_to_end' => 6,
            'forgot_password' => 5,
            'order_mail' => 4,
            'welcome_mail' => 2,
            'download' => 9,
            'invoice_template' => 8,
            'file_storage' => 'storage/upload',
            'password_mail' => 11,

        ]);
    }
}
