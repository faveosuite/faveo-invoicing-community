<?php

namespace Database\Seeders\v4_0_2_2_1;

use App\Model\Product\ProductUpload;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->movePreReleaseData();
    }

    private function movePreReleaseData(): void
    {
        ProductUpload::where('is_pre_release', 1)->each(function ($product): void {
           $product->update(['release_type' => 'pre_release']);
        });
    }
}



