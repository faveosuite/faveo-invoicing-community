<?php

namespace Database\Factories\Model\Product;

use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Product::class;

    public function definition()
    {
        return [

            'name' => $this->faker->name(),
            'description' => $this->faker->sentence(),
            // 'type'                => 1,
            'group' => 1,
            'can_modify_agent' => 0,
            'can_modify_quantity' => 0,
            'require_domain' => 1,
            'show_agent' => 1,
            'product_sku' => 'FAVEOCLOUDBETA',
        ];
    }
}
