<?php

namespace Database\Seeders\v3_0_3;
use Illuminate\Database\Seeder;
use App\Model\Common\TemplateType;
use App\Model\Common\StatusSetting;
use App\Model\Common\Template;
use App\Demo_page;
use Illuminate\Support\Facades\DB;
use App\SocialLogin;
use App\Model\Common\PricingTemplate;
class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([Demo_pageTableSeeder::class]);

        $this->call([TemplateTypeTableSeeder::class]);
        $this->command->info('Template Type table seeded!');

        $this->call([TemplateTableSeeder::class]);
        $this->command->info('Template table seeded!');
        $this->SocialLoginSeeder();


        $this->call([PricingTemplateSeeder::class]);
        $this->command->info('Pricing Template Table Seeded!');
    }

    private function SocialLoginSeeder()
    {
        DB::table('social_logins')->truncate();
        $social_logins = [
           [
               'type' => 'Google',
               'client_id' => '',
               'client_secret' => '',
               'redirect_url' => '',
               'status' => 0,
           ],
           [
               'type' => 'Github',
               'client_id' => '',
               'client_secret' => '',
               'redirect_url' => '',
               'status' => 0,
           ],
           [
               'type' => 'Twitter',
               'client_id' => '',
               'client_secret' => '',
               'redirect_url' => '',
               'status' => 0,
           ],
           [
               'type' => 'Linkedin',
               'client_id' => '',
               'client_secret' => '',
               'redirect_url' => '',
               'status' => 0,
           ],
       ];
      foreach ($social_logins as $data) {
           SocialLogin::insertOrIgnore($data);
       }
   }

    }


class Demo_pageTableSeeder extends Seeder
{
    public function run()
    {
      DB::table('demo_pages')->truncate();
      Demo_page::create(['id' => 1, 'status' => 0]);
    }
}

class TemplateTypeTableSeeder extends Seeder
{
    public function run()
    {

        DB::table('template_types')->where('id',20)->delete();
        TemplateType::where('id',12)->update(['name' => 'auto_subscription_going_to_end']);
        TemplateType::create(['id' => 20, 'name' => 'cloud_created']);
    }
}

