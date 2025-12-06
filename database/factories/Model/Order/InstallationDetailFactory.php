<?php

namespace Database\Factories\Model\Order;

use App\Model\Order\InstallationDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstallationDetailFactory extends Factory
{
    protected $model = InstallationDetail::class;
    public function definition(): array
    {
        return [
            'installation_path' => $this->faker->url(),
            'installation_ip'   => $this->faker->ipv4(),
            'version'           => $this->faker->randomElement(['1.0.0', '1.1.0', '2.0.0', '2.3.5']),
            'last_active'       => $this->faker->dateTimeBetween('-10 days', 'now'),
        ];
    }
}
