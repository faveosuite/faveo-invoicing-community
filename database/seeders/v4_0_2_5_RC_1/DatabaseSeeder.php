<?php

namespace Database\Seeders\v4_0_2_5_RC_1;


use App\Model\Common\Country;
use App\Model\Common\State;
use App\Model\Payment\Currency;
use App\Plugins\Recaptcha\Model\RecaptchaSetting;
use DB;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createRecaptcha();
        $this->countrySeeder();
        $this->addMailTemplateForEmailAndMobileChange();

    }

    public function createRecaptcha()
    {
        RecaptchaSetting::firstOrCreate([]);
    }

    public function countrySeeder(): void
    {
        $currencies = collect(require database_path('seeders/v4_0_2_5_RC_1/currencies.php'));
        $countries = collect(require database_path('seeders/v4_0_2_5_RC_1/countries.php'));
        $states = collect(require database_path('seeders/v4_0_2_5_RC_1/states.php'));

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate tables
        State::truncate();
        Country::truncate();
        Currency::truncate();

        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Chunked bulk inserts for currencies
        $currencies->chunk(500)->each(function($chunk){
            DB::table('currencies')->insert(
                $chunk->map(function($c){
                    return [
                        'id' => $c['id'],
                        'code' => $c['code'],
                        'name' => $c['name'],
                        'symbol' => $c['symbol'],
                        'dashboard_currency' => $c['code'] === 'USD' ? 1 : 0,
                        'status' => $c['code'] === 'USD' ? 1 : 0,
                    ];
                })->toArray()
            );
        });

        // Chunked bulk inserts for countries
        $countries->chunk(500)->each(function($chunk){
            DB::table('countries')->insert(
                $chunk->map(function($c){
                    return [
                        'country_id' => $c['country_id'],
                        'country_code_char2' => $c['country_code_char2'],
                        'country_code_char3' => $c['country_code_char3'],
                        'country_name' => $c['country_name'],
                        'numcode' => $c['numcode'],
                        'phonecode' => $c['phonecode'],
                        'capital' => $c['capital'],
                        'latitude' => $c['latitude'],
                        'longitude' => $c['longitude'],
                        'emoji' => $c['emoji'],
                        'emojiU' => $c['emojiU'],
                        'currency_id' => $c['currency_id'],
                        'status' => $c['country_code_char2'] === 'AQ' ? 0 : 1,
                    ];
                })->toArray()
            );
        });

        // Chunked bulk inserts for states
        $states->chunk(500)->each(function($chunk){
            DB::table('states_subdivisions')->insert(
                $chunk->map(function($s){
                    return [
                        'state_subdivision_id' => $s['state_subdivision_id'],
                        'state_subdivision_name' => $s['state_subdivision_name'],
                        'country_code' => $s['country_code'],
                        'iso2' => $s['iso2'],
                        'primary_level_name' => $s['primary_level_name'],
                        'latitude' => $s['latitude'],
                        'longitude' => $s['longitude'],
                        'country_id' => $s['country_id'],
                    ];
                })->toArray()
            );
        });

        // Update users in a single query
        DB::table('users')
            ->whereNotNull('state')
            ->update([
                'state' => DB::raw("SUBSTRING_INDEX(state, '-', -1)")
            ]);

        DB::table('tax_by_states')
            ->whereNotNull('state_code')
            ->update([
                'state_code' => DB::raw("SUBSTRING_INDEX(state_code, '-', -1)")
            ]);
    }

    public function addMailTemplateForEmailAndMobileChange()
    {
        TemplateType::updateOrCreate(
            ['id' => '25'],
            ['name' => 'verify_new_email']
        );

        Template::updateOrCreate(
            ['id' => '25'],
            [
                'name' => 'Verify your new email address',
                'type' => 25,
                'url' => 'null',
                'data' => '
<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
        <td style="width: 30px;">&nbsp;</td>
        <td style="width: 640px; padding-top: 30px;">
            <h2 style="color: #333; font-family: Arial, sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;">
                {{logo}}
            </h2>
        </td>
        <td style="width: 30px;">&nbsp;</td>
    </tr>
    <tr>
        <td style="width: 30px;">&nbsp;</td>
        <td style="width: 640px; padding-top: 30px;">
            <table style="width: 640px; border-bottom: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr>
                    <td style="background: #fff; border-left: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px;">&nbsp;</td>
                    <td style="background: #fff; border-top: 1px solid #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">
                        <p style="font-family: Arial, sans-serif; font-size: 14px; color: #333;">
                            Hi {{name}}, <br/><br/>
                            You have requested to verify your new email address. Please use the verification code below to complete the verification:
                        </p>
                        <div style="background:#f5f4f5; border-radius:4px; padding:20px; margin:20px 50px;">
                            <div style="text-align:center; vertical-align:middle; font-size:30px; font-weight: bold; color:#333;">
                                {{otp}}
                            </div>
                        </div>
                        <p><strong>If someone asks for this code</strong></p>
                        <p style="font-family: Arial, sans-serif; font-size: 14px; color: #333;">
                            Don\'t share this code with anyone, especially if they tell you that they work for Faveo Invoicing Community. They may be trying to hack your account.
                        </p>
                        <p><strong>Didn\'t request this?</strong></p>
                        <p style="font-family: Arial, sans-serif; font-size: 14px; color: #333;">
                        If you didn’t request this email verification, someone else may be trying to access your account. Don’t share this code. If you need help, please reach out to our <a href="{{contact_url}}" style="text-decoration: none;">support team.</a>  </p>
                        <p style="font-family: Arial, sans-serif; font-size: 14px; color: #333; margin-top: 20px;">
                            Thank you, <br/>  
                            {{app_name}} Team
                        </p>
                    </td>
                    <td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px;">&nbsp;</td>
                </tr>
                </tbody>
            </table>
        </td>
        <td style="width: 30px;">&nbsp;</td>
    </tr>
    <tr>
        <td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
        <td style="padding: 20px 0 10px 0; width: 640px;" align="left">
            {{contact}}
        </td>
        <td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
    </tr>
    </tbody>
</table>
'
            ]
        );

        // Confirm Old Email Change
        TemplateType::updateOrCreate(
            ['id' => '26'],
            ['name' => 'confirm_old_email']
        );

        Template::updateOrCreate(
            ['id' => '26'],
            [
                'name' => 'Confirm your old email address change',
                'type' => 26,
                'url' => 'null',
                'data' => '
<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
        <td style="width: 30px;">&nbsp;</td>
        <td style="width: 640px; padding-top: 30px;">
            <h2 style="color: #333; font-family: Arial, sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;">
                {{logo}}
            </h2>
        </td>
        <td style="width: 30px;">&nbsp;</td>
    </tr>
    <tr>
        <td style="width: 30px;">&nbsp;</td>
        <td style="width: 640px; padding-top: 30px;">
            <table style="width: 640px; border-bottom: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr>
                    <td style="background: #fff; border-left: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px;">&nbsp;</td>
                    <td style="background: #fff; border-top: 1px solid #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">
                        <p style="font-family: Arial, sans-serif; font-size: 14px; color: #333;">
                            Hi {{name}}, <br/><br/>
                            You have requested to change your old email address. Please use the verification code below to confirm this change:
                        </p>
                        <div style="background:#f5f4f5; border-radius:4px; padding:20px; margin:20px 50px;">
                            <div style="text-align:center; vertical-align:middle; font-size:30px; font-weight: bold; color:#333;">
                                {{otp}}
                            </div>
                        </div>
                        <p><strong>If someone asks for this code</strong></p>
                        <p style="font-family: Arial, sans-serif; font-size: 14px; color: #333;">
                            Don\'t share this code with anyone, especially if they tell you that they work for Faveo Invoicing Community. They may be trying to hack your account.
                        </p>
                        <p><strong>Didn\'t request this?</strong></p>
                        <p style="font-family: Arial, sans-serif; font-size: 14px; color: #333;">
                        If you didn’t request this email verification, someone else may be trying to access your account. Don’t share this code. If you need help, please reach out to our <a href="{{contact_url}}" style="text-decoration: none;">support team.</a>  </p>
                        <p style="font-family: Arial, sans-serif; font-size: 14px; color: #333; margin-top: 20px;">
                            Thank you, <br/>  
                            {{app_name}} Team
                        </p>
                    </td>
                    <td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px;">&nbsp;</td>
                </tr>
                </tbody>
            </table>
        </td>
        <td style="width: 30px;">&nbsp;</td>
    </tr>
    <tr>
        <td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
        <td style="padding: 20px 0 10px 0; width: 640px;" align="left">
            {{contact}}
        </td>
        <td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
    </tr>
    </tbody>
</table>
'
            ]
        );


        TemplateType::updateOrCreate(
            ['id' => '27'],
            ['name' => 'confirm_mobile_number_change']
        );

        Template::updateOrCreate(
            ['id' => '27'],
            [
                'name' => 'Confirm your mobile number change',
                'type' => 27,
                'url' => 'null',
                'data' => '
<table style="background: #f2f2f2; width: 700px;" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
        <td style="width: 30px;">&nbsp;</td>
        <td style="width: 640px; padding-top: 30px;">
            <h2 style="color: #333; font-family: Arial, sans-serif; font-size: 18px; font-weight: bold; padding: 0; margin: 0;">
                {{logo}}
            </h2>
        </td>
        <td style="width: 30px;">&nbsp;</td>
    </tr>
    <tr>
        <td style="width: 30px;">&nbsp;</td>
        <td style="width: 640px; padding-top: 30px;">
            <table style="width: 640px; border-bottom: 1px solid #ccc;" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr>
                    <td style="background: #fff; border-left: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px;">&nbsp;</td>
                    <td style="background: #fff; border-top: 1px solid #ccc; padding: 40px 0 10px 0; width: 560px;" align="left">
                        <p style="font-family: Arial, sans-serif; font-size: 14px; color: #333;">
                            Hi {{name}}, <br/><br/>
                            You have requested to change your mobile number. Please use the verification code below to confirm this change:
                        </p>
                        <div style="background:#f5f4f5; border-radius:4px; padding:20px; margin:20px 50px;">
                            <div style="text-align:center; vertical-align:middle; font-size:30px; font-weight: bold; color:#333;">
                                {{otp}}
                            </div>
                        </div>
                        <p><strong>If someone asks for this code</strong></p>
                        <p style="font-family: Arial, sans-serif; font-size: 14px; color: #333;">
                            Don\'t share this code with anyone, especially if they tell you that they work for Faveo Invoicing Community. They may be trying to hack your account.
                        </p>
                        <p><strong>Didn\'t request this?</strong></p>
                        <p style="font-family: Arial, sans-serif; font-size: 14px; color: #333;">
                            If you didn’t request this email verification, someone else may be trying to access your account. Don’t share this code. If you need help, please reach out to our <a href="{{contact_url}}" style="text-decoration: none;">support team.</a>
                        </p>
                        <p style="font-family: Arial, sans-serif; font-size: 14px; color: #333; margin-top: 20px;">
                            Thank you, <br/>
                            {{app_name}} Team
                        </p>
                    </td>
                    <td style="background: #fff; border-right: 1px solid #ccc; border-top: 1px solid #ccc; width: 40px;">&nbsp;</td>
                </tr>
                </tbody>
            </table>
        </td>
        <td style="width: 30px;">&nbsp;</td>
    </tr>
    <tr>
        <td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
        <td style="padding: 20px 0 10px 0; width: 640px;" align="left">
            {{contact}}
        </td>
        <td style="width: 30px; padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
    </tr>
    </tbody>
</table>
'
            ]
        );


    }
}