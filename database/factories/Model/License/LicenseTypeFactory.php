<?php

declare(strict_types=1);

namespace Database\Factories\Model\License;

use App\Model\License\LicenseType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LicenseType>
 */
class LicenseTypeFactory extends Factory
{
    protected $model = LicenseType::class;

    public function definition()
    {
        return [
            'name' => fake()->unique()->word(),
        ];
    }
}
