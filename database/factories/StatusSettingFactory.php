<?php

namespace Database\Factories;

use App\Model\Common\StatusSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model\Common\StatusSetting>
 */
class StatusSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = StatusSetting::class;

    public function definition(): array
    {
        return [
            'expiry_mail' => rand(0, 1),
            'subs_expirymail' => rand(0, 1),
            'activity_log_delete' => rand(0, 1),
            'license_status' => rand(0, 1),
            'github_status' => rand(0, 1),
            'mailchimp_status' => rand(0, 1),
            'twitter_status' => rand(0, 1),
            'msg91_status' => rand(0, 1),
            'emailverification_status' => rand(0, 1),
            'recaptcha_status' => rand(0, 1),
            'v3_recaptcha_status' => rand(0, 1),
            'zoho_status' => rand(0, 1),
            'rzp_status' => rand(0, 1),
            'mailchimp_product_status' => rand(0, 1),
            'mailchimp_ispaid_status' => rand(0, 1),
            'terms' => rand(0, 1),
            'pipedrive_status' => rand(0, 1),
            'domain_check' => rand(0, 1),
            'v3_v2_recaptcha_status' => rand(0, 1),
        ];
    }
}
