<?php

namespace Database\Seeders\v4_0_2_1;
use Illuminate\Database\Seeder;
use App\ReportColumn;
use App\ReportSetting;
use App\Model\Common\Template;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ReportColumn::truncate();
        ReportSetting::truncate();

        $this->call([ReportColumnSeeder::class]);
        $this->command->info('Report column Table Seeded!');

        $this->call([ReportSettingSeeder::class]);
        $this->command->info('Report column Table Seeded!');

        $this->call([
            TemplateTableSeeder::class,
        ]);

         \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }

}

class ReportColumnSeeder extends Seeder
{
    public function run()
    {

        ReportColumn::create([
            'id' => '1',
            'key' => 'checkbox',
            'label' => 'checkbox',
            'type' => 'users',
            'default' => '1'
        ]);
        ReportColumn::create([
            'id' => '2',
            'key' => 'name',
            'label' => 'name',
            'type' => 'users',
            'default' => '1'
        ]);
        ReportColumn::create([
            'id' => '3',
            'key' => 'email',
            'label' => 'email',
            'type' => 'users',
            'default' => '1'
        ]);
        ReportColumn::create([
            'id' => '4',
            'key' => 'mobile',
            'label' => 'mobile',
            'type' => 'users',
            'default' => '1'
        ]);
        ReportColumn::create([
            'id' => '5',
            'key' => 'country',
            'label' => 'country',
            'type' => 'users',
            'default' => '1'
        ]);
         ReportColumn::create([
            'id' => '6',
            'key' => 'created_at',
            'label' => 'created_at',
            'type' => 'users',
            'default' => '1'
        ]);
          ReportColumn::create([
            'id' => '7',
            'key' => 'active',
            'label' => 'active',
            'type' => 'users',
            'default' => '1'
        ]);
           ReportColumn::create([
            'id' => '8',
            'key' => 'action',
            'label' => 'action',
            'type' => 'users',
            'default' => '1'
        ]);

        ReportColumn::create([
            'id' => '9',
            'key' => 'checkbox',
            'label' => 'checkbox',
            'type' => 'invoices',
            'default' => '1'
        ]);
        ReportColumn::create([
            'id' => '10',
            'key' => 'user_id',
            'label' => 'user_id',
            'type' => 'invoices',
            'default' => '1'
        ]);
        ReportColumn::create([
            'id' => '11',
            'key' => 'email',
            'label' => 'email',
            'type' => 'invoices',
            'default' => '0'
        ]);
        ReportColumn::create([
            'id' => '12',
            'key' => 'mobile',
            'label' => 'mobile',
            'type' => 'invoices',
            'default' => '0'
        ]);
        ReportColumn::create([
            'id' => '13',
            'key' => 'country',
            'label' => 'country',
            'type' => 'invoices',
            'default' => '0'
        ]);
         ReportColumn::create([
            'id' => '14',
            'key' => 'number',
            'label' => 'number',
            'type' => 'invoices',
            'default' => '1'
        ]);
          ReportColumn::create([
            'id' => '15',
            'key' => 'product',
            'label' => 'product',
            'type' => 'invoices',
            'default' => '0'
        ]);
          ReportColumn::create([
            'id' => '16',
            'key' => 'date',
            'label' => 'date',
            'type' => 'invoices',
            'default' => '1'
        ]);
          ReportColumn::create([
            'id' => '17',
            'key' => 'grand_total',
            'label' => 'grand_total',
            'type' => 'invoices',
            'default' => '1'
        ]);
          ReportColumn::create([
            'id' => '18',
            'key' => 'status',
            'label' => 'status',
            'type' => 'invoices',
            'default' => '1'
        ]);
         ReportColumn::create([
            'id' => '19',
            'key' => 'action',
            'label' => 'action',
            'type' => 'invoices',
            'default' => '1'
        ]);

        ReportColumn::create([
            'id' => '20',
            'key' => 'checkbox',
            'label' => 'checkbox',
            'type' => 'orders',
            'default' => '1'
        ]);
        ReportColumn::create([
            'id' => '21',
            'key' => 'client',
            'label' => 'client',
            'type' => 'orders',
            'default' => '1'
        ]);
        ReportColumn::create([
            'id' => '22',
            'key' => 'email',
            'label' => 'email',
            'type' => 'orders',
            'default' => '0'
        ]);
        ReportColumn::create([
            'id' => '23',
            'key' => 'mobile',
            'label' => 'mobile',
            'type' => 'orders',
            'default' => '0'
        ]);
        ReportColumn::create([
            'id' => '24',
            'key' => 'country',
            'label' => 'country',
            'type' => 'orders',
            'default' => '0'
        ]);
         ReportColumn::create([
            'id' => '25',
            'key' => 'number',
            'label' => 'number',
            'type' => 'orders',
            'default' => '1'
        ]);
          ReportColumn::create([
            'id' => '26',
            'key' => 'status',
            'label' => 'status',
            'type' => 'orders',
            'default' => '0'
        ]);
          ReportColumn::create([
            'id' => '27',
            'key' => 'product_name',
            'label' => 'product_name',
            'type' => 'orders',
            'default' => '1'
        ]);
          ReportColumn::create([
            'id' => '28',
            'key' => 'plan_name',
            'label' => 'plan_name',
            'type' => 'orders',
            'default' => '0'
        ]);
          ReportColumn::create([
            'id' => '29',
            'key' => 'version',
            'label' => 'version',
            'type' => 'orders',
            'default' => '1'
        ]);
        ReportColumn::create([
            'id' => '30',
            'key' => 'agents',
            'label' => 'agents',
            'type' => 'orders',
            'default' => '1'
        ]);
         ReportColumn::create([
            'id' => '31',
            'key' => 'order_status',
            'label' => 'order_status',
            'type' => 'orders',
            'default' => '1'
        ]);
         ReportColumn::create([
            'id' => '32',
            'key' => 'order_date',
            'label' => 'order_date',
            'type' => 'orders',
            'default' => '1'
        ]);
         ReportColumn::create([
            'id' => '33',
            'key' => 'update_ends_at',
            'label' => 'update_ends_at',
            'type' => 'orders',
            'default' => '1'
        ]);
         ReportColumn::create([
            'id' => '34',
            'key' => 'action',
            'label' => 'action',
            'type' => 'orders',
            'default' => '1'
        ]);


        ReportColumn::create([
            'id' => '35',
            'key' => 'checkbox',
            'label' => 'checkbox',
            'type' => 'tenats',
            'default' => '1'
        ]);
        ReportColumn::create([
            'id' => '36',
            'key' => 'Order',
            'label' => 'Order',
            'type' => 'tenats',
            'default' => '1'
        ]);
        ReportColumn::create([
            'id' => '37',
            'key' => 'name',
            'label' => 'name',
            'type' => 'tenats',
            'default' => '0'
        ]);
        ReportColumn::create([
            'id' => '38',
            'key' => 'email',
            'label' => 'email',
            'type' => 'tenats',
            'default' => '0'
        ]);
        ReportColumn::create([
            'id' => '39',
            'key' => 'mobile',
            'label' => 'mobile',
            'type' => 'tenats',
            'default' => '0'
        ]);
        ReportColumn::create([
            'id' => '40',
            'key' => 'country',
            'label' => 'country',
            'type' => 'tenats',
            'default' => '0'
        ]);
         ReportColumn::create([
            'id' => '41',
            'key' => 'Expiry day',
            'label' => 'Expiry day',
            'type' => 'tenats',
            'default' => '0'
        ]);
         ReportColumn::create([
            'id' => '42',
            'key' => 'Deletion day',
            'label' => 'Deletion day',
            'type' => 'tenats',
            'default' => '1'
        ]);
         ReportColumn::create([
            'id' => '43',
            'key' => 'plan',
            'label' => 'plan',
            'type' => 'tenats',
            'default' => '0'
        ]);
          ReportColumn::create([
            'id' => '44',
            'key' => 'tenants',
            'label' => 'tenants',
            'type' => 'tenats',
            'default' => '1'
        ]);
         ReportColumn::create([
            'id' => '45',
            'key' => 'domain',
            'label' => 'domain',
            'type' => 'tenats',
            'default' => '1'
        ]);
           ReportColumn::create([
            'id' => '46',
            'key' => 'db_name',
            'label' => 'db_name',
            'type' => 'tenats',
            'default' => '1'
        ]);
        ReportColumn::create([
            'id' => '47',
            'key' => 'db_username',
            'label' => 'db_username',
            'type' => 'tenats',
            'default' => '1'
        ]);
         ReportColumn::create([
            'id' => '48',
            'key' => 'action',
            'label' => 'action',
            'type' => 'tenats',
            'default' => '1'
        ]);
    }
}
class ReportSettingSeeder extends Seeder
{
    public function run()
    {
        ReportSetting::create([
            'id' => '1',
            'records' => '3000'
        ]);
    }
}





