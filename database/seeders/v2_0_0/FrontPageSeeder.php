<?php

namespace Database\Seeders\v2_0_0;

use App\DefaultPage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FrontPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('frontend_pages')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        DefaultPage::create([
            'page_id' => '1',
            'page_url' => url('/my-invoices'),
        ]);
    }
}
