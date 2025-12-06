<?php

namespace Database\Factories\Model\Front;

use App\Model\Front\FrontendPage;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'slug' => $this->faker->unique()->slug,
            'name' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'url' => $this->faker->url,
            'publish' => $this->faker->boolean,
            'type' => $this->faker->randomElement(['header', 'footer', 'other']),
            'created_at' => now(),
        ];
    }
}
