<?php

namespace Database\Seeders\v2_0_0;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySize extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('company_sizes')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $sizes = ['Myself only', '2-10', '11-50', '51-200', '201-500', '501-1000', '1001-5000', '5001-10000', '10001+'];
        foreach ($sizes as $size) {
            DB::table('company_sizes')->insert([
                'short' => str_slug($size),
                'name' => $size,
            ]);
        }
    }
}
