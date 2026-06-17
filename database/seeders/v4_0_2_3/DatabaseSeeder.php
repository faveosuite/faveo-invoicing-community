<?php

declare(strict_types=1);

namespace Database\Seeders\v4_0_2_3;

use App\Model\User\AccountActivate;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->deleteOldOTP();
    }

    private function deleteOldOTP(): void
    {
        AccountActivate::query()->delete();
    }
}
