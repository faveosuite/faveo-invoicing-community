<?php

namespace Database\Seeders\v2_0_0;

use App\ApiKey;
use App\Model\Common\Mailchimp\MailchimpFieldAgoraRelation;
use App\Model\Common\Mailchimp\MailchimpSetting;
use App\Model\Common\PricingTemplate;
use App\Model\Common\StatusSetting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Github\Github;
use App\Model\License\LicensePermission;
use App\Model\Mailjob\ActivityLogDay;
use App\Model\Mailjob\Condition;
use App\Model\Payment\Period;
use App\Model\Payment\Plan;
use App\Model\Payment\Promotion;
use App\Model\Payment\PromotionType;
use App\Model\Payment\TaxOption;
use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Model::unguard();

        $this->call([TemplateTypeTableSeeder::class]);
        $this->command->info('Template Type table seeded!');

        $this->call([PeriodTypeTableSeeder::class]);
        $this->command->info('Period table seeded!');

        $this->call([TemplateTableSeeder::class]);
        $this->command->info('Template table seeded!');

        $this->call([LicensePermissionTableSeeder::class]);
        $this->command->info('License Permission table seeded');

        // $this->call('GroupTableSeeder');
        // $this->command->info('Product Group table seeded!');

        // $this->call('ProductTypesTableSeeder');
        // $this->command->info('Product Types table seeded!');

        $this->call([PromotionTypeTableSeeder::class]);
        $this->command->info('Promotion Types table seeded!');

        $this->call([mailchimpFieldAgoraSeeder::class]);
        $this->command->info('Mailchimp Field Agora Relation table seeded!');

        $this->call([mailchimpSettingSeeder::class]);
        $this->command->info('Mailchimp Settings table seeded!');

        $this->call([PromotionTableSeeder::class]);
        $this->command->info('Promotion table seeded!');

        $this->call([ApiKeyTableSeeder::class]);
        $this->command->info('ApiKey table seeded!');

        $this->call([TaxOptionTableSeeder::class]);
        $this->command->info('Tax Option Table seeded');

        // $this->call('ProductTableSeeder');
        // $this->command->info('Product table seeded!');

        $this->call([GitHubTableSeeder::class]);
        $this->command->info('Github table seeded!');

        $this->call([StatusSettingSeeder::class]);
        $this->command->info('Status Setting table seeded!');

        $this->call([PricingTemplateSeeder::class]);
        $this->command->info('Pricing Template Table Seeded!');
        
        
        $this->call([UserTableSeeder::class]);
        $this->command->info('User table seeded!');


        $this->call([ConditionSeeder::class]);
        $this->command->info('Condition table seeded!');

        $this->call([ActivityLogDelSeeder::class]);
        $this->command->info('Activity Log Days table seeded!');

        $this->call([FormatCurrenciesSeeder::class]);
        $this->command->info('Format Currencies table seeded!');
        
        // $this->call([FaveoCloudSeeder::class]);
        // $this->command->info('Format faveocloud table seeded!');

        $this->call([PluginSeeder::class]);
        $this->command->info('Format plugin table seeded!');

        $this->call(CompanySize::class);
        $this->call(CompanyType::class);
        $this->call(SettingsSeeder::class);
        $this->call(FrontPageSeeder::class);
        $this->call(BussinessSeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(CountrySeeder::class);
        $this->call(StateSubdivision::class);
        $this->call(BaseStateSubdivisionSeeder::class);
        $this->call(ExtendedStateSubdivisionSeeder::class);
        $this->call(StatesSeeder::class);
        $this->call(TaxByStatesSeeder::class);
        $this->call(TimezoneSeeder::class);
        $this->call(QueueServiceSeeder::class);
        $this->call(PlanPriceSeeder::class);

        $this->call(StripeSupportedCurrencySeeder::class);
        $this->command->info('Stripe supported currency table seeded!');

        $this->call(RazorpaySupportedCurrencySeeder::class);
        $this->command->info('Razorpay supported currency table seeded!');

        // DB::unprepared(file_get_contents(storage_path('agora.sql')));
        // DB::unprepared(file_get_contents(storage_path('states.sql')));
        // DB::unprepared(file_get_contents(storage_path('taxrates.sql')));
        // DB::unprepared(file_get_contents(storage_path('dummy-data.sql')));
    }
}

