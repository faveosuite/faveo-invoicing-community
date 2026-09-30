<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Order;

use App\Http\Requests\InvoiceRequest;
use Tests\TestCase;

class InvoiceRequestTest extends TestCase
{
    private function rules(): array
    {
        return (new InvoiceRequest())->rules();
    }

    private function valid(): array
    {
        return ['user' => 1, 'date' => '2025-01-15', 'price' => 99.99, 'product' => 1];
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new InvoiceRequest())->authorize());
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $v = validator($this->valid(), $this->rules());
        $this->assertFalse($v->fails());
    }

    public function test_validation_fails_when_user_missing(): void
    {
        $data = $this->valid();
        unset($data['user']);
        $this->assertArrayHasKey('user', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_validation_fails_when_date_missing(): void
    {
        $data = $this->valid();
        unset($data['date']);
        $this->assertArrayHasKey('date', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_validation_fails_when_date_is_not_a_date(): void
    {
        $data = array_merge($this->valid(), ['date' => 'not-a-date']);
        $this->assertArrayHasKey('date', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_validation_fails_when_price_missing(): void
    {
        $data = $this->valid();
        unset($data['price']);
        $this->assertArrayHasKey('price', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_validation_fails_when_product_missing(): void
    {
        $data = $this->valid();
        unset($data['product']);
        $this->assertArrayHasKey('product', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_domain_regex_rejects_invalid_domain(): void
    {
        $data = array_merge($this->valid(), ['domain' => 'not a valid domain!!']);
        $this->assertArrayHasKey('domain', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_messages_returns_non_empty_array(): void
    {
        $this->assertNotEmpty((new InvoiceRequest())->messages());
    }
}
