<?php

declare(strict_types=1);

namespace Database\Factories\Model\Common;

use App\Model\Common\SeoDefaultPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeoDefaultPage>
 */
class SeoDefaultPageFactory extends Factory
{
    protected $model = SeoDefaultPage::class;

    /**
     * @return array<mixed>
     */
    public function definition()
    {
        return [
            'page_key' => fake()->unique()->slug(2),
            'meta_title' => fake()->sentence(4),
            'meta_description' => fake()->sentence(10),
            'og_title' => fake()->sentence(4),
            'og_description' => fake()->sentence(10),
            'og_image' => null,
            'og_same_as_meta' => false,
        ];
    }
}