// class PlanTableSeeder extends Seeder
// {
//     public function run()
//     {
//         DB::statement('SET FOREIGN_KEY_CHECKS=0;');
//         DB::table('plans')->truncate();
//         DB::statement('SET FOREIGN_KEY_CHECKS=1;');
//         $subcriptions = [
//             0 => ['name' => 'no subcription', 'days' => 0],
//             1 => ['name' => 'one week', 'days' => 7],
//             2 => ['name' => 'one month', 'days' => 30],
//             3 => ['name' => 'three months', 'days' => 90],
//             4 => ['name' => 'six months', 'days' => 180],
//             5 => ['name' => 'one year', 'days' => 365],
//             6 => ['name' => 'three year', 'days' => 1095],
//             ];
//         //var_dump($subcriptions);
//         for ($i = 0; $i < count($subcriptions); $i++) {
//             Plan::create(['name' => $subcriptions[$i]['name'], 'product'=>'1', 'allow_tax'=>'1', 'days' => $subcriptions[$i]['days']]);
//         }
//     }
// }

class TaxOptionTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('tax_rules')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        TaxOption::create(['id' => 1, 'tax_enable' => 0, 'inclusive' => 0, 'rounding' => 1]);
    }
}

class ApiKeyTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('api_keys')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        ApiKey::create(['id' => 1, 'rzp_key' => '', 'rzp_secret' => '', 'apilayer_key' => '', 'bugsnag_api_key' => '', 'zoho_api_key' => '', 'msg91_auth_key' => '', 'twitter_consumer_key' => '', 'twitter_consumer_secret' => '', 'twitter_access_token' => '', 'access_tooken_secret' => '', 'license_api_secret' => '', 'license_api_url' => '', 'update_api_url' => '', 'update_api_secret' => '', 'terms_url' => '', 'pipedrive_api_key' => '']);
    }
}

class PeriodTypeTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('periods')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Period::create(['id' => 1, 'name' => '1 Year', 'days' => '366']);
        Period::create(['id' => 2, 'name' => '2 Years', 'days' => '730']);
        Period::create(['id' => 3, 'name' => '3 Years', 'days' => '1095']);
        Period::create(['id' => 4, 'name' => '4 Years', 'days' => '1460']);
        Period::create(['id' => 5, 'name' => '5 Years', 'days' => '1825']);
        Period::create(['id' => 6, 'name' => '6 Years', 'days' => '2190']);
        Period::create(['id' => 7, 'name' => '7 Years', 'days' => '2555']);
        Period::create(['id' => 8, 'name' => '8 Years', 'days' => '2920']);
        Period::create(['id' => 9, 'name' => '9 Years', 'days' => '3285']);
        Period::create(['id' => 10, 'name' => '1 Month', 'days' => '30']);
        Period::create(['id' => 11, 'name' => '2 Months', 'days' => '60']);
        Period::create(['id' => 12, 'name' => '3 Months', 'days' => '90']);
        Period::create(['id' => 13, 'name' => '6 Months', 'days' => '180']);
        Period::create(['id' => 14, 'name' => 'One Time', 'days' => '365']);
    }
}

class PromotionTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('promotions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Promotion::create(['id' => 1, 'code' => 'none', 'type' => 1, 'uses' => 0, 'value' => 'none', 'start' => '1000-01-01 00:00:00', 'expiry' => '1000-01-01 00:00:00']);
    }
}

class PromotionTypeTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('promotion_types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        PromotionType::create(['id' => 1, 'name' => 'Percentage']);
        PromotionType::create(['id' => 2, 'name' => 'Fixed Amount']);
    }
}

class mailchimpFieldAgoraSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('mailchimp_field_agora_relations')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        MailchimpFieldAgoraRelation::create(['id' => 1]);
    }
}

class mailchimpSettingSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('mailchimp_settings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        MailchimpSetting::create(['id' => 1]);
    }
}

class TemplateTypeTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('template_types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        TemplateType::create(['id' => 1, 'name' => 'welcome_mail']);
        TemplateType::create(['id' => 2, 'name' => 'forgot_password_mail']);
        TemplateType::create(['id' => 4, 'name' => 'subscription_going_to_end_mail']);
        TemplateType::create(['id' => 5, 'name' => 'subscription_over_mail']);
        TemplateType::create(['id' => 6, 'name' => 'invoice_mail']);
        TemplateType::create(['id' => 7, 'name' => 'order_mail']);
        TemplateType::create(['id' => 8, 'name' => 'download_mail']);
        TemplateType::create(['id' => 9, 'name' => 'sales_manager_email']);
        TemplateType::create(['id' => 10, 'name' => 'account_manager_email']);
        TemplateType::create(['id' => 11, 'name' => 'password_mail']);
    }
}

class TemplateTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('templates')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Template::create(['id' => 2, 'name' => '[Faveo Helpdesk] Verify your email address', 'type' => 1, 'url' => 'null', 'data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="width: 30px;"> </td>
<td style="width: 640px; padding-top: 30px;">
<h2 style="color: #333; font-family: Arial, sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;"><img src="https://billing.faveohelpdesk.com/common/images/faveo1.png" alt="Faveo Helpdesk" /></h2>
</td>
<td style="width: 30px;"> </td>
</tr>
<tr>
<td style="width: 30px;"> </td>
<td style="width: 640px;  padding-top: 30px;">
<table style="width: 640px; border-bottom: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;"> </td>
<td style="background: #fff; border-top: 1px solid #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">
<p>Dear {$name}, <br /><br /> Before you can login, you must active your account. Click <a href="{$url}">{$url}</a> to activate your account.<br /><br /> <strong>Your Profile & Control Panel Login</strong><br /><br /> You can start exploring our feature-rich Control Panel, which will allow you to manage all your Products, buy new Products, check all your transactions and more.<br /><br /> <strong>Login Details:</strong><br /> <strong>URL: </strong><a href="{$website_url}">{$website_url}</a> <br /> <strong>Username:</strong> {$username}<br /><br /> <strong>Password:</strong> If you can not recall your current password, <a href="{$website_url}/password/reset">click here</a> to request a new password to login.<br /><br /> Thank You.<br /> Regards,<br /> Faveo Helpdesk</p>Thank You.<br /> Regards,<br /> Faveo Helpdesk</p>
</td>
<td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;"> </td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;"> </td>
<td style="background: #fff; padding: 0; width: 560px;" align="left"> </td>
<td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;"> </td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;"> </td>
<td style="background: #fff; padding: 20px 0 50px 0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{$url}" target="_blank">Verify Email </a></td>
<td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;"> </td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px;"> </td>
</tr>
<tr>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;"> </td>
<td style="padding: 20px 0 10px 0; width: 640px;" align="left">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">SALES CONTACT</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">TECHNICAL SUPPORT</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-abbreviated" href="mailto:sales@faveohelpdesk.com">sales@faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-freetext" href="https://www.support.faveohelpdesk.com">www.support.faveohelpdesk.com</a></p>
</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;">Ladybird Web Solution Pvt Ltd<br /><a class="moz-txt-link-abbreviated" href="mailto:accounts@ladybirdweb.com">accounts@ladybirdweb.com</a><br /><a class="moz-txt-link-freetext" href="https://www.faveohelpdesk.com">www.faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;"> </td>
</tr>
</tbody>
</table>
<p> </p>']);

        Template::create(['id' => 4, 'name' => '[Faveo Helpdesk] Purchase confirmation', 'type' => 7, 'url' => 'null', 'data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<h2 style="color: #333; font-family: Arial,sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;"><img src="https://billing.faveohelpdesk.com/common/images/faveo1.png" alt="Faveo Helpdesk" /></h2>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<table style="width: 640px;border-bottom: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="background: #fff; border-left: 1px solid                      #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px; border-radius: 5px                      0 0 0;">&nbsp;</td>
<td style="background: #fff; border-top: 1px solid                      #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">Dear {$name},<br /><br />
<h1 style="color: #0088cc; font-family: Arial,sans-serif; font-size: 24px; font-weight: bold; padding: 0; margin: 0;">Thanks for your {$product} order</h1>
<br /> Your order and payment details are below.</td>
<td style="background: #fff; border-right: 1px solid                      #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px; border-radius: 0                      5px 0 0;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 0; width: 560px;" align="left">
<table style="margin: 25px 0 30px                        0; width: 560px; border: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<thead>
<tr style="background-color: #f8f8f8;">
<th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Order Number</th>
<th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Product</th>
<th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Expiry</th>
</tr>
</thead>
<tbody>
<tr>
<td style="border-bottom: 1px solid#ccc; color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; padding: 15px                              8px;" valign="top">{$number}</td>
<td style="border-bottom: 1px solid#ccc; color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; padding: 15px                              8px;" valign="top">{$product}</td>
<td style="border-bottom: 1px solid#ccc; color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; padding: 15px                              8px;" valign="top">{$expiry}</td>
</tr>
</tbody>
</table>
<p><a class="moz-txt-link-abbreviated" href="{$serialkeyurl}" target="_blank" rel="noopener"> Click Here</a> to get your License Code.</p>
<p><a class="moz-txt-link-abbreviated" href="{$knowledge_base$}/category-list/installation-and-upgrade-guide"> Refer To Our Knowledge Base</a> for further installation assistance</p>
<p style="color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Click below to login to your Control Panel to view invoice or to pay for any pending invoice.</p>
</td>
<td style="background: #fff; border-right: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 20px 0 50px                      0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{$invoiceurl}" target="_blank"> View Invoice </a></td>
<td style="background: #fff; border-right: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="padding: 20px 0 10px 0; width: 640px;" align="left">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">SALES CONTACT</td>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">TECHNICAL SUPPORT</td>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-abbreviated" href="mailto:sales@faveohelpdesk.com">sales@faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-freetext" href="https://www.support.faveohelpdesk.com">www.support.faveohelpdesk.com</a></p>
</td>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;">Ladybird Web Solution Pvt Ltd<br /> <a class="moz-txt-link-abbreviated" href="mailto:accounts@ladybirdweb.com">accounts@ladybirdweb.com</a><br /> <a class="moz-txt-link-freetext" href="https://www.faveohelpdesk.com">www.faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>']);

        Template::create(['id' => 5, 'name' => '[Faveo Helpdesk] Reset your password', 'type' => 2, 'url' => 'null', 'data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<h2 style="color: #333; font-family: Arial, sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;"><img src="https://billing.faveohelpdesk.com/common/images/faveo1.png" alt="Faveo Helpdesk" /></h2>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<table style="width: 640px;border-bottom: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; border-top: 1px solid #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">
<p>Dear {$name},<br /><br /> A request to reset password was received from your account.&nbsp; Use the link below to reset your password and login.<br /><br /> <strong>Link:</strong>&nbsp; <a href="{$url}">{$url}</a><br /><br /> Thank You.<br /> Regards,<br /> Faveo Helpdesk<br /><br /> <strong>IMP:</strong> If you have not initiated this request, <a href="{$contact-us}/contact-us/">report it to us immediately</a>.<br /><br /> <em>This is an automated email, please do not reply.</em></p>
</td>
<td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 20px 0 50px 0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{$url}" target="_blank">Reset Password </a></td>
<td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="padding: 20px 0 10px 0; width: 640px;" align="left">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">SALES CONTACT</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">TECHNICAL SUPPORT</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-abbreviated" href="mailto:sales@faveohelpdesk.com">sales@faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-freetext" href="https://www.support.faveohelpdesk.com">www.support.faveohelpdesk.com</a></p>
</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;">Ladybird Web Solution Pvt Ltd<br /><a class="moz-txt-link-abbreviated" href="mailto:support@ladybirdweb.com">accounts@ladybirdweb.com</a><br /><a class="moz-txt-link-freetext" href="https://www.faveohelpdesk.com">www.faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>']);

        Template::create(['id' => 6, 'name' => '[Faveo Helpdesk] Consolidated renewal reminder', 'type' => 4, 'url' => 'null', 'data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<h2 style="color: #333; font-family: Arial, sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;"><img src="https://billing.faveohelpdesk.com/common/images/faveo1.png" alt="Faveo Helpdesk" /></h2>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<table style="width: 640px;border-bottom: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; border-top: 1px solid #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">Dear {$name},<br /><br />
<h1 style="color: #0088cc; font-family: Arial, sans-serif; font-size: 24px; font-weight: bold; padding: 0; margin: 0;">Your orders are expiring soon.<br /> Renew them now.</h1>
</td>
<td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 0; width: 560px;" align="left">
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Some of your orders are expiring soon (or have already expired.) Please renew them before they are deleted to avoid loss of data.</p>
<table style="margin: 25px 0 30px 0; width: 560px; border: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<thead>
<tr style="background-color: #f8f8f8;">
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Order ID</th>
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Product</th>
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Expiry Date</th>
</tr>
</thead>
<tbody>
<tr>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{$number}</td>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{$product}</td>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{$expiry} <br /><span style="color: #ad7b33; font-family: Arial, sans-serif; font-size: 12px;">Expiring Soon!</span></td>
</tr>
</tbody>
</table>
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Click the button below to login to your Control Panel and renew your orders.</p>
</td>
<td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 20px 0 50px 0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{$url}" target="_blank"> Renew Order </a></td>
<td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="padding: 20px 0 10px 0; width: 640px;" align="left">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">SALES CONTACT</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">TECHNICAL SUPPORT</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-abbreviated" href="mailto:sales@faveohelpdesk.com">sales@faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-freetext" href="https://www.support.faveohelpdesk.com">www.support.faveohelpdesk.com</a></p>
</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;">Ladybird Web Solution Pvt Ltd<br /><a class="moz-txt-link-abbreviated" href="mailto:accounts@ladybirdweb.com">accounts@ladybirdweb.com</a><br /><a class="moz-txt-link-freetext" href="https://www.faveohelpdesk.com">www.faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>']);

        Template::create(['id' => 7, 'name' => '[Faveo Helpdesk] URGENT: Order has expired', 'type' => 5, 'url' => 'null', 'data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<h2 style="color: #333; font-family: Arial, sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;"><img src="https://billing.faveohelpdesk.com/common/images/faveo1.png" alt="Faveo Helpdesk" /></h2>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<table style="width: 640px;border-bottom: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; border-top: 1px solid #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">Dear {$name},<br /><br />
<h1 style="color: #0088cc; font-family: Arial, sans-serif; font-size: 24px; font-weight: bold; padding: 0; margin: 0;">Your orders has expired.<br /> Renew them now.</h1>
</td>
<td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 0; width: 560px;" align="left">
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Some of your orders are expired. Please renew them before they are deleted to avoid loss of data.</p>
<table style="margin: 25px 0 30px 0; width: 560px; border: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<thead>
<tr style="background-color: #f8f8f8;">
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Order ID</th>
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Product</th>
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Expiry Date</th>
</tr>
</thead>
<tbody>
<tr>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{$number}</td>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{$product}</td>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{$expiry}</td>
</tr>
</tbody>
</table>
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Click the button below to login to your Control Panel and renew your orders.</p>
</td>
<td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 20px 0 50px 0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{$url}" target="_blank"> Renew Order </a></td>
<td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="padding: 20px 0 10px 0; width: 640px;" align="left">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">SALES CONTACT</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">TECHNICAL SUPPORT</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-abbreviated" href="mailto:sales@faveohelpdesk.com">sales@faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-freetext" href="https://www.support.faveohelpdesk.com">www.support.faveohelpdesk.com</a></p>
</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;">Ladybird Web Solution Pvt Ltd<br /><a class="moz-txt-link-abbreviated" href="mailto:accounts@ladybirdweb.com">accounts@ladybirdweb.com</a><br /><a class="moz-txt-link-freetext" href="https://www.faveohelpdesk.com">www.faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>']);

        Template::create(['id' => 8, 'name' => '[Faveo Helpdesk] Invoice', 'type' => 6, 'url' => 'null', 'data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<h2 style="color: #333; font-family: Arial,sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;"><img src="https://billing.faveohelpdesk.com/common/images/faveo1.png" alt="Faveo Helpdesk" /></h2>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<table style="width: 640px;border-bottom: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="background: #fff; border-left: 1px solid                      #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px; border-radius: 5px                      0 0 0;">&nbsp;</td>
<td style="background: #fff; border-top: 1px solid                      #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">Dear {$name},<br /><br />
<h1 style="color: #0088cc; font-family: Arial,sans-serif; font-size: 24px; font-weight: bold; padding: 0; margin: 0;">Thanks for your order</h1>
<br /> Your order and payment details are below.</td>
<td style="background: #fff; border-right: 1px solid                      #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px; border-radius: 0                      5px 0 0;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 0; width: 560px;" align="left">
<table style="margin: 25px 0 30px                        0; width: 560px; border: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<thead>
<tr style="background-color: #f8f8f8;">
<th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Invoice Number</th>
<th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Product</th>
<th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Total</th>
</tr>
</thead>
<tbody>
<tr>
<td>&nbsp;{$content}</td>
</tr>
</tbody>
</table>
<p style="color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Click below to login to your Control Panel to view invoice or to pay for any pending invoice.</p>
</td>
<td style="background: #fff; border-right: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 20px 0 50px                      0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{$invoiceurl}" target="_blank"> Invoice </a></td>
<td style="background: #fff; border-right: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="padding: 20px 0 10px 0; width: 640px;" align="left">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">SALES CONTACT</td>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">TECHNICAL SUPPORT</td>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-abbreviated" href="mailto:sales@faveohelpdesk.com">sales@faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;"><a class="moz-txt-link-freetext" href="https://www.support.faveohelpdesk.com">www.support.faveohelpdesk.com</a></p>
</td>
<td style="color: #333; font-family: Arial,sans-serif; font-size: 11px; padding-left: 25px;" valign="top">
<p style="line-height: 20px;">Ladybird Web Solution Pvt Ltd<br /> <a class="moz-txt-link-abbreviated" href="mailto:accounts@ladybirdweb.com">accounts@ladybirdweb.com</a><br /> <a class="moz-txt-link-freetext" href="https://www.faveohelpdesk.com">www.faveohelpdesk.com</a><br /> Tel: +91 80 3075 2618</p>
</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>']);

        Template::create(['id' => 9, 'name' => '[Faveo Helpdesk] Your New Sales Manager', 'type' => 9, 'url' => 'null', 'data' => '<p>Dear {$name},</p>
<p>This is {$manager_first_name} {$manager_last_name}.</p>
<p>From now onwards I will be your one point of contact. I will followup with you as well as with our team. Please feel free to get in touch with me anytime if you have any issues with regards to your account. You can also add me on Skype. My ID is mentioned in my signature. It is a pleasure to have you on board and I look forward to effective conversations with you in future.</p>
<p>Hope you have a great day.</p>
<p>Regards,</p>
<p>{$manager_first_name}{$manager_last_name}</p>
<p>Sales Manager,<br /> Faveo Helpdesk<br /> Mobile :{$manager_code} {$manager_mobile}<br /> Skype ID : {$manager_skype}<br /> Email : {$manager_email}</p>']);

        Template::create(['id' => 10, 'name' => '[Faveo Helpdesk] Your New Account Manager', 'type' => 10, 'url' => 'null', 'data' => '<p>Dear {$name},</p>
<p>This is {$manager_first_name} {$manager_last_name}.</p>
<p>From now onwards I will be your one point of contact. I will followup with you as well as with our team. Please feel free to get in touch with me anytime if you have any issues with regards to your account. You can also add me on Skype. My ID is mentioned in my signature. It is a pleasure to have you on board and I look forward to effective conversations with you in future.</p>
<p>Hope you have a great day.</p>
<p>Regards,</p>
<p>{$manager_first_name}{$manager_last_name}</p>
<p>Account Manager,<br /> Faveo Helpdesk<br /> Mobile :{$manager_code} {$manager_mobile}<br /> Skype ID : {$manager_skype}<br /> Email : {$manager_email}</p>']);

        Template::create(['id' => 11, 'name' => '[Faveo Helpdesk] Your New Password', 'type' => 11, 'url' => 'null', 'data' => '<table style="background: #f2f2f2 none repeat scroll 0% 0%; width: 700px; height: 524px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr style="height: 134px;">
<td style="width: 28px; height: 134px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px; height: 134px;">
<h2 style="color: #333; font-family: Arial, sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;"><img src="https://billing.faveohelpdesk.com/common/images/faveo1.png" alt="Faveo Helpdesk" /></h2>
</td>
<td style="width: 28px; height: 134px;">&nbsp;</td>
</tr>
<tr style="height: 350px;">
<td style="width: 28px; height: 350px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px; height: 350px;">
<table style="width: 635px; border-bottom: 1px solid #cccccc; height: 300px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr style="height: 271px;">
<td style="background: #ffffff none repeat scroll 0% 0%; border-left: 1px solid #cccccc; border-top: 1px solid #cccccc; width: 38px; padding-top: 10px; padding-bottom: 10px; height: 271px;">&nbsp;</td>
<td style="background: #ffffff none repeat scroll 0% 0%; border-top: 1px solid #cccccc; padding: 40px 0px 10px; width: 554.75px; height: 271px;" align="left">
<p>Dear {$name}, <br /><br />your new password is generated sucessfully<br /><br /><strong>Login Details:</strong><br /><strong>Username:</strong> {$username}<br /><strong>Password:</strong> {$password}<br /><br />Thank You.<br />Regards,<br />Faveo Helpdesk</p>
</td>
<td style="background: #ffffff none repeat scroll 0% 0%; border-right: 1px solid #cccccc; border-top: 1px solid #cccccc; width: 38.25px; padding-top: 10px; padding-bottom: 10px; height: 271px;">&nbsp;</td>
</tr>
<tr style="height: 19px;">
<td style="background: #ffffff none repeat scroll 0% 0%; border-left: 1px solid #cccccc; width: 38px; padding-top: 10px; padding-bottom: 10px; height: 19px;">&nbsp;</td>
<td style="background: #ffffff none repeat scroll 0% 0%; padding: 0px; width: 554.75px; height: 19px;" align="left">&nbsp;</td>
<td style="background: #ffffff none repeat scroll 0% 0%; border-right: 1px solid #cccccc; width: 38.25px; padding-top: 10px; padding-bottom: 10px; height: 19px;">&nbsp;</td>
</tr>
<tr style="height: 10px;">
<td style="background: #ffffff none repeat scroll 0% 0%; border-left: 1px solid #cccccc; width: 38px; padding-top: 10px; padding-bottom: 10px; height: 10px;">&nbsp;</td>
<td style="background: #ffffff none repeat scroll 0% 0%; padding: 20px 0px 50px; width: 554.75px; height: 10px;" align="left">&nbsp;</td>
<td style="background: #ffffff none repeat scroll 0% 0%; border-right: 1px solid #cccccc; width: 38.25px; padding-top: 10px; padding-bottom: 10px; height: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 28px; height: 350px;">&nbsp;</td>
</tr>
<tr style="height: 40px;">
<td style="width: 28px; padding-top: 10px; padding-bottom: 10px; height: 40px;">&nbsp;</td>
<td style="padding: 20px 0px 10px; width: 640px; height: 40px;" align="left">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">&nbsp;</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">&nbsp;</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">&nbsp;</td>
</tr>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">&nbsp;</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">&nbsp;</td>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 11px; padding-left: 25px;" valign="top">&nbsp;</td>
</tr>
</tbody>
</table>
</td>
<td style="width: 28px; padding-top: 10px; padding-bottom: 10px; height: 40px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>',
        ]);
    }
}

class LicensePermissionTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('license_permissions')->truncate();
        LicensePermission::create(['id' => 1, 'permissions' => 'Can be Downloaded']);
        LicensePermission::create(['id' => 2, 'permissions' => 'Generate License Expiry Date']);
        LicensePermission::create(['id' => 3, 'permissions' => 'Generate Updates Expiry Date']);
        LicensePermission::create(['id' => 4, 'permissions' => 'Generate Support Expiry Date']);
        LicensePermission::create(['id' => 5, 'permissions' => 'No Permissions']);
        LicensePermission::create(['id' => 6, 'permissions' => 'Allow Downloads Before Updates Expire']);
    }
}

class StatusSettingSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('status_settings')->truncate();
        StatusSetting::create(['id' => 1, 'expiry_mail' => 0, 'activity_log_delete' => 0, 'license_status' => 0, 'github_status' => 0, 'mailchimp_status' => 0, 'twitter_status' => 0, 'msg91_status' => 0, 'emailverification_status' => 0, 'recaptcha_status' => 0, 'update_settings' => 0, 'zoho_status' => 0, 'rzp_status' => 0, 'mailchimp_product_status' => 0, 'mailchimp_ispaid_status' => 0, 'terms' => 0, 'pipedrive_status' => 0]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

class PricingTemplateSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('pricing_templates')->truncate();
        PricingTemplate::create(['id' => 1, 'data' => '<div class="col-md-3 col-sm-6">
                            <div class="plan">
                                <div class="plan-header">
                                    <h3>{{name}}</h3>
                                </div>
                                <div class="plan-price">
                                    <span class="price">{{price}}</span>
                                    <br>
                                    <label class="price-label">{{price-description}}</label>
                                </div>
                                <div class="plan-features">
                                    <ul>
                                    <li>{{feature}}</li>
                                </ul>
                                     
                                </div>
                                <div class="plan-footer">
                                <div class="subscription table-responsive">{{subscription}}</div><br/>
                                <div>{{url}} </div>
                                </div>
                                
                            </div>
                        </div>', 'image' => 'pricing_template1.png', 'name' => 'Porto Theme(With Gap Style)']);
    }
}

class GitHubTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('githubs')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Github::create(['id' => 1, 'client_id' => null, 'client_secret' => null, 'username' => null, 'password' => null]);
    }
}

class ConditionSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['job' => 'expiryMail', 'value' => 'everyFiveMinutes'],
            ['job' => 'deleteLogs', 'value' => 'daily'],

        ];
        foreach ($data as $job) {
            Condition::updateOrCreate($job);
        }
    }
}

class ActivityLogDelSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('activity_log_days')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        ActivityLogDay::create(['id' => 1, 'days' => '']);
    }
}

class FormatCurrenciesSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('format_currencies')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        DB::table('format_currencies')->insert(['id' => 1, 'name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$', 'format' => '$1,0.00', 'exchange_rate' => 0, 'active' => 0]);
        DB::table('format_currencies')->insert(['id' => 2, 'name' => 'Indian Rupee', 'code' => 'INR', 'symbol' => '₹', 'format' => '₹1,0.00', 'exchange_rate' => 0, 'active' => 0]);
    }
}

// class FaveoCloudSeeder extends Seeder
// {
//     public function run()
//     {
//         DB::statement('SET FOREIGN_KEY_CHECKS=0;');
//         DB::table('faveo_cloud')->truncate();
//         DB::statement('SET FOREIGN_KEY_CHECKS=1;');
//         DB::table('faveo_cloud')->insert(['id' => 1, 'cloud_central_domain' => 'https://billing.faveocloud.com', 'cron_server_url' => 'http://165.227.242.64', 'cron_server_key' => '31ba9b727ee347d12ffcb891c064cf9032a8b1d62480690894870df05ebda47c']);
//     }
// }

class PluginSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('plugins')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        DB::table('plugins')->insert(['id' => 1, 'name' => 'Ccavenue', 'path' => 'Ccavenue', 'status' => '0']);
        DB::table('plugins')->insert(['id' => 2, 'name' => 'Ccavenue', 'path' => 'Ccavenue', 'status' => '0']);
        DB::table('plugins')->insert(['id' => 3, 'name' => 'Stripe', 'path' => 'Stripe', 'status' => '1']);
        DB::table('plugins')->insert(['id' => 4, 'name' => 'Razorpay', 'path' => 'Razorpay', 'status' => '1']);
    }
}

class UserTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // DB::table('users')->truncate();
        DB::table('users')->where('email','demo@admin.com')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return \App\User::create([
            'first_name' => 'Demo',
            'last_name' => 'Admin',
            'user_name' => 'demo',
            'email' => 'demo@admin.com',
            'role' => 'admin',
            'password' => Hash::make('password'),
            'active' => 1,
            'mobile_verified' => 1,
            'currency' => 'INR',
            'company' => 'My Company Name',
            'mobile' => '',
            'mobile_code' => '',
            'address' => '',
            'town' => '',
            'country' => 'IN',
            'state' => 'IN-KA',
            'zip' => '',
            'profile_pic' => '',
            'debit' => 0,
        ]);
    }
}
