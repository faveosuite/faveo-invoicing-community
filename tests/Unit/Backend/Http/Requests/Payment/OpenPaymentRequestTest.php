<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Payment;

use App\Http\Requests\Payment\OpenPaymentRequest;
use Tests\TestCase;

class OpenPaymentRequestTest extends TestCase
{
    private function rules(): array
    {
        return (new OpenPaymentRequest())->rules();
    }

    private function valid(): array
    {
        return [
            'name' => 'John Doe', 'email' => 'john@example.com', 'mobile' => '9876543210',
            'address' => '123 Main St', 'city' => 'Mumbai', 'state' => 'Maharashtra',
            'zip' => '400001', 'country' => 'IN', 'company' => 'Acme Ltd',
            'amount' => 100, 'currency' => 'INR', 'gateway' => 'Razorpay',
        ];
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new OpenPaymentRequest())->authorize());
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $v = validator($this->valid(), $this->rules());
        $this->assertFalse($v->fails());
    }

    public function test_validation_fails_when_email_is_missing(): void
    {
        $data = $this->valid();
        unset($data['email']);
        $this->assertArrayHasKey('email', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_validation_fails_when_email_format_is_invalid(): void
    {
        $data = array_merge($this->valid(), ['email' => 'not-an-email']);
        $this->assertArrayHasKey('email', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_validation_fails_when_amount_is_zero(): void
    {
        $data = array_merge($this->valid(), ['amount' => 0]);
        $this->assertArrayHasKey('amount', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_validation_fails_when_amount_is_negative(): void
    {
        $data = array_merge($this->valid(), ['amount' => -10]);
        $this->assertArrayHasKey('amount', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_validation_fails_for_unsupported_currency(): void
    {
        $data = array_merge($this->valid(), ['currency' => 'EUR']);
        $this->assertArrayHasKey('currency', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_validation_fails_for_unsupported_gateway(): void
    {
        $data = array_merge($this->valid(), ['gateway' => 'PayPal']);
        $this->assertArrayHasKey('gateway', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_mobile_min_8_chars_boundary(): void
    {
        $data = array_merge($this->valid(), ['mobile' => '1234567']); // 7 chars
        $this->assertArrayHasKey('mobile', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_zip_max_15_chars_boundary(): void
    {
        $data = array_merge($this->valid(), ['zip' => str_repeat('1', 16)]);
        $this->assertArrayHasKey('zip', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_name_max_100_chars_boundary(): void
    {
        $data = array_merge($this->valid(), ['name' => str_repeat('a', 101)]);
        $this->assertArrayHasKey('name', validator($data, $this->rules())->errors()->toArray());
    }

    public function test_messages_returns_non_empty_array(): void
    {
        $this->assertNotEmpty((new OpenPaymentRequest())->messages());
    }
}
