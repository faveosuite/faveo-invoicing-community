<?php

declare(strict_types=1);

namespace Database\Factories\Model\Product;

use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model\Product\Product>
 */
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
            'name' => fake()->name(),
            'description' => fake()->sentence(),
            'group' => 1,
            'can_modify_agent' => 0,
            'can_modify_quantity' => 0,
            'require_domain' => 1,
            'show_agent' => 1,
            'product_sku' => 'FAVEOCLOUDBETA',
        ];
    }
}
