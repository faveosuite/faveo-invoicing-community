<?php

namespace Database\Seeders\v3_0_5;
use App\Model\Mailjob\ExpiryMailDay;

use App\Model\Common\Template;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ExpiryMailDaySeeder::class);
       
    }

}


class ExpiryMailDaySeeder extends Seeder
   {
    public function run()
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