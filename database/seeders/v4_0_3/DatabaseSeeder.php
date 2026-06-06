<?php

namespace Database\Seeders\v4_0_3;

use App\Plugins\Zoho\Models\FaveoLocalFields;
use App\Plugins\Zoho\Models\ZohoIntegration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $this->faveoLocalFieldsSeeder();
       $this->zohoSeeder();
       $this->packageRemoval();
    }

    public function faveoLocalFieldsSeeder(): void
    {
        $fields = [
            ['label' => 'User Name',  'key' => 'user_name',  'type' => 'string'],
            ['label' => 'First Name', 'key' => 'first_name', 'type' => 'string'],
            ['label' => 'Last Name',  'key' => 'last_name',  'type' => 'string'],
            ['label' => 'Email',      'key' => 'email',      'type' => 'email'],
            ['label' => 'Mobile',     'key' => 'mobile',     'type' => 'phone'],
            ['label' => 'Company',    'key' => 'company',    'type' => 'string'],
            ['label' => 'Address',    'key' => 'address',    'type' => 'text'],
            ['label' => 'Town',       'key' => 'town',       'type' => 'string'],
            ['label' => 'State',      'key' => 'state',      'type' => 'string'],
            ['label' => 'Country',    'key' => 'country',    'type' => 'string'],
            ['label' => 'Created At', 'key' => 'created_at', 'type' => 'datetime'],
        ];

        FaveoLocalFields::insert(
            collect($fields)->map(fn ($field) => [
                'display_name' => $field['label'],
                'field_key'    => $field['key'],
                'field_type'   => $field['type'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ])->toArray()
        );
    }

    public function zohoSeeder(): void
    {
        $integrations = [
            [
                'id' => 1,
                'platform'       => 'crm',
                'description'    => 'Zoho CRM integration for managing leads, contacts, and sales automation.',
            ],
            [
                'id' => 2,
                'platform'       => 'campaigns',
                'description'    => 'Zoho Campaigns integration for managing email marketing and subscriber lists.',
            ],
        ];

        foreach ($integrations as $integration) {
            ZohoIntegration::updateOrCreate(
                ['id' => $integration['id']],
                [
                    'platform'    => $integration['platform'],
                    'description' => $integration['description'],
                    'is_active'   => false,
                ]
            );
        }
    }
    public function packageRemoval(): void
    {
        $packages = [
            'simplesoftwareio/simple-qrcode',
            'swiftmailer/swiftmailer',
            'rachidlaasri/laravel-installer',
            'yajra/laravel-datatables',
            'anhskohbo/no-captcha',
            'barryvdh/laravel-dompdf',
            'torann/currency',
            'devio/pipedrive',
            'slavka/mailchimp-apiv3',
        ];

        $configs = [
            'datatables.php',
            'datatables-buttons.php',
            'datatables-fractal.php',
            'dompdf.php',
            'currency.php'
        ];

        foreach ($packages as $package) {

            $packagePath = base_path("vendor/{$package}");

            if (! File::exists($packagePath)) {
                continue;
            }

            File::deleteDirectory($packagePath);

            $authorPath = dirname($packagePath);

            if (
                File::exists($authorPath)
                && File::isDirectory($authorPath)
                && count(File::files($authorPath)) === 0
                && count(File::directories($authorPath)) === 0
            ) {
                File::deleteDirectory($authorPath);
            }
        }

        foreach ($configs as $config) {

            $configPath = config_path($config);

            if (File::exists($configPath)) {
                File::delete($configPath);
            }
        }
    }

}