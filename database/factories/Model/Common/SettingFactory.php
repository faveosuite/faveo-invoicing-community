<?php

declare(strict_types=1);

namespace Database\Factories\Model\Common;

use App\Model\Common\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model\Common\Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Setting::class;

    public function definition()
    {
        return [
            'company' => fake()->company,
            'website' => fake()->url,
            'phone' => fake()->numerify('##########'),
            'logo' => 'default-logo.png',
            'phone_country_iso' => 'IN',
            'address' => fake()->address,
            'host' => 'smtp.example.com',
            'port' => 587,
            'encryption' => 'tls',
            'email' => fake()->safeEmail,
            'password' => 'secret',
            'error_log' => 0,
            'error_email' => fake()->safeEmail,
            'state' => 1,
            'city' => fake()->city,
            'country' => 1,
            'invoice' => 1,
            'download' => 1,
            'subscription_over' => 0,
            'subscription_going_to_end' => 0,
            'forgot_password' => 1,
            'order_mail' => 1,
            'welcome_mail' => 1,
            'invoice_template' => 'default',
            'driver' => 'smtp',
            'admin_logo' => null,
            'title' => 'System Settings',
            'favicon_title' => 'Faveo',
            'fav_icon' => null,
            'company_email' => fake()->safeEmail,
            'favicon_title_client' => 'Faveo Client',
            'default_currency' => 'USD',
            'default_symbol' => '$',
            'file_storage' => 'local',
            'cin_no' => 'CIN123456',
            'gstin' => 'GSTIN123456',
            'zip' => fake()->postcode,
            'from_name' => 'Faveo Support',
            'phone_code' => '+91',
            'knowledge_base_url' => 'https://kb.example.com',
            'content' => 'en',
            'version' => 'V1.5.2',
        ];
    }
}
