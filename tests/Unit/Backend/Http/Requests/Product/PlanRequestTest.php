<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Product;

use App\Http\Requests\PlanRequest;
use App\Model\Product\Product;
use Tests\DBTestCase;

class PlanRequestTest extends DBTestCase
{
    /** @return array<string, string> */
    private function errorsFor(int $showAgent, array $input): array
    {
        $product = Product::factory()->create(['show_agent' => $showAgent]);
        $request = new PlanRequest(['product' => $product->id] + $input);

        return validator($request->all(), $request->rules(), $request->messages())->errors()->toArray();
    }

    public function test_agent_product_requires_agents_even_with_a_leftover_quantity(): void
    {
        $this->assertArrayHasKey('no_of_agents', $this->errorsFor(1, ['product_quantity' => 1]));
    }

    public function test_agent_product_accepts_zero_agents_for_unlimited(): void
    {
        $this->assertArrayNotHasKey('no_of_agents', $this->errorsFor(1, ['no_of_agents' => 0]));
    }

    public function test_quantity_product_requires_quantity_even_with_leftover_agents(): void
    {
        $errors = $this->errorsFor(0, ['no_of_agents' => 5]);

        $this->assertArrayHasKey('product_quantity', $errors);
        $this->assertArrayNotHasKey('no_of_agents', $errors);
    }
}
