<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Order;

use App\Http\Requests\Order\OrderRequest;
use Tests\TestCase;

class OrderRequestTest extends TestCase
{
    private function rules(): array
    {
        return (new OrderRequest())->rules();
    }

    private function valid(): array
    {
        return [
            'client' => 1, 'payment_method' => 'Stripe', 'promotion_code' => 'NONE',
            'order_status' => 'Active', 'product' => 1, 'subscription' => 1,
        ];
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new OrderRequest())->authorize());
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $v = validator($this->valid(), $this->rules());
        $this->assertFalse($v->fails());
    }

    public function test_each_required_field_fails_when_missing(): void
    {
        foreach (['client', 'payment_method', 'promotion_code', 'order_status', 'product', 'subscription'] as $field) {
            $data = $this->valid();
            unset($data[$field]);
            $v = validator($data, $this->rules());
            $this->assertArrayHasKey($field, $v->errors()->toArray(), "Expected '$field' to fail when missing");
        }
    }

    public function test_price_override_must_be_numeric(): void
    {
        $data = array_merge($this->valid(), ['price_override' => 'free']);
        $this->assertArrayHasKey('price_override', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_qty_must_be_integer(): void
    {
        $data = array_merge($this->valid(), ['qty' => 'two']);
        $this->assertArrayHasKey('qty', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_messages_returns_expected_keys(): void
    {
        $messages = (new OrderRequest())->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('client.required', $messages);
        $this->assertArrayHasKey('price_override.numeric', $messages);
        $this->assertArrayHasKey('subscription.required', $messages);
    }
}
