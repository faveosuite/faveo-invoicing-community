<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Front;

use App\Http\Requests\Front\CheckoutRequest;
use Tests\TestCase;

class CheckoutRequestTest extends TestCase
{
    private function postRules(): array
    {
        $request = new CheckoutRequest();
        $request->setMethod('POST');

        return $request->rules();
    }

    private function validPost(): array
    {
        return [
            'first_name' => 'John', 'last_name' => 'Doe', 'company' => 'Acme',
            'address' => '123 Main St', 'zip' => '12345', 'email' => 'john@example.com',
        ];
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new CheckoutRequest())->authorize());
    }

    public function test_validation_passes_with_valid_post_data(): void
    {
        $v = validator($this->validPost(), $this->postRules());
        $this->assertFalse($v->fails());
    }

    public function test_first_name_required_for_post(): void
    {
        $data = $this->validPost();
        unset($data['first_name']);
        $this->assertArrayHasKey('first_name', validator($data, $this->postRules())->errors()->toArray());
    }

    public function test_last_name_required_for_post(): void
    {
        $data = $this->validPost();
        unset($data['last_name']);
        $this->assertArrayHasKey('last_name', validator($data, $this->postRules())->errors()->toArray());
    }

    public function test_email_must_be_valid_format(): void
    {
        $data = array_merge($this->validPost(), ['email' => 'not-email']);
        $this->assertArrayHasKey('email', validator($data, $this->postRules())->errors()->toArray());
    }

    public function test_zip_must_be_numeric(): void
    {
        $data = array_merge($this->validPost(), ['zip' => 'ABCDE']);
        $this->assertArrayHasKey('zip', validator($data, $this->postRules())->errors()->toArray());
    }

    public function test_zip_value_below_5_fails(): void
    {
        // min:5 on a numeric field is a value check (not length) — value 4 < 5 fails
        $data = array_merge($this->validPost(), ['zip' => '4']);
        $this->assertArrayHasKey('zip', validator($data, $this->postRules())->errors()->toArray());
    }

    public function test_get_method_returns_empty_rules(): void
    {
        $request = new CheckoutRequest();
        $request->setMethod('GET');
        $v = validator(['anything' => 'value'], $request->rules());
        $this->assertFalse($v->fails());
    }
}
