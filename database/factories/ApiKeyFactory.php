<?php

declare(strict_types=1);

namespace Database\Factories;

use App\ApiKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApiKey>
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
            'rzp_key' => fake()->sha1,
            'rzp_secret' => fake()->sha1,
            'apilayer_key' => fake()->sha1,
            'bugsnag_api_key' => fake()->sha1,
            'zoho_api_key' => fake()->sha1,
            'msg91_auth_key' => fake()->sha1,
            'msg91_sender' => 'MSGOTP',
            'msg91_template_id' => (string) fake()->randomNumber(6, strict: true),
            'twitter_consumer_key' => fake()->sha1,
            'twitter_consumer_secret' => fake()->sha1,
            'twitter_access_token' => fake()->sha1,
            'access_tooken_secret' => fake()->sha1,
            'nocaptcha_sitekey' => fake()->sha1,
            'captcha_secretCheck' => fake()->sha1,
            'update_api_url' => fake()->url,
            'update_api_secret' => fake()->md5,
            'terms_url' => fake()->url,
            'pipedrive_api_key' => fake()->sha1,
            'stripe_key' => fake()->sha1,
            'stripe_secret' => fake()->sha1,
        ];
    }
}
