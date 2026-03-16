<?php

namespace Database\Seeders\v4_0_2_6_RC_3;

use App\Model\Common\FaveoCloud;
use App\Model\Order\InstallationDetail;
use App\Model\Product\Subscription;
use App\ReportColumn;
use App\ThirdPartyApp;
use File;
use GuzzleHttp\Client;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->addGroupColum();
    }

    private function addGroupColum(): void
    {
        ReportColumn::firstOrCreate(
            ['key' => 'group_name', 'type' => 'orders'],
            ['label' => 'group_name', 'default' => '1']
        );
    }
}