<?php

namespace Database\Seeders\v4_0_2_4_RC_1;

use App\Model\Common\PipedriveLocalFields;
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
            ['field_name' => 'State', 'field_key' => 'state'],
            ['field_name' => 'Town', 'field_key' => 'town'],
            ['field_name' => 'Mobile', 'field_key' => 'mobile'],
            ['field_name' => 'Country', 'field_key' => 'country'],
            ['field_name' => 'Company', 'field_key' => 'company'],
            ['field_name' => 'Address', 'field_key' => 'address'],
            ['field_name' => 'Email', 'field_key' => 'email'],
            ['field_name' => 'User Name', 'field_key' => 'user_name'],
            ['field_name' => 'First Name', 'field_key' => 'first_name'],
            ['field_name' => 'Last Name', 'field_key' => 'last_name'],
            ['field_name' => 'Timezone', 'field_key' => 'timezone_id'],
        ];

        foreach ($fields as $field) {
            PipedriveLocalFields::updateOrCreate(
                ['field_key' => $field['field_key']],
                $field
            );
        }
    }
}