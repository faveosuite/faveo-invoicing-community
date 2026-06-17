<?php

declare(strict_types=1);

namespace Database\Factories\Model\Front;

use App\Model\Front\FrontendPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model\Front\FrontendPage>
 */
class FrontendPageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FrontendPage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'parent_page_id' => 0,
            'slug' => fake()->unique()->slug,
            'name' => fake()->sentence,
            'content' => fake()->paragraph,
            'url' => fake()->url,
            'publish' => fake()->boolean,
            'type' => fake()->randomElement(['header', 'footer', 'other']),
            'created_at' => now(),
        ];
    }
}
