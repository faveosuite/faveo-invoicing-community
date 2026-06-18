<?php

declare(strict_types=1);

namespace Database\Factories\Model\Product;

use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model\Product\ProductUpload>
 */
class ProductUploadFactory extends Factory
{
    protected $model = ProductUpload::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'title' => fake()->sentence,
            'description' => fake()->paragraph,
            'version' => fake()->semver,
            'file' => fake()->word.'.'.fake()->fileExtension,
            'is_private' => fake()->boolean,
            'is_restricted' => fake()->boolean,
            'release_type' => fake()->randomElement(['official', 'beat']),
            'dependencies' => null,
        ];
    }
}
