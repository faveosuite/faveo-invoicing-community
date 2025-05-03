<?php

namespace Database\Seeders\v4_0_2_4_RC_1;

use App\Model\Common\Msg91Status;
use App\Http\Controllers\Common\PipedriveController;
use App\Model\Common\PipedriveGroups;
use App\Model\Common\PipedriveLocalFields;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->addMsgStatus();
        $this->addFielsForPipedrive();
    }

    public function addMsgStatus()
    {
        $statuses = [
            ['status_code' => 0, 'status_label' => 'Pending'],
            ['status_code' => 1, 'status_label' => 'Delivered'],
            ['status_code' => 2, 'status_label' => 'Failed'],
            ['status_code' => 9, 'status_label' => 'NDNC'],
            ['status_code' => 16, 'status_label' => 'Rejected'],
            ['status_code' => 25, 'status_label' => 'Rejected'],
            ['status_code' => 17, 'status_label' => 'Blocked number'],
        ];

        foreach ($statuses as $status) {
            Msg91Status::updateOrCreate(
                ['status_code' => $status['status_code']],
                ['status_label' => $status['status_label']]
            );
        }
    }

    private function addFielsForPipedrive()
    {
        $fields = [
            ['field_name' => 'User Name', 'field_key' => 'user_name'],
            ['field_name' => 'First Name', 'field_key' => 'first_name'],
            ['field_name' => 'Last Name', 'field_key' => 'last_name'],
            ['field_name' => 'Email', 'field_key' => 'email'],
            ['field_name' => 'Mobile', 'field_key' => 'mobile'],
            ['field_name' => 'Company', 'field_key' => 'company'],
            ['field_name' => 'Address', 'field_key' => 'address'],
            ['field_name' => 'Town', 'field_key' => 'town'],
            ['field_name' => 'State', 'field_key' => 'state'],
            ['field_name' => 'Country', 'field_key' => 'country'],
            ['field_name' => 'Created At', 'field_key' => 'created_at'],
        ];

        $groups = [
            ['group_name' => 'Person'],
            ['group_name' => 'Organization'],
            ['group_name' => 'Deal'],
        ];

        foreach ($fields as $field) {
            PipedriveLocalFields::updateOrCreate(
                ['field_key' => $field['field_key']],
                $field
            );
        }

        foreach ($groups as $group) {
            PipedriveGroups::updateOrCreate(
                ['group_name' => $group['group_name']],
                $group
            );
        }
    }
}