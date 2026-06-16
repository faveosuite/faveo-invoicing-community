<?php

namespace Database\Factories\Model\Payment;

use Illuminate\Support\Facades\Date;
use App\Model\Payment\Promotion;
use App\Model\Payment\PromotionType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    public function definition(): array
    {
        $typeId = PromotionType::query()->inRandomOrder()->value('id') ?? $this->faker->randomElement([1, 2]);

        $rawValue = $this->faker->numberBetween(5, 80);

        $value = ($typeId == 1) ? $rawValue.'%' : $rawValue;

        return [
            'code' => strtoupper(Str::random(8)),
            'type' => $typeId,
            'uses' => $this->faker->numberBetween(1, 50),
            'value' => $value,
            'start' => Date::now()->subDays(random_int(1, 3))->format('Y-m-d H:i:s'),
            'expiry' => Date::now()->addDays(random_int(3, 10))->format('Y-m-d H:i:s'),
        ];
    }
}