class TemplateTableSeeder extends Seeder
{
    public function run()
    {
      Template::where('id',2)->update(['name' => 'Verify your email address', 'type' => 1, 'url' => 'null', 'data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
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
      <td style="background: #fff; border-left: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
      <td style="background: #fff; border-top: 1px solid #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">
      <p>Dear {{name}},&nbsp;<br /><br />Before you can login, you must activate your account. Click <a href="{{url}}">{{url}}</a> to activate your account.<br /><br /><strong>Your Profile &amp; Control Panel Login</strong><br /><br />You can start exploring our feature-rich Control Panel, which will allow you to manage all your Products, buy new Products, check all your transactions and more.<br /><br /><strong>Login Details:</strong><br /><strong>URL: </strong><a href="{{website_url}}">{{website_url}}</a> <br /><strong>Username:</strong> {{username}}<br /><strong>Password:</strong> If you can not recall your current password, <a href="{{website_url}}/password/reset">click here</a> to request a new password to login.<br /><br />Thank You.</p>
      </td>
      <td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
      </tr>
      <tr>
      <td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
      <td style="background: #fff; padding: 0; width: 560px;" align="left">&nbsp;</td>
      <td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
      </tr>
      <tr>
      <td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
      <td style="background: #fff; padding: 20px 0 50px 0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{{url}}" target="_blank" rel="noopener">Verify Email </a></td>
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
    <td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
    </tr>
    <tr>
    {{contact}}
    </tr>
    </tbody>
    </table>
    </td>
    <td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
    </tr>
    </tbody>
    </table>
    <p>&nbsp;</p>']);
        
    Template::where('id',4)->update(['name' => 'Purchase confirmation', 'type' => 7, 'url' => 'null', 'data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
    <td style="width: 30px;">&nbsp;</td>
    <td style="width: 640px; padding-top: 30px;">
    <h2 style="color: #333; font-family: Arial,sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;">{{logo}}</h2>
    </td>
    <td style="width: 30px;">&nbsp;</td>
    </tr>
    <tr>
    <td style="width: 30px;">&nbsp;</td>
    <td style="width: 640px; padding-top: 30px;">
    <table style="width: 640px; border-bottom: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
    <td style="background: #fff; border-left: 1px solid                      #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px; border-radius: 5px                      0 0 0;">&nbsp;</td>
    <td style="background: #fff; border-top: 1px solid                      #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">Dear {{name}},<br /><br />
    <h1 style="color: #0088cc; font-family: Arial,sans-serif; font-size: 24px; font-weight: bold; padding: 0; margin: 0;">Thanks for your {{product}} order</h1>
    <br />Your order and payment details are below.</td>
    <td style="background: #fff; border-right: 1px solid                      #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px; border-radius: 0                      5px 0 0;">&nbsp;</td>
    </tr>
    <tr>
    <td style="background: #fff; border-left: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
    <td style="background: #fff; padding: 0; width: 560px;" align="left">
    <table style="margin: 25px 0 30px                        0; width: 560px; border: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
    <thead>
    <tr style="background-color: #f8f8f8;">
    <th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Order No</th>
    <th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Product</th>
    <th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Expiry</th>
    <th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">{{orderHeading}}</th>
    </tr>
    </thead>
    <tbody>
    <tr>
    <td style="border-bottom: 1px solid#ccc; color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; padding: 15px                              8px;" valign="top">{{number}}</td>
    <td style="border-bottom: 1px solid#ccc; color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; padding: 15px                              8px;" valign="top">{{product}}</td>
    <td style="border-bottom: 1px solid#ccc; color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; padding: 15px                              8px;" valign="top">{{expiry}}</td>
    <td style="border-bottom: 1px solid#ccc; color: #333; font-family: Arial,sans-serif; font-size: 14px; line-height: 20px; padding: 15px                              8px;" valign="top"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{{downloadurl}}" target="_blank" rel="noopener">{{orderHeading}}</a></td>
    </tr>
    </tbody>
    </table>
    <p><a class="moz-txt-link-abbreviated" href="{{erialkeyurl}}" target="_blank" rel="noopener"> Click Here</a> to get your License Code.</p>
    <p><a class="moz-txt-link-abbreviated" href="{{knowledge_base}}/category-list/installation-and-upgrade-guide"> Refer To Our Knowledge Base</a> for further installation assistance</p>
    <p>Click below to login to your Control Panel to view the invoice or to pay for any pending invoice.</p>
    </td>
    <td style="background: #fff; border-right: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
    </tr>
    <tr>
    <td style="background: #fff; border-left: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
    <td style="background: #fff; padding: 20px 0 50px                      0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{{invoiceurl}}" target="_blank" rel="noopener"> View Invoice </a></td>
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
    <td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
    </tr>
    <tr>
   {{contact}}
    </tr>
    </tbody>
    </table>
    </td>
    <td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
    </tr>
    </tbody>
    </table>
    <p>&nbsp;</p>']);
        
        
    Template::where('id',5)->update(['name' => 'Reset your password', 'type' => 2, 'url' => 'null', 'data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
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
    <td style="background: #fff; border-top: 1px solid #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">
    <p>Dear {{name}},<br /><br />A request to reset password was received from your account. Use the link below to reset your password.<br /><br /><strong>Link:</strong>&nbsp; <a href="{{url}}">{{url}}</a><br /><br />Thank You.<br /><br /><strong>PS:</strong> If you have not initiated this request, <a href="{{contact-us}}/contact-us/">report it to us immediately</a>.<br /><br /><em>This is an automated email, please do not reply.</em></p>
    </td>
    <td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
    </tr>
    <tr>
    <td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
    <td style="background: #fff; padding: 20px 0 50px 0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{{url}}" target="_blank" rel="noopener">Reset Password </a></td>
    <td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
    </tr>
    </tbody>
    </table>
    <td style="width: 30px;">&nbsp;</td>
  </tr>
  <tr>
  <td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
  <td style="padding: 20px 0 10px 0; width: 640px;" align="left">
  <table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tbody>
  <tr>
  <td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
  </tr>
  <tr>
  {{contact}}
  </tr>
  </tbody>
  </table>
  </td>
  <td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
  </tr>
  </tbody>
  </table>
  <p>&nbsp;</p>']);

  Template::where('id',6)->update(['name' => 'Consolidated renewal reminder', 'type' => 4, 'url' => 'null', 'data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
  <tbody>
  <tr>
  <td style="width: 28px;">&nbsp;</td>
  <td style="width: 640px; padding-top: 30px;">
  <h2 style="color: #333; font-family: Arial, sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;">{{logo}}</h2>
  </td>
  <td style="width: 28px;">&nbsp;</td>
  </tr>
  <tr>
  <td style="width: 28px;">&nbsp;</td>
  <td style="width: 640px; padding-top: 30px;">
  <table style="width: 640px; border-bottom: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
  <tbody>
  <tr>
  <td style="background: #fff; border-left: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
  <td style="background: #fff; border-top: 1px solid #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">Dear {{name}},<br /><br />
  <h1 style="color: #0088cc; font-family: Arial, sans-serif; font-size: 24px; font-weight: bold; padding: 0; margin: 0;">Your order is expiring soon.<br />Renew now.</h1>
  </td>
  <td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
  </tr>
  <tr>
  <td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
  <td style="background: #fff; padding: 0; width: 560px;" align="left">
  <p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Your order is expiring soon. Please renew before the order is deleted to avoid losing your data.</p>
  <table style="margin: 25px 0 30px 0; width: 560px; border: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
  <thead>
  <tr style="background-color: #f8f8f8;">
  <th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Order No</th>
  <th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Product</th>
  <th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Expiry Date</th>
  <th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">{{product_type}}</th>
  </tr>
  </thead>
  <tbody>
  <tr>
  <td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{number}}</td>
  <td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{product}}</td>
  <td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{expiry}} <br /><span style="color: #ad7b33; font-family: Arial, sans-serif; font-size: 12px;">Expiring Soon!</span></td>
  <td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{deletionDate}}</td>
  </tr>
  </tbody>
  </table>
  <p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Click the below button to login to your Control Panel and renew your order.</p>
  </td>
  <td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
  </tr>
  <tr>
  <td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
  <td style="background: #fff; padding: 20px 0 50px 0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{{url}}" target="_blank" rel="noopener"> Renew Order </a></td>
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
<td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
{{contact}}
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>']); 
        
       
            
Template::where('id',7)->update(['name' => 'URGENT: Order has expired', 'type' => 5, 'url' => 'null', 'data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
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
<h1 style="color: #0088cc; font-family: Arial, sans-serif; font-size: 24px; font-weight: bold; padding: 0; margin: 0;">Your order has expired.<br />Renew them now.</h1>
</td>
<td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 0; width: 560px;" align="left">
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Your order is expired. Please renew them before they are deleted to avoid loss of data.</p>
<table style="margin: 25px 0 30px 0; width: 560px; border: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<thead>
<tr style="background-color: #f8f8f8;">
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Order ID</th>
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Product</th>
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Expiry Date</th>
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">{{product_type}}</th>
</tr>
</thead>
<tbody>
<tr>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{number}}</td>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{product}}</td>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{expiry}}</td>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{deletionDate}}</td>
</tr>
</tbody>
</table>
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Click the button below to login to your Control Panel and renew your order.</p>
</td>
<td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 20px 0 50px 0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{{url}}" target="_blank" rel="noopener"> Renew Order </a></td>
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
<td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
{{contact}}
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>']); 

Template::where('id',8)->update(['name' => 'Invoice', 'type' => 6, 'url' => 'null', 'data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<h2 style="color: #333; font-family: Arial,sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;">{{logo}}</h2>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<table style="width: 640px; border-bottom: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="background: #fff; border-left: 1px solid                      #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px; border-radius: 5px                      0 0 0;">&nbsp;</td>
<td style="background: #fff; border-top: 1px solid                      #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">Dear {{name}},<br /><br />
<h1 style="color: #0088cc; font-family: Arial,sans-serif; font-size: 24px; font-weight: bold; padding: 0; margin: 0;">Thanks for your order</h1>
<br />Your order and payment details are below.</td>
<td style="background: #fff; border-right: 1px solid                      #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px; border-radius: 0                      5px 0 0;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 0; width: 560px;" align="left">
<table style="margin: 25px 0px 30px; width: 560px; border: 1px solid #cccccc; height: 38.5px;" border="0" cellspacing="0" cellpadding="0">
<thead>
<tr style="background-color: #f8f8f8; height: 20px;">
<th style="color: #333333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px; height: 20px;" align="left" valign="top">Invoice No</th>
<th style="color: #333333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px; height: 20px;" align="left" valign="top">Product</th>
<th style="color: #333333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px; height: 20px;" align="left" valign="top">Total</th>
</tr>
</thead>
<tbody>
<tr style="height: 18.5px;">
<td style="height: 18.5px;">&nbsp;{{content}}</td>
</tr>
</tbody>
</table>
<p>Click below to login to your Control Panel to view the invoice or to pay for any pending invoice.</p>
</td>
<td style="background: #fff; border-right: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 20px 0 50px                      0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid                        #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{{invoiceurl}}" target="_blank" rel="noopener"> Invoice </a></td>
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
<td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
{{contact}}
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>']); 

Template::where('id',9)->update(['name' => 'Your New Sales Manager', 'type' => 9, 'url' => 'null', 'data' => '<p>Dear {{name}},</p>
<p>This is {{manager_first_name}} {{manager_last_name}}.</p>
<p>From now onwards I will be your one point of contact. I will follow up with you as well as with our team. Please feel free to get in touch with me anytime if you have any issues with regard to your account. You can also add me on Skype. My ID is mentioned in my signature. It is a pleasure to have you on board and I look forward to effective conversations with you in future.</p>
<p>Hope you have a great day.</p>
<p>Regards,</p>
<p>{{manager_first_name}} {{manager_last_name}}</p>
<p>Sales Manager,<br />Mobile: {{manager_code}} {{manager_mobile}}<br />Skype ID: {{manager_skype}}<br />Email: {{manager_email}}</p>']);        
            
Template::where('id',10)->update(['name' => 'Your New Sales Manager', 'type' => 10, 'url' => 'null', 'data' => '<p>Dear {{name}},</p>
<p>This is {{manager_first_name}} {{manager_last_name}}.</p>
<p>From now onwards I will be your one point of contact. I will follow up with you as well as with our team. Please feel free to get in touch with me anytime if you have any issues with regard to your account. You can also add me on Skype. My ID is mentioned in my signature. It is a pleasure to have you on board and I look forward to effective conversations with you in future.</p>
<p>Hope you have a great day.</p>
<p>Regards,</p>
<p>{{manager_first_name}} {{manager_last_name}}</p>
<p>Account Manager,<br />Mobile: {{manager_code}} {{manager_mobile}}<br />Skype ID: {{manager_skype}}<br />Email: {{manager_email}}</p>']);                
             
          
Template::where('id',12)->update(['name' => 'Consolidated renewal reminder', 'type' => 12, 'url' => 'null', 'data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 5px;">
<h2 style="color: #333; font-family: Arial, sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;">{{logo}}</h2>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 5px;">
<table style="width: 640px; border-bottom: 1px solid #cccccc; height: 437.5px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr style="height: 20px;">
<td style="background: #ffffff; border-left: 1px solid #cccccc; border-top: 1px solid #cccccc; width: 40px; padding-top: 10px; padding-bottom: 0px; height: 22px;">&nbsp;</td>
<td style="background: #ffffff; border-top: 1px solid #cccccc; padding: 40px 0px 0px; width: 560px; height: 22px;" align="left">Dear {{name}},<br /><br />
<h1 style="color: #0088cc; font-family: Arial, sans-serif; font-size: 24px; font-weight: bold; padding-bottom: 20px; margin: 0;">Your orders are expiring soon.</h1>
</td>
<td style="background: #ffffff; border-right: 1px solid #cccccc; border-top: 1px solid #cccccc; width: 40px; padding-top: 10px; padding-bottom: 0px; height: 22px;">&nbsp;</td>
</tr>
<tr style="height: 247px;">
<td style="background: #ffffff; border-left: 1px solid #cccccc; width: 40px; padding-top: 3px; padding-bottom: 3px; height: 247px;">&nbsp;</td>
<td style="background: #ffffff; padding: 0px; width: 560px; height: 247px;" align="left">
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">You are using automatic payments. With this, you do not have to send transfers manually, but instead, we charge your account when a certain amount is due.</p>
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">With this e-mail, we would like to inform you that we will perform an automatic payment on  <strong>{{expiry}}.</strong></p>
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">The amount will be <strong>{{renewPrice}} </strong> most likely, but this is only the predicted amount. It may change based on further taxes.</p>
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Please ensure that there is at least one valid funding source configured in your account. Funding sources can be credit cards or your bank account.</p>
<table style="margin: 25px 0 30px 0; width: 560px; border: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<thead>
<tr style="background-color: #f8f8f8;">
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Order No</th>
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Product</th>
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Expiry Date</th>
<th style="color: #333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">{{product_type}}</th>
</tr>
</thead>
<tbody>
<tr>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{number}}</td>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{product}}</td>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{expiry}} <br /><span style="color: #ad7b33; font-family: Arial, sans-serif; font-size: 12px;">Expiring Soon!</span></td>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{deletionDate}}</td>
</tr>
</tbody>
</table>
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">&nbsp;</p>
</td>
<td style="background: #ffffff; border-right: 1px solid #cccccc; width: 40px; padding-top: 10px; padding-bottom: 10px; height: 247px;">&nbsp;</td>
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
<td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
{{contact}}
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>']); 
                
Template::where('id',13)->update(['name' => 'Auto payment successfull', 'type' => 13, 'url' => 'null', 'data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<h2 style="color: #333; font-family: Arial,sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;">{{logo}}</h2>
</td>
<td style="width: 30px;">&nbsp;</td>
</tr>
<tr>
<td style="width: 30px;">&nbsp;</td>
<td style="width: 640px; padding-top: 30px;">
<table style="width: 640px; border-bottom: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="background: #fff; border-left: 1px solid                      #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px; border-radius: 5px                      0 0 0;">&nbsp;</td>
<td style="background: #fff; border-top: 1px solid                      #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">Dear {{name}},<br /><br />
<h1 style="color: #0088cc; font-family: Arial,sans-serif; font-size: 24px; font-weight: bold; padding: 0; margin: 0;">Your subscription was successfully renewed.</h1>
<p>We have successfully received a payment of <strong>{{total}} </strong> for the renewal of {{product}}. The product is now valid till <strong>{{future_expiry}}.</strong></p>
<p>The authorization for this transaction is based on the Auto-renewal billing setup in your {{product}} account.</p>
<p>For more details of your transaction login to your control panel.</p>
</td>
<td style="background: #fff; border-right: 1px solid                      #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px; border-radius: 0                      5px 0 0;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid                      #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 0; width: 560px;" align="left">
<table style="margin: 25px 0px 30px; width: 560px; border: 1px solid #cccccc; height: 38.5px;" border="0" cellspacing="0" cellpadding="0">
<thead>
<tr style="background-color: #f8f8f8; height: 20px;">
<th style="color: #333333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px; height: 20px;" align="left" valign="top">Product</th>
<th style="color: #333333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px; height: 20px;" align="left" valign="top">Total</th>
<th style="color: #333333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px; height: 20px;" align="left" valign="top">Order No</th>
</tr>
</thead>
<tbody>
<tr style="height: 18.5px;">
<td style="border-bottom: 1px; color: #333333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px; width: 152.672px;" valign="top">{{product}}</td>
<td style="border-bottom: 1px; color: #333333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px; width: 100.773px;" valign="top">{{total}}</td>
<td style="border-bottom: 1px; color: #333333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px; width: 98.5391px;">{{number}}</td>
</tr>
</tbody>
</table>
</td>
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
<td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
{{contact}}
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>']); 
                
Template::where('id',14)->update(['name' => 'Auto payment failed', 'type' => 14, 'url' => 'null', 'data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
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
<h1 style="color: #0088cc; font-family: Arial, sans-serif; font-size: 24px; font-weight: bold; padding: 0; margin: 0;">Oh no, your payment failed.</h1>
</td>
<td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 0; width: 560px;" align="left">
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Your Payment failed for your subscription. Dont worry, Well try again over the next few days.</p>
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">To keep your product active you may need to update your payment, Please use the below link to renew your order.&nbsp;&nbsp;</p>
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;"><strong>Reason for Failure:</strong></p>
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">{{exception}}.</p>
<table style="margin: 25px 0 30px 0; width: 560px; border: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
<thead>
<tr style="background-color: #f8f8f8;">
<th style="color: #333333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px; width: 139.516px;" align="left" valign="top">Order No</th>
<th style="color: #333333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px; width: 138.031px;" align="left" valign="top">Product</th>
<th style="color: #333333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px; width: 83.6016px;" align="left" valign="top">Expiry Date</th>
<th style="color: #333333; font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px; width: 35.8281px;">Total</th>
</tr>
</thead>
<tbody>
<tr>
<td style="border-bottom: 1px; color: #333333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px; width: 139.516px;" valign="top">{{number}}</td>
<td style="border-bottom: 1px; color: #333333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px; width: 138.031px;" valign="top">{{product}}</td>
<td style="border-bottom: 1px; color: #333333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px; width: 83.6016px;" valign="top">{{expiry}}</td>
<td style="border-bottom: 1px; color: #333333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px; width: 35.8281px;">{{total}}</td>
</tr>
</tbody>
</table>
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Click the button below to login to your Control Panel and renew your orders.</p>
</td>
<td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 20px 0 50px 0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{{url}}" target="_blank" rel="noopener"> Make Payment </a></td>
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
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr>
{{contact}}
</tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>']); 


Template::where('id',15)->delete();
Template::where('id',16)->delete();
Template::where('id',17)->delete();
Template::where('id',18)->delete();
                
                
Template::where('id',19)->update(['name' => 'URGENT: Order has been deleted', 'type' => 19, 'url' => 'null', 'data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
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
<h1 style="color: #0088cc; font-family: Arial, sans-serif; font-size: 24px; font-weight: bold; padding: 0; margin: 0;">Your Faveo Cloud has been Deleted</h1>
</td>
<td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
<tr>
<td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
<td style="background: #fff; padding: 0; width: 560px;" align="left">
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">We are reaching out to inform you that your Faveo Cloud order has expired, and as a result, we have proceeded with the deletion of your order and the associated details from our system. Unfortunately, this means you can no longer use the product.</p>
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Thank you For Choosing us!</p>
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
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{number}}</td>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{product}}</td>
<td style="border-bottom: 1px; color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; padding: 15px 8px;" valign="top">{{expiry}}</td>
</tr>
</tbody>
</table>
<p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">&nbsp;</p>
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
<td style="padding: 20px 0 10px 0; width: 640px;" align="left">{{contact}}
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="color: #333; font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
</tr>
<tr></tr>
</tbody>
</table>
</td>
<td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>']); 
DB::table('templates')->where('id',20)->delete();
Template::create(['id' => 20, 'name' => 'New instance created', 'type' => 20, 'url' => 'null', 'data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
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
          <td style="background: #fff; border-left: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
          <td style="background: #fff; border-top: 1px solid #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">
          <p>Dear {{name}},&nbsp;<br /><br /></p>
          <p><strong>Welcome to {{title}}!</strong> We are delighted to have you on board. You are joining thousands of businesses that use {{title}} to engage with their customers meaningfully.</p>
          <p>{{message}}</p>
          <p>We are delighted to offer you a trial period to explore our product.</p>
          <p>Please write to our team of product specialists at {{company_email}} for any questions or assistance during your trial period.</p>
          <p>Thank you for choosing us!</p>
          <p>Regards,<br />Team {{title}}</p>
          </td>
          <td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
          </tr>
          <tr>
          <td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
          <td style="background: #fff; padding: 0; width: 560px;" align="left">&nbsp;</td>
          <td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
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
        <td style="color: #333; font-family: Arial,sans-serif; font-size: 12px; font-weight: bold; padding-bottom: 0; padding-left: 25px;">BILLING CONTACT</td>
        </tr>
        <tr>
        {{contact}}
        </tr>
        </tbody>
        </table>
        </td>
        <td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
        </tr>
        </tbody>
        </table>
        <p>&nbsp;</p>']);

    }



}
class PricingTemplateSeeder extends Seeder
{
    public function run()
    {
        PricingTemplate::where('id',1)->update(['data' => '<div class="col-md-3 col-sm-6">
                            <div class="plan">
                                <div class="plan-header">
                                    <h3>{{name}}</h3>
                                </div>
                                <div class="plan-price">
                       <div class="content-switcher-wrapper">
                                                <div class="content-switcher left-50pct transform3dx-n50 active" data-content-switcher-id="pricingTable1" data-content-switcher-rel="1">
                                                
                    <span class="price">{{price-year}}</span>

                     <span class="strike">{{strike-priceyear}}</span>
                                                                                <label class="price-label">{{price-description}}</label><br><br>
                     <div class="subscription table-responsive">{{subscription}}</div><br>

                    <div>{{url}} </div>                                                 
                                                                    </div>
                                                                    <div class="content-switcher left-50pct transform3dx-n50" data-content-switcher-id="pricingTable1" data-content-switcher-rel="2">
                                                                        
                    <span class="price">{{price}}</span>
                     <span class="strike">{{strike-price}}</span>
                                                                                        <label class="price-label">{{pricemonth-description}}</label><br> <br>                                                                                        
                                                                            
                     <div class="subscription table-responsive">{{subscription}}</div><br>

                    <div>{{url}} </div>                                                 
                                                                    </div>
                                                                </div>
                    <br>


                                                    </div>

                      
                                                    <div class="plan-features">
                                                        <ul>
                                                        <li>{{feature}}</li>
                                                    </ul>
                                                         
                                                    </div>
                                                    <div class="plan-footer">
                                          
                                                  
                                                    </div>
                                                    
                            </div>
                        </div>']);
         DB::table('pricing_templates')->where('id',2)->delete();

        PricingTemplate::create(['id' => 2, 'data' => '<div class="col-md-3 col-sm-6">
                 <div class="plan plan-featured transform-none">
                 <div class="plan-header bg-primary">
                 <h3>{{name}}</h3>
                 </div>
                 <div class="plan-price">
                 <div class="content-switcher-wrapper">
                                                                <div class="content-switcher left-50pct transform3dx-n50 active" data-content-switcher-id="pricingTable1" data-content-switcher-rel="1">
                                                                    
                                                                        <span class="price">{{price-year}}</span>
                 <span class="strike">{{strike-priceyear}}</span>
                                                                                    <label class="price-label">{{price-description}}</label><br><br>
                 <div class="subscription">{{subscription}}</div>
                <br>
                <div>{{url}} </div>                                                 </div>
                                                                
                                                                <div class="content-switcher left-50pct transform3dx-n50" data-content-switcher-id="pricingTable1" data-content-switcher-rel="2">
                                                                    
                                                                        <span class="price">{{price}}</span>

                 <span class="strike">{{strike-price}}</span>                                                                   <label class="price-label">{{pricemonth-description}}</label><br><br>
                 <div class="subscription">{{subscription}}</div>
                <br>
                <div>{{url}} </div>                                                 
                                                                </div>
                                                            </div><br>

                 </div>


                 <div class="plan-features">
                 <ul>
                 <li>{{feature}}</li>
                 </ul>
                 
                 </div>
                 <div class="plan-footer">

                 
                 </div>
                 
                 </div>
                 </div>']);
    }
}

