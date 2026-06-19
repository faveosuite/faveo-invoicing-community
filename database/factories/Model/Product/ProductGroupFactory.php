<?php

declare(strict_types=1);

namespace Database\Factories\Model\Product;

use App\Model\Common\PricingTemplate;
use App\Model\Product\ProductGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model\Product\ProductGroup>
 */
class ProductGroupFactory extends Factory
{
    protected $model = ProductGroup::class;

    public function definition()
    {
        /** @var \App\Model\Common\PricingTemplate $template */
        $template = PricingTemplate::query()->first();

        return [
            'name' => fake()->unique()->words(2, asText: true),
            'headline' => fake()->sentence,
            'tagline' => fake()->sentence(3),
            'available_payment' => 'stripe,razorpay',
            'hidden' => 0,
            'cart_link' => fake()->url,
            'pricing_templates_id' => $template->id,
            'status' => 1,
        ];
    }
}