class TemplateTableSeeder extends Seeder
{
    public function run()
    {
    
    Template::where('id',23)->update(['data' => '<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
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
   <h1 style="color: #0088cc; font-family: Arial, sans-serif; font-size: 24px; font-weight: bold; padding: 0; margin: 0;">Renew your subscription to continue using our services.</h1>
  
   </td>
   <td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
   </tr>
   <tr>
   <td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
   <td style="background: #fff; padding: 0; width: 560px;" align="left">
   <p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Your subscription with {{company_title}} is set to expire on {{date}}.</p>
   <p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">To ensure uninterrupted service, please complete your payment now. With Auto-renewal enabled, all future payments will be automatic and hassle-free.&nbsp;</p>
   <p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Thank you for choosing Ladybird Web Solutions!&nbsp;</p>
   <table style="margin: 25px 0 30px 0; width: 560px; border: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
      <thead>
      <tr style="background-color: #f8f8f8;">
      <th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Product</th>
      <th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Order No</th>
      <th style="color: #333; font-family: Arial,sans-serif; font-size: 14px; font-weight: bold; line-height: 20px; padding: 15px 8px;" align="left" valign="top">Total</th>

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
   <p style="color: #333; font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; text-align: left;">Click the button below to make a payment.</p>
   </td>
   <td style="background: #fff; border-right: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
   </tr>
   <tr>
   <td style="background: #fff; border-left: 1px solid #ccc; width: 40px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
   <td style="background: #fff; padding: 20px 0 50px 0; width: 560px;" align="left"><a style="background: #00aeef; border: 1px solid #0088CC; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: bold; color: #fff; outline: none; text-shadow: none; text-decoration: none; font-family: Arial,sans-serif;" href="{{url}}" target="_blank" rel="noopener"> Make Payment </a><br><br>
      <p style="font-family:sans-serif;font-weight:normal;padding:0;margin:0;font-size:14px;line-height:19px;margin-bottom:10px;color: grey">
         This link will expire on {{expiry_date}}
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
   <p>&nbsp;</p>']);
 }
}



