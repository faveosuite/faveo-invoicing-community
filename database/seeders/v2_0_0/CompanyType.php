<?php

namespace Database\Seeders\v2_0_0;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyType extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('company_types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $types = ['Public Company', 'Self Employed', 'Non Profit', 'Privately Held', 'Partnership'];
        foreach ($types as $type) {
            DB::table('company_types')->insert([
                'short' => str_slug($type),
                'name' => $type,
            ]);
        }
    }
}
