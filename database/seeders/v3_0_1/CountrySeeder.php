<?php

namespace Database\Seeders\v3_0_1;


use App\Model\Common\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

      Country::where('nicename','Tajikistan')->delete();
       
      }
}