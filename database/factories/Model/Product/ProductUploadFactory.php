<?php

namespace Database\Factories\Model\Product;

use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductUploadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
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
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'version' => $this->faker->semver,
            'file' => $this->faker->word.'.'.$this->faker->fileExtension,
            'is_private' => $this->faker->boolean,
            'is_restricted' => $this->faker->boolean,
            'release_type' => $this->faker->randomElement(['official', 'beat']),
            'dependencies' => null,
        ];
    }
}
