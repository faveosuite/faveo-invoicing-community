<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\User;

use App\Http\Requests\ValidateSecretRequest;
use Tests\TestCase;

class ValidateSecretRequestTest extends TestCase
{
    private function rules(): array
    {
        return (new ValidateSecretRequest())->rules();
    }

    public function test_totp_is_required(): void
    {
        $v = validator([], $this->rules());
        $this->assertArrayHasKey('totp', $v->errors()->toArray());
    }

    public function test_totp_must_be_6_digits(): void
    {
        $v = validator(['totp' => '12345'], $this->rules()); // 5 digits
        $this->assertArrayHasKey('totp', $v->errors()->toArray());
    }

    public function test_totp_must_be_digits_only(): void
    {
        $v = validator(['totp' => 'abc123'], $this->rules());
        $this->assertArrayHasKey('totp', $v->errors()->toArray());
    }

    public function test_totp_7_digits_fails(): void
    {
        $v = validator(['totp' => '1234567'], $this->rules());
        $this->assertArrayHasKey('totp', $v->errors()->toArray());
    }

    public function test_messages_returns_non_empty_array(): void
    {
        $this->assertNotEmpty((new ValidateSecretRequest())->messages());
    }
}
