<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Product;

use App\Http\Requests\Product\AddonRequest;
use Tests\TestCase;

class AddonRequestTest extends TestCase
{
    private function rules(): array
    {
        return (new AddonRequest())->rules();
    }

    private function valid(): array
    {
        return ['name' => 'Extra Support', 'subscription' => 1, 'regular_price' => 99.99, 'selling_price' => 79.99, 'products' => [1]];
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new AddonRequest())->authorize());
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $v = validator($this->valid(), $this->rules());
        $this->assertFalse($v->fails());
    }

    public function test_each_required_field_fails_when_missing(): void
    {
        foreach (['name', 'subscription', 'regular_price', 'selling_price', 'products'] as $field) {
            $data = $this->valid();
            unset($data[$field]);
            $errors = validator($data, $this->rules())->errors()->toArray();
            $this->assertArrayHasKey($field, $errors, "Expected '$field' error when missing");
        }
    }

    public function test_regular_price_must_be_numeric(): void
    {
        $data = array_merge($this->valid(), ['regular_price' => 'free']);
        $this->assertArrayHasKey('regular_price', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_selling_price_must_be_numeric(): void
    {
        $data = array_merge($this->valid(), ['selling_price' => 'free']);
        $this->assertArrayHasKey('selling_price', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_messages_returns_expected_keys(): void
    {
        $messages = (new AddonRequest())->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('regular_price.numeric', $messages);
        $this->assertArrayHasKey('products.required', $messages);
    }
}
