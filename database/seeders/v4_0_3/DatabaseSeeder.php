<?php

namespace Database\Seeders\v4_0_3;

use App\Model\Common\CommonSettings;
use App\Plugins\Zoho\Models\FaveoLocalFields;
use App\Plugins\Zoho\Models\ZohoIntegration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
       $this->openPaymentEmailTemplates();
       $this->seedSentrySettings();
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
    public function openPaymentEmailTemplates(): void
    {
        $types = ['open_payment_success', 'open_payment_failed', 'open_payment_admin_success', 'open_payment_admin_failed'];

        foreach ($types as $name) {
            if (! DB::table('template_types')->where('name', $name)->exists()) {
                DB::table('template_types')->insert(['name' => $name, 'selected_template_id' => null, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        $successId       = DB::table('template_types')->where('name', 'open_payment_success')->value('id');
        $failedId        = DB::table('template_types')->where('name', 'open_payment_failed')->value('id');
        $adminSuccessId  = DB::table('template_types')->where('name', 'open_payment_admin_success')->value('id');
        $adminFailedId   = DB::table('template_types')->where('name', 'open_payment_admin_failed')->value('id');

        $this->seedTemplate('Payment Received', $successId, '
<table style="background:#f2f2f2;width:700px;" border="0" cellspacing="0" cellpadding="0"><tbody>
<tr><td style="width:30px;">&nbsp;</td><td style="width:640px;padding-top:30px;"><h2 style="color:#333;font-family:Arial,sans-serif;font-size:18px;font-weight:bold;margin:0;">{{logo}}</h2></td><td style="width:30px;">&nbsp;</td></tr>
<tr><td style="width:30px;">&nbsp;</td><td style="width:640px;padding-top:30px;">
<table style="width:640px;border-bottom:1px solid #ccc;" border="0" cellspacing="0" cellpadding="0"><tbody>
<tr>
<td style="background:#fff;border-left:1px solid #ccc;border-top:1px solid #ccc;width:40px;padding:10px;">&nbsp;</td>
<td style="background:#fff;border-top:1px solid #ccc;padding:40px 0 20px 0;width:560px;" align="left">
Dear {{name}},<br/><br/>
<h1 style="color:#0088cc;font-family:Arial,sans-serif;font-size:22px;font-weight:bold;margin:0 0 16px 0;">Payment Successful!</h1>
<p style="color:#333;font-family:Arial,sans-serif;font-size:14px;line-height:22px;">Your payment has been received successfully. Thank you!</p>
<table style="margin:20px 0;width:560px;border:1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<thead><tr style="background-color:#f8f8f8;">
<th style="color:#333;font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:12px 10px;" align="left">Transaction ID</th>
<th style="color:#333;font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:12px 10px;" align="left">Amount</th>
<th style="color:#333;font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:12px 10px;" align="left">Gateway</th>
<th style="color:#333;font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:12px 10px;" align="left">Date</th>
</tr></thead>
<tbody><tr>
<td style="color:#333;font-family:monospace;font-size:12px;padding:12px 10px;">{{transaction_id}}</td>
<td style="color:#333;font-family:Arial,sans-serif;font-size:13px;padding:12px 10px;font-weight:bold;">{{currency}} {{amount}}</td>
<td style="color:#333;font-family:Arial,sans-serif;font-size:13px;padding:12px 10px;">{{gateway}}</td>
<td style="color:#333;font-family:Arial,sans-serif;font-size:13px;padding:12px 10px;">{{date}}</td>
</tr></tbody></table>
<p style="color:#555;font-family:Arial,sans-serif;font-size:13px;">Please keep this email as your payment confirmation.</p>
</td>
<td style="background:#fff;border-right:1px solid #ccc;border-top:1px solid #ccc;width:40px;padding:10px;">&nbsp;</td>
</tr></tbody></table>
</td><td style="width:30px;">&nbsp;</td></tr>
<tr><td style="width:30px;padding:10px;">&nbsp;</td><td style="padding:20px 0 10px 0;width:640px;" align="left">{{contact}}</td><td style="width:30px;padding:10px;">&nbsp;</td></tr>
</tbody></table>');

        $this->seedTemplate('Payment Failed', $failedId, '
<table style="background:#f2f2f2;width:700px;" border="0" cellspacing="0" cellpadding="0"><tbody>
<tr><td style="width:30px;">&nbsp;</td><td style="width:640px;padding-top:30px;"><h2 style="color:#333;font-family:Arial,sans-serif;font-size:18px;font-weight:bold;margin:0;">{{logo}}</h2></td><td style="width:30px;">&nbsp;</td></tr>
<tr><td style="width:30px;">&nbsp;</td><td style="width:640px;padding-top:30px;">
<table style="width:640px;border-bottom:1px solid #ccc;" border="0" cellspacing="0" cellpadding="0"><tbody>
<tr>
<td style="background:#fff;border-left:1px solid #ccc;border-top:1px solid #ccc;width:40px;padding:10px;">&nbsp;</td>
<td style="background:#fff;border-top:1px solid #ccc;padding:40px 0 20px 0;width:560px;" align="left">
Dear {{name}},<br/><br/>
<h1 style="color:#cc0000;font-family:Arial,sans-serif;font-size:22px;font-weight:bold;margin:0 0 16px 0;">Payment Failed</h1>
<p style="color:#333;font-family:Arial,sans-serif;font-size:14px;line-height:22px;">Unfortunately your payment of <strong>{{currency}} {{amount}}</strong> via <strong>{{gateway}}</strong> could not be processed.</p>
<p style="color:#333;font-family:Arial,sans-serif;font-size:14px;line-height:22px;">Please try again or contact our support team if the issue persists.</p>
</td>
<td style="background:#fff;border-right:1px solid #ccc;border-top:1px solid #ccc;width:40px;padding:10px;">&nbsp;</td>
</tr></tbody></table>
</td><td style="width:30px;">&nbsp;</td></tr>
<tr><td style="width:30px;padding:10px;">&nbsp;</td><td style="padding:20px 0 10px 0;width:640px;" align="left">{{contact}}</td><td style="width:30px;padding:10px;">&nbsp;</td></tr>
</tbody></table>');

        $this->seedTemplate('Open Payment Failed', $adminFailedId, '
<table style="background:#f2f2f2;width:700px;" border="0" cellspacing="0" cellpadding="0"><tbody>
<tr><td style="width:30px;">&nbsp;</td><td style="width:640px;padding-top:30px;"><h2 style="color:#333;font-family:Arial,sans-serif;font-size:18px;font-weight:bold;margin:0;">{{logo}}</h2></td><td style="width:30px;">&nbsp;</td></tr>
<tr><td style="width:30px;">&nbsp;</td><td style="width:640px;padding-top:30px;">
<table style="width:640px;border-bottom:1px solid #ccc;" border="0" cellspacing="0" cellpadding="0"><tbody>
<tr>
<td style="background:#fff;border-left:1px solid #ccc;border-top:1px solid #ccc;width:40px;padding:10px;">&nbsp;</td>
<td style="background:#fff;border-top:1px solid #ccc;padding:30px 0 20px 0;width:560px;" align="left">
<h1 style="color:#cc0000;font-family:Arial,sans-serif;font-size:20px;font-weight:bold;margin:0 0 20px 0;">Open Payment Failed</h1>
<table style="width:560px;border:1px solid #ccc;border-collapse:collapse;" border="0" cellspacing="0" cellpadding="0"><tbody>
<tr><td style="font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:10px 12px;width:180px;border-bottom:1px solid #eee;">Payer</td><td style="font-family:Arial,sans-serif;font-size:13px;padding:10px 12px;border-bottom:1px solid #eee;">{{name}} ({{company}})</td></tr>
<tr><td style="font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:10px 12px;border-bottom:1px solid #eee;">Email</td><td style="font-family:Arial,sans-serif;font-size:13px;padding:10px 12px;border-bottom:1px solid #eee;">{{email}}</td></tr>
<tr><td style="font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:10px 12px;border-bottom:1px solid #eee;">Amount</td><td style="font-family:Arial,sans-serif;font-size:13px;padding:10px 12px;border-bottom:1px solid #eee;">{{currency}} {{amount}}</td></tr>
<tr><td style="font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:10px 12px;border-bottom:1px solid #eee;">Gateway</td><td style="font-family:Arial,sans-serif;font-size:13px;padding:10px 12px;border-bottom:1px solid #eee;">{{gateway}}</td></tr>
<tr><td style="font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:10px 12px;">Date</td><td style="font-family:Arial,sans-serif;font-size:13px;padding:10px 12px;">{{date}}</td></tr>
</tbody></table>
</td>
<td style="background:#fff;border-right:1px solid #ccc;border-top:1px solid #ccc;width:40px;padding:10px;">&nbsp;</td>
</tr></tbody></table>
</td><td style="width:30px;">&nbsp;</td></tr>
<tr><td style="width:30px;padding:10px;">&nbsp;</td><td style="padding:20px 0 10px 0;width:640px;" align="left">{{contact}}</td><td style="width:30px;padding:10px;">&nbsp;</td></tr>
</tbody></table>');

        $this->seedTemplate('New Open Payment Received', $adminSuccessId, '
<table style="background:#f2f2f2;width:700px;" border="0" cellspacing="0" cellpadding="0"><tbody>
<tr><td style="width:30px;">&nbsp;</td><td style="width:640px;padding-top:30px;"><h2 style="color:#333;font-family:Arial,sans-serif;font-size:18px;font-weight:bold;margin:0;">{{logo}}</h2></td><td style="width:30px;">&nbsp;</td></tr>
<tr><td style="width:30px;">&nbsp;</td><td style="width:640px;padding-top:30px;">
<table style="width:640px;border-bottom:1px solid #ccc;" border="0" cellspacing="0" cellpadding="0"><tbody>
<tr>
<td style="background:#fff;border-left:1px solid #ccc;border-top:1px solid #ccc;width:40px;padding:10px;">&nbsp;</td>
<td style="background:#fff;border-top:1px solid #ccc;padding:30px 0 20px 0;width:560px;" align="left">
<h1 style="color:#0088cc;font-family:Arial,sans-serif;font-size:20px;font-weight:bold;margin:0 0 20px 0;">New Open Payment Received</h1>
<table style="width:560px;border:1px solid #ccc;border-collapse:collapse;" border="0" cellspacing="0" cellpadding="0"><tbody>
<tr><td style="font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:10px 12px;width:180px;border-bottom:1px solid #eee;">Payer</td><td style="font-family:Arial,sans-serif;font-size:13px;padding:10px 12px;border-bottom:1px solid #eee;">{{name}} ({{company}})</td></tr>
<tr><td style="font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:10px 12px;border-bottom:1px solid #eee;">Email</td><td style="font-family:Arial,sans-serif;font-size:13px;padding:10px 12px;border-bottom:1px solid #eee;">{{email}}</td></tr>
<tr><td style="font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:10px 12px;border-bottom:1px solid #eee;">Base Amount</td><td style="font-family:Arial,sans-serif;font-size:13px;padding:10px 12px;border-bottom:1px solid #eee;">{{currency}} {{base_amount}}</td></tr>
<tr><td style="font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:10px 12px;border-bottom:1px solid #eee;">Processing Fee ({{fee_rate}}%)</td><td style="font-family:Arial,sans-serif;font-size:13px;padding:10px 12px;border-bottom:1px solid #eee;">{{currency}} {{processing_fee}}</td></tr>
<tr><td style="font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:10px 12px;border-bottom:1px solid #eee;">Total Charged</td><td style="font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:10px 12px;border-bottom:1px solid #eee;">{{currency}} {{amount}}</td></tr>
<tr><td style="font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:10px 12px;border-bottom:1px solid #eee;">Gateway</td><td style="font-family:Arial,sans-serif;font-size:13px;padding:10px 12px;border-bottom:1px solid #eee;">{{gateway}}</td></tr>
<tr><td style="font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:10px 12px;border-bottom:1px solid #eee;">Transaction ID</td><td style="font-family:monospace;font-size:12px;padding:10px 12px;border-bottom:1px solid #eee;">{{transaction_id}}</td></tr>
<tr><td style="font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:10px 12px;">Date</td><td style="font-family:Arial,sans-serif;font-size:13px;padding:10px 12px;">{{date}}</td></tr>
</tbody></table>
</td>
<td style="background:#fff;border-right:1px solid #ccc;border-top:1px solid #ccc;width:40px;padding:10px;">&nbsp;</td>
</tr></tbody></table>
</td><td style="width:30px;">&nbsp;</td></tr>
<tr><td style="width:30px;padding:10px;">&nbsp;</td><td style="padding:20px 0 10px 0;width:640px;" align="left">{{contact}}</td><td style="width:30px;padding:10px;">&nbsp;</td></tr>
</tbody></table>');
    }

    private function seedTemplate(string $name, int $typeId, string $html): void
    {
        if (DB::table('templates')->where('name', $name)->exists()) return;

        $id = DB::table('templates')->insertGetId([
            'name'       => $name,
            'type'       => $typeId,
            'url'        => '',
            'data'       => trim($html),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('template_types')->where('id', $typeId)->update(['selected_template_id' => $id, 'updated_at' => now()]);
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
            'bugsnag/bugsnag',
            'bugsnag/bugsnag-laravel',
        ];

        $configs = [
            'datatables.php',
            'datatables-buttons.php',
            'datatables-fractal.php',
            'dompdf.php',
            'currency.php',
            'bugsnag.php'
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


    private function seedSentrySettings(): void
    {
        $settings = [
            // Migrate debug settings from config (sourced from .env) → DB for existing users
            ['option_name' => 'debugging', 'optional_field' => 'app_debug', 'option_value' => config('app.debug') ? '1' : '0'],
            ['option_name' => 'debugging', 'optional_field' => 'pulse_enabled', 'option_value' => config('pulse.enabled') ? '1' : '0'],
            ['option_name' => 'debugging', 'optional_field' => 'clockwork_enable', 'option_value' => config('clockwork.enable') ? '1' : '0'],
            // Sentry defaults: crash reporting ON, performance monitoring OFF
            ['option_name' => 'sentry', 'optional_field' => 'crash_reporting', 'option_value' => '1'],
            ['option_name' => 'sentry', 'optional_field' => 'performance_monitoring', 'option_value' => '0'],
        ];

        foreach ($settings as $setting) {
            CommonSettings::updateOrCreate(
                ['option_name' => $setting['option_name'], 'optional_field' => $setting['optional_field']],
                ['option_value' => $setting['option_value']]
            );
        }
    }

}