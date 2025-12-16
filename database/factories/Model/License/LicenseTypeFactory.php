<?php

namespace Database\Factories\Model\License;


use App\Model\License\LicenseType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseTypeFactory extends Factory
{
    protected $model = LicenseType::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word(),
        ];
    }
}
