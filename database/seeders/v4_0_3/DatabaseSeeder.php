<?php

namespace Database\Seeders\v4_0_3;

use App\Model\Common\CommonSettings;
use App\Model\Common\SeoDefaultPage;
use App\Plugins\Zoho\Models\FaveoLocalFields;
use App\Plugins\Zoho\Models\ZohoIntegration;
use App\ReportColumn;
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
        $this->seedCacheSessionDefaults();
        $this->seedLicensesReportColumns();
        $this->seedSeoDefaultPages();
        $this->seedSeoSettings();
        $this->razorpayAutoRenewSetupEmailTemplate();
    }

    /**
     * Default title/description templates used for Pages-module pages and
     * Product Groups that don't have their own specific meta_title/
     * meta_description — e.g. "{name} | {company}" instead of a bare name.
     * See App\Services\Seo\SeoTemplateFormatter.
     */
    public function seedSeoSettings(): void
    {
        $defaults = [
            'pages_title_format' => '{name} | {company}',
            'groups_title_format' => '{name} | {company}',
            'pages_description_format' => 'Learn more about {name} at {company}.',
            'groups_description_format' => 'Learn more about {name} at {company}.',
            'pages_og_title_format' => '{name} | {company}',
            'groups_og_title_format' => '{name} | {company}',
            'pages_og_description_format' => 'Learn more about {name} at {company}.',
            'groups_og_description_format' => 'Learn more about {name} at {company}.',
        ];

        foreach ($defaults as $field => $value) {
            CommonSettings::firstOrCreate(
                ['option_name' => 'seo', 'optional_field' => $field],
                ['option_value' => $value, 'status' => '']
            );
        }
    }

    /**
     * Fixed 3-row SEO editor for non-authenticated default pages
     * (login/forgot-password/reset-password). No separate "home" or
     * "register" rows: "/" always redirects through to /login for any
     * unauthenticated visitor (i.e. every crawler), so it's the same page,
     * not distinct content — and `/login` (clientRouter.js) renders
     * LoginRegister.vue, which shows the login and register forms side by
     * side on one page (verified in the component template, not assumed).
     * There is no distinct /register URL, so "login" covers both.
     */
    public function seedSeoDefaultPages(): void
    {
        $pages = [
            ['page_key' => 'login', 'meta_title' => 'Login & Register', 'meta_description' => 'Sign in or create a new account to access your dashboard, invoices, and orders.'],
            ['page_key' => 'forgot_password', 'meta_title' => 'Forgot Password', 'meta_description' => 'Reset your account password securely.'],
            ['page_key' => 'reset_password', 'meta_title' => 'Reset Password', 'meta_description' => 'Choose a new password for your account.'],
        ];

        // Insertion order (above) determines display order via `id` — this
        // fixed list is edit-only (no create/delete endpoint), so id order
        // won't drift.
        foreach ($pages as $page) {
            SeoDefaultPage::firstOrCreate(
                ['page_key' => $page['page_key']],
                ['meta_title' => $page['meta_title'], 'meta_description' => $page['meta_description']]
            );
        }
    }

    /**
     * Column definitions for the admin Licenses list (/licenses/list), consumed
     * by the Vue ColumnSelector. Keys match the dataColumns names in
     * LicensesIndex.vue so visibility/order map without translation.
     */
    public function seedLicensesReportColumns(): void
    {
        $columns = [
            ['key' => 'license_code',          'label' => 'License code'],
            ['key' => 'client_email',          'label' => 'Email'],
            ['key' => 'product_title',         'label' => 'Product'],
            ['key' => 'license_order_number',  'label' => 'Order number'],
            ['key' => 'license_domain',        'label' => 'Domain'],
            ['key' => 'license_ip',            'label' => 'IP'],
            ['key' => 'license_date',          'label' => 'License date'],
            ['key' => 'installation_counts',   'label' => 'Installations count'],
            ['key' => 'call_backs_count',      'label' => 'Callbacks count'],
            ['key' => 'latest_call_backs',     'label' => 'Latest callbacks'],
            ['key' => 'license_limit',         'label' => 'License limit'],
            ['key' => 'license_expire_date',   'label' => 'License expiry'],
            ['key' => 'license_updates_date',  'label' => 'Updates expiry'],
            ['key' => 'license_support_date',  'label' => 'Support expiry'],
            ['key' => 'license_status',        'label' => 'Status'],
            ['key' => 'actions',               'label' => 'Actions'],
        ];

        foreach ($columns as $col) {
            ReportColumn::firstOrCreate(
                ['type' => 'licenses', 'key' => $col['key']],
                ['label' => $col['label'], 'default' => 1]
            );
        }
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
            collect($fields)->map(fn ($field): array => [
                'display_name' => $field['label'],
                'field_key' => $field['key'],
                'field_type' => $field['type'],
                'created_at' => now(),
                'updated_at' => now(),
            ])->all()
        );
    }

    public function zohoSeeder(): void
    {
        $integrations = [
            [
                'id' => 1,
                'platform' => 'crm',
                'description' => 'Zoho CRM integration for managing leads, contacts, and sales automation.',
            ],
            [
                'id' => 2,
                'platform' => 'campaigns',
                'description' => 'Zoho Campaigns integration for managing email marketing and subscriber lists.',
            ],
        ];

        foreach ($integrations as $integration) {
            ZohoIntegration::updateOrCreate(
                ['id' => $integration['id']],
                [
                    'platform' => $integration['platform'],
                    'description' => $integration['description'],
                    'is_active' => false,
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

        $successId = DB::table('template_types')->where('name', 'open_payment_success')->value('id');
        $failedId = DB::table('template_types')->where('name', 'open_payment_failed')->value('id');
        $adminSuccessId = DB::table('template_types')->where('name', 'open_payment_admin_success')->value('id');
        $adminFailedId = DB::table('template_types')->where('name', 'open_payment_admin_failed')->value('id');

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

    /**
     * A distinct email template for "just opted in to auto-renewal at
     * checkout, please authorize the recurring mandate" — separate from the
     * existing 'stripe_subscription_authentication' template (despite its
     * name, used for Razorpay), which is worded for renewal:cron sending it
     * near an order's actual renewal date ("your subscription is about to
     * expire, complete your payment now", with a "Make Payment" button).
     * Sending that wording immediately after a fresh purchase reads as a
     * second payment demand, when nothing is actually due yet.
     */
    public function razorpayAutoRenewSetupEmailTemplate(): void
    {
        if (! DB::table('template_types')->where('name', 'razorpay_autorenew_setup')->exists()) {
            DB::table('template_types')->insert(['name' => 'razorpay_autorenew_setup', 'selected_template_id' => null, 'created_at' => now(), 'updated_at' => now()]);
        }

        $typeId = DB::table('template_types')->where('name', 'razorpay_autorenew_setup')->value('id');

        $this->seedTemplate('Set up automatic renewal for your order', $typeId, '
<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
   <tbody>
   <tr>
   <td style="width: 30px;">&nbsp;</td>
   <td style="width: 640px; padding-top: 30px;">
   <h2 style="color: #333; font-family: Arial, sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;">{{logo}}</h2>
   </td>
   <td style="width: 30px;">&nbsp;</td>
   </tr>
   <tr>
   <td style="width: 30px;">&nbsp;</td>
   <td style="width: 640px; padding-top: 30px;">
   <table style="width: 640px; border-bottom: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
   <tbody>
   <tr>
   <td style="background: #fff; border-left: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
   <td style="background: #fff; border-top: 1px solid #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">Dear {{name}},<br /><br />
   <h1 style="color: #0088cc; font-family: Arial, sans-serif; font-size: 24px; font-weight: bold; padding: 0; margin: 0;">Set up automatic renewal for your order</h1>
   </td>
   <td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
   </tr>
   <tr>
   <td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
   <td style="background: #fff; padding: 0; width: 560px;" align="left">
   <p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Thanks for your order with {{company_title}} — you chose to enable auto-renewal, so it stays active without any extra effort on your part.</p>
   <p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;"><strong>No payment is due right now.</strong> To finish setting this up, please authorize automatic renewal below. You\'ll only be charged {{total}} when it\'s actually time to renew, on {{date}}.</p>
   <table style="margin: 25px 0 30px 0; width: 560px; border: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
      <thead>
      <tr style="background-color: #f8f8f8;">
      <th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Product</th>
      <th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Order No</th>
      <th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Renews at</th>
      </tr>
      </thead>
      <tbody>
      <tr>
      <td style="border-bottom: 1px solid#ccc; color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{product}}</td>
      <td style="border-bottom: 1px solid#ccc; color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{number}}</td>
      <td style="border-bottom: 1px solid#ccc; color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{total}}</td>
      </tr>
      </tbody>
      </table>
   <p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Click the button below to authorize automatic renewal.</p>
   </td>
   <td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
   </tr>
   <tr>
   <td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
   <td style="background: #fff; padding: 20px 0 50px 0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{{url}}" target="_blank" rel="noopener"> Authorize Auto-Renewal </a><br><br>
      <p style="font-family:sans-serif;font-weight:normal;padding:0;margin:0;font-size:14px;line-height:19px;margin-bottom:10px;color: grey">
         This link will expire on {{expiry_date}}. You can also set this up anytime from your order page.
     </p>
   </td>
   <td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
   </tr>
   </tbody>
   </table>
   </td>
   <td style="width: 30px;">&nbsp;</td>
   </tr>
   <tr>
   <td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
   <td style="padding: 20px 0 10px 0; width: 640px;" align="left">{{contact}}</td>
   <td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
   </tr>
   </tbody>
   </table>
   <p>&nbsp;</p>');
    }

    private function seedTemplate(string $name, int $typeId, string $html): void
    {
        if (DB::table('templates')->where('name', $name)->exists()) {
            return;
        }

        $id = DB::table('templates')->insertGetId([
            'name' => $name,
            'type' => $typeId,
            'url' => '',
            'data' => trim($html),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('template_types')->where('id', $typeId)->update(['selected_template_id' => $id, 'updated_at' => now()]);
    }

    public function packageRemoval(): void
    {
        $packages = [
            'simplesoftwareio/simple-qrcode',   // replaced by bacon/bacon-qr-code (already a dependency)
            'swiftmailer/swiftmailer',           // EOL; replaced by symfony/mailer
            'rachidlaasri/laravel-installer',   // unused installer scaffold; removed to reduce attack surface
            'yajra/laravel-datatables',         // replaced by v-tables-3 on the Vue frontend
            'anhskohbo/no-captcha',             // replaced by the in-house Recaptcha plugin
            'barryvdh/laravel-dompdf',          // replaced by spatie/laravel-pdf
            'torann/currency',                  // unused; currency logic moved in-house
            'devio/pipedrive',                  // replaced by the official pipedrive/pipedrive SDK
            'slavka/mailchimp-apiv3',           // replaced by the in-house Mailchimp plugin
            'bugsnag/bugsnag',                  // replaced by sentry/sentry-laravel
            'bugsnag/bugsnag-laravel',          // replaced by sentry/sentry-laravel
            'graham-campbell/markdown',         // redundant wrapper; league/commonmark is already a direct dependency
            'symfony/templating',               // deprecated since Symfony 5.4, removed in Symfony 7
            'endroid/qr-code-bundle',           // Symfony Bundle — wrong architecture for Laravel; bacon/bacon-qr-code already covers QR generation
            'cartalyst/stripe-laravel',         // superseded by the official stripe/stripe-php SDK; single usage migrated to StripeClient
        ];

        $configs = [
            'datatables.php',         // belonged to yajra/laravel-datatables
            'datatables-buttons.php', // belonged to yajra/laravel-datatables
            'datatables-fractal.php', // belonged to yajra/laravel-datatables
            'dompdf.php',             // belonged to barryvdh/laravel-dompdf
            'currency.php',           // belonged to torann/currency
            'bugsnag.php',            // belonged to bugsnag/bugsnag-laravel
            'markdown.php',           // belonged to graham-campbell/markdown
        ];

        foreach ($packages as $package) {

            $packagePath = base_path('vendor/'.$package);

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

    private function seedCacheSessionDefaults(): void
    {
        $defaults = [
            ['option_name' => 'cache', 'optional_field' => 'driver', 'option_value' => 'file', 'status' => ''],
        ];

        foreach ($defaults as $setting) {
            CommonSettings::firstOrCreate(
                ['option_name' => $setting['option_name'], 'optional_field' => $setting['optional_field']],
                ['option_value' => $setting['option_value']]
            );
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
