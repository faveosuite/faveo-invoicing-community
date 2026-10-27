<?php

namespace Database\Seeders\v3_0_5;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(ExpiryMailDaySeeder::class);

    }
}

class ExpiryMailDaySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('expiry_mail_days')->truncate();
        DB::table('expiry_mail_days')->insert([
            'id' => 1,
            'days' => '["30","15","7","1"]',
            'autorenewal_days' => '["30","15","7","1"]',
            'postexpiry_days' => '["30","15","7","1"]',
            'cloud_days' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
