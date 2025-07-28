<?php

namespace Database\Seeders\v3_0_1;


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
        $this->call(StatesSubdivisionSeeder::class);
        $this->call(CountrySeeder::class);
    }
}