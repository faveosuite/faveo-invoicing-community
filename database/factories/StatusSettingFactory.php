<?php

namespace Database\Factories;

use App\Model\Common\StatusSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StatusSetting>
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
            'expiry_mail' => random_int(0, 1),
            'subs_expirymail' => random_int(0, 1),
            'activity_log_delete' => random_int(0, 1),
            'github_status' => random_int(0, 1),
            'mailchimp_status' => random_int(0, 1),
            'twitter_status' => random_int(0, 1),
            'msg91_status' => random_int(0, 1),
            'emailverification_status' => random_int(0, 1),
            'recaptcha_status' => random_int(0, 1),
            'zoho_status' => random_int(0, 1),
            'rzp_status' => random_int(0, 1),
            'mailchimp_product_status' => random_int(0, 1),
            'mailchimp_ispaid_status' => random_int(0, 1),
            'terms' => random_int(0, 1),
            'pipedrive_status' => random_int(0, 1),
            'domain_check' => random_int(0, 1),
        ];
    }
}
