<?php

namespace Database\Seeders\v3_0_5;
use App\Model\Mailjob\ExpiryMailDay;

use Illuminate\Database\Seeder;

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
    ExpiryMailDay::truncate();
    ExpiryMailDay::create([
    'id' => 1,
    'days' => '["30","15","7","1"]',
    'autorenewal_days' => '["30","15","7","1"]',
    'postexpiry_days' => '["30","15","7","1"]',
    'cloud_days' => 7

    
        
]);
    }
  }