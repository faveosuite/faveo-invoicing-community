<?php

namespace Database\Seeders\v4_0_2_4;

use App\ApiKey;
use App\Model\Common\EmailMobileValidationProviders;
use App\Model\Common\ManagerSetting;
use App\Model\Common\Msg91Status;
use App\Http\Controllers\Common\PipedriveController;
use App\Model\Common\PipedriveGroups;
use App\Model\Common\PipedriveLocalFields;
use App\Model\Common\PricingTemplate;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Github\Github;
use App\Model\Mailjob\ExpiryMailDay;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->addMangerRole();
        $this->addMailTemplateForEmailAndMobileChange();
    }

    public function addMangerRole(): void
    {
        foreach (['account', 'sales'] as $role) {
            ManagerSetting::updateOrCreate(
                ['manager_role' => $role],
                ['auto_assign' => 0]
            );
        }
    }

    public function addMailTemplateForEmailAndMobileChange()
    {
        TemplateType::updateOrCreate(
            ['id' => '25'],
            ['name' => 'user_profile_update']
        );

        Template::updateOrCreate(
            ['id' => '25'],
            [
                'name' => 'Verify your new email address',
                'type' => 25,
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
            ['name' => 'user_profile_update']
        );

        Template::updateOrCreate(
            ['id' => '26'],
            [
                'name' => 'Confirm your old email address change',
                'type' => 26,
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
            ['name' => 'user_profile_update']
        );

        Template::updateOrCreate(
            ['id' => '27'],
            [
                'name' => 'Confirm your mobile number change',
                'type' => 27,
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