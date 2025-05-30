<?php

namespace Database\Seeders\v4_0_2_4_RC_1;

use App\ApiKey;
use App\Model\Common\EmailMobileValidationProviders;
use App\Model\Common\Msg91Status;
use App\Http\Controllers\Common\PipedriveController;
use App\Model\Common\PipedriveGroups;
use App\Model\Common\PipedriveLocalFields;
use App\Model\Github\Github;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->addMsgStatus();
        $this->removeOldGitPassword();
        $this->updateAppKey();
        $this->addFielsForPipedrive();
        $this->add_providers();
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

    public function removeOldGitPassword()
    {
        Github::where('id', 1)->update(['password' => null]);
    }

    private function updateAppKey()
    {
        $env = base_path() . DIRECTORY_SEPARATOR . '.env';

        if (is_file($env) && config('app.env') !== 'testing' && env('APP_KEY_UPDATED') !== 'true') {

            setEnvValue(['APP_PREVIOUS_KEYS' => 'SomeRandomString']);

            \Artisan::call('key:generate', ['--force' => true]);

            setEnvValue(['APP_KEY_UPDATED' => 'true']);
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



    public function add_providers(){
        $providers =[ ['provider'=>'reoon','type'=>'email'],
            ['provider'=>'vonage','type'=>'mobile'],
            ['provider'=>'abstract','type'=>'mobile'],
        ];
        foreach ($providers as $provider) {
            EmailMobileValidationProviders::updateOrCreate([
                'provider' => $provider['provider'],
                'type' => $provider['type'],
            ]);
        }
    }
}