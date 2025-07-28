<?php

namespace Database\Seeders\v4_0_2_2_1;

use App\Model\Product\ProductUpload;
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
        $this->movePreReleaseData();
    }

    private function movePreReleaseData()
    {
        ProductUpload::where('is_pre_release', 1)->each(function ($product) {
           $product->update(['release_type' => 'pre_release']);
        });
    }
}



