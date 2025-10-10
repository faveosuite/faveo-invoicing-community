<?php

namespace Database\Seeders\v4_0_2_4;

use App\Model\Common\ManagerSetting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->addMangerRole();
    }

    public function addMangerRole(): void
    {
        foreach (['account', 'sales'] as $role) {
            ManagerSetting::updateOrCreate(
                ['manager_role' => $role],
                ['auto_assign' => 0]
            );
        }
    }
}