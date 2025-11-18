<?php

namespace Database\Factories\Model\Common;

use App\Model\Common\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'company'                  => $this->faker->company,
            'website'                  => $this->faker->url,
            'phone'                    => $this->faker->numerify('##########'),
            'logo'                     => 'default-logo.png',
            'phone_country_iso'        => 'IN',
            'address'                  => $this->faker->address,
            'host'                     => 'smtp.example.com',
            'port'                     => 587,
            'encryption'               => 'tls',
            'email'                    => $this->faker->safeEmail,
            'password'                 => 'secret',
            'error_log'                => 0,
            'error_email'              => $this->faker->safeEmail,
            'state'                    => 1,
            'city'                     => $this->faker->city,
            'country'                  => 1,
            'invoice'                  => 1,
            'download'                 => 1,
            'subscription_over'        => 0,
            'subscription_going_to_end'=> 0,
            'forgot_password'          => 1,
            'order_mail'               => 1,
            'welcome_mail'             => 1,
            'invoice_template'         => 'default',
            'driver'                   => 'smtp',
            'admin_logo'               => null,
            'title'                    => 'System Settings',
            'favicon_title'            => 'Faveo',
            'fav_icon'                 => null,
            'company_email'            => $this->faker->safeEmail,
            'favicon_title_client'     => 'Faveo Client',
            'default_currency'         => 'USD',
            'default_symbol'           => '$',
            'file_storage'             => 'local',
            'cin_no'                   => 'CIN123456',
            'gstin'                    => 'GSTIN123456',
            'zip'                      => $this->faker->postcode,
            'from_name'                => 'Faveo Support',
            'phone_code'               => '+91',
            'knowledge_base_url'       => 'https://kb.example.com',
            'content'                  => 'en',
            'version' => 'V1.5.2',
        ];
    }
}
