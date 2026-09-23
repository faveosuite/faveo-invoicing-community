<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Cart;

use App\Http\Requests\Cart\AddCartItemRequest;
use Tests\TestCase;

class AddCartItemRequestTest extends TestCase
{
    private AddCartItemRequest $req;

    protected function setUp(): void
    {
        parent::setUp();
        $this->req = new AddCartItemRequest();
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue($this->req->authorize());
    }

    public function test_rules_require_product_id(): void
    {
        $rules = $this->req->rules();
        $this->assertArrayHasKey('product_id', $rules);
        $this->assertContains('required', $rules['product_id']);
    }

    public function test_validation_fails_when_product_id_missing(): void
    {
        $v = validator([], $this->req->rules());
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('product_id', $v->errors()->toArray());
    }

    public function test_validation_fails_when_product_id_is_not_integer(): void
    {
        $v = validator(['product_id' => 'abc'], $this->req->rules());
        $this->assertTrue($v->fails());
    }

    public function test_quantity_min_boundary_of_one_passes(): void
    {
        $v = validator(['product_id' => 1, 'quantity' => 1], $this->req->rules());
        // product_id exists check will fail in unit context; check quantity rule independently
        $errors = $v->errors()->toArray();
        $this->assertArrayNotHasKey('quantity', $errors);
    }

    public function test_quantity_zero_fails(): void
    {
        $v = validator(['product_id' => 1, 'quantity' => 0], $this->req->rules());
        $this->assertArrayHasKey('quantity', $v->errors()->toArray());
    }

    public function test_billing_cycle_invalid_value_fails(): void
    {
        $v = validator(['product_id' => 1, 'billing_cycle' => 'quarterly'], $this->req->rules());
        $this->assertArrayHasKey('billing_cycle', $v->errors()->toArray());
    }

    public function test_billing_cycle_valid_values_pass(): void
    {
        foreach (['monthly', 'yearly', 'onetime'] as $cycle) {
            $v = validator(['product_id' => 1, 'billing_cycle' => $cycle], $this->req->rules());
            $this->assertArrayNotHasKey('billing_cycle', $v->errors()->toArray(), "Cycle '$cycle' should pass");
        }
    }

    public function test_domain_max_255_chars_boundary_fails(): void
    {
        $v = validator(['product_id' => 1, 'domain' => str_repeat('a', 256)], $this->req->rules());
        $this->assertArrayHasKey('domain', $v->errors()->toArray());
    }
}
