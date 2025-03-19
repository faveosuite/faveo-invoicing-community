<?php

namespace Database\Seeders\v4_0_2_4_RC_1;

use App\Model\Common\PipedriveField;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->addFielsForPipedrive();
    }

    private function addFielsForPipedrive()
    {
        $fields = [
            ['field_name' => 'Name', 'field_key' => null, 'field_type' => 'varchar', 'active' => true],
            ['field_name' => 'Email', 'field_key' => null, 'field_type' => 'varchar', 'active' => true],
            ['field_name' => 'Phone', 'field_key' => null, 'field_type' => 'phone', 'active' => true],
            ['field_name' => 'Country', 'field_key' => null, 'field_type' => 'varchar', 'active' => true],
        ];

        foreach ($fields as $field) {
            PipedriveField::updateOrCreate(
                ['field_name' => $field['field_name']],
                $field
            );
        }
    }
}