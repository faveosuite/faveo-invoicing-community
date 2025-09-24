<?php

namespace Database\Factories\Model\Product;

use App\Model\Common\PricingTemplate;
use App\Model\Product\ProductGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductGroupFactory extends Factory
{
    protected $model = ProductGroup::class;

    public function definition()
    {
        $template = PricingTemplate::query()->first();

        return [
            'name' => $this->faker->unique()->words(2, true),
            'headline' => $this->faker->sentence,
            'tagline' => $this->faker->sentence(3),
            'available_payment' => 'stripe,razorpay',
            'hidden' => 0,
            'cart_link' => $this->faker->url,
            'pricing_templates_id' => $template->id,
            'status' => 1,
        ];
    }
}
