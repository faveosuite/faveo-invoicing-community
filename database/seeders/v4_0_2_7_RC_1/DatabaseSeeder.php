<?php

declare(strict_types=1);

namespace Database\Seeders\v4_0_2_7_RC_1;

use App\License\Database\Seeders\LicenseModuleSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->licenseModuleSeeder();
    }

    private function licenseModuleSeeder(): void
    {
        $this->call([LicenseModuleSeeder::class]);
        $this->command->info('Seeded License Module!');
    }
}
