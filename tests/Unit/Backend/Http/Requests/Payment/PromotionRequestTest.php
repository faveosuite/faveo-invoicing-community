<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Payment;

use App\Http\Requests\Payment\PromotionRequest;
use Tests\TestCase;

class PromotionRequestTest extends TestCase
{
    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new PromotionRequest())->authorize());
    }

    public function test_rules_define_code_required(): void
    {
        $rules = (new PromotionRequest())->rules();
        $this->assertArrayHasKey('code', $rules);
    }

    public function test_validation_fails_when_code_missing(): void
    {
        $v = validator(['type' => '2', 'applied' => '2025-01-01', 'uses' => 10,
            'start' => '2025-01-01', 'expiry' => '2025-12-31', 'value' => 50],
            (new PromotionRequest())->rules());
        $this->assertArrayHasKey('code', $v->errors()->toArray());
    }

    public function test_validation_fails_when_uses_is_not_numeric(): void
    {
        $data = ['code' => 'PROMO10', 'type' => '2', 'applied' => '2025-01-01',
            'uses' => 'many', 'start' => '2025-01-01', 'expiry' => '2025-12-31', 'value' => 50];
        $v = validator($data, (new PromotionRequest())->rules());
        $this->assertArrayHasKey('uses', $v->errors()->toArray());
    }

    public function test_validation_fails_when_expiry_before_start(): void
    {
        $data = ['code' => 'PROMO10', 'type' => '2', 'applied' => '2025-01-01',
            'uses' => 10, 'start' => '2025-12-31', 'expiry' => '2025-01-01', 'value' => 50];
        $v = validator($data, (new PromotionRequest())->rules());
        $this->assertArrayHasKey('expiry', $v->errors()->toArray());
    }

    public function test_percentage_type_value_must_be_between_1_and_100(): void
    {
        // type '1' → percentage → value must be between 1 and 100
        $request = new PromotionRequest();
        $request->merge(['type' => '1']);
        $v = validator(['code' => 'P', 'type' => '1', 'applied' => '2025-01-01',
            'uses' => 5, 'start' => '2025-01-01', 'expiry' => '2025-12-31', 'value' => 150],
            $request->rules());
        $this->assertArrayHasKey('value', $v->errors()->toArray());
    }

    public function test_messages_returns_non_empty_array(): void
    {
        $this->assertNotEmpty((new PromotionRequest())->messages());
    }
}
