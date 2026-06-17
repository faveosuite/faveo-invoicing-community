<?php

declare(strict_types=1);

namespace Database\Factories\Model\Order;

use App\Model\Order\InstallationDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model\Order\InstallationDetail>
 */
class InstallationDetailFactory extends Factory
{
    protected $model = InstallationDetail::class;

    public function definition(): array
    {
        return [
            'installation_path' => fake()->url(),
            'installation_ip' => fake()->ipv4(),
            'version' => fake()->randomElement(['1.0.0', '1.1.0', '2.0.0', '2.3.5']),
            'last_active' => fake()->dateTimeBetween('-10 days', 'now'),
        ];
    }
}
