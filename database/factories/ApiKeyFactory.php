<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\ApiKey;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\ApiKey>
 */
class ApiKeyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rzp_key' => $this->faker->sha1,
            'rzp_secret' => $this->faker->sha1,
            'apilayer_key' => $this->faker->sha1,
            'bugsnag_api_key' => $this->faker->sha1,
            'zoho_api_key' => $this->faker->sha1,
            'msg91_auth_key' => $this->faker->sha1,
            'msg91_sender' => 'MSGOTP',
            'msg91_template_id' => (string) $this->faker->randomNumber(6, true),
            'twitter_consumer_key' => $this->faker->sha1,
            'twitter_consumer_secret' => $this->faker->sha1,
            'twitter_access_token' => $this->faker->sha1,
            'access_tooken_secret' => $this->faker->sha1,
            'license_api_secret' => $this->faker->md5,
            'license_api_url' => $this->faker->url,
            'nocaptcha_sitekey' => $this->faker->sha1,
            'captcha_secretCheck' => $this->faker->sha1,
            'update_api_url' => $this->faker->url,
            'update_api_secret' => $this->faker->md5,
            'terms_url' => $this->faker->url,
            'pipedrive_api_key' => $this->faker->sha1,
            'stripe_key' => $this->faker->sha1,
            'stripe_secret' => $this->faker->sha1,
            'license_client_id' => $this->faker->sha1,
            'license_client_secret' => $this->faker->sha1,
            'license_grant_type' => 'client_credentials',
        ];
    }
}
