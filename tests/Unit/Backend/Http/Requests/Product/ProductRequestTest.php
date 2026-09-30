<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Product;

use App\Http\Requests\Product\ProductRequest;
use Tests\TestCase;

class ProductRequestTest extends TestCase
{
    private function rules(): array
    {
        return (new ProductRequest())->rules();
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new ProductRequest())->authorize());
    }

    public function test_rules_require_core_fields(): void
    {
        $rules = $this->rules();
        foreach (['name', 'type', 'group', 'subscription', 'currency'] as $field) {
            $this->assertArrayHasKey($field, $rules, "Missing: $field");
            $this->assertContains('required', (array) $rules[$field]);
        }
    }

    public function test_validation_fails_when_name_missing(): void
    {
        $v = validator(['type' => '1', 'group' => '1', 'subscription' => '1', 'currency' => 'USD',
            'github_owner' => 'owner', 'github_repository' => 'repo'], $this->rules());
        $this->assertArrayHasKey('name', $v->errors()->toArray());
    }

    public function test_validation_fails_when_type_missing(): void
    {
        $v = validator(['name' => 'Product', 'group' => '1', 'subscription' => '1', 'currency' => 'USD',
            'github_owner' => 'owner', 'github_repository' => 'repo'], $this->rules());
        $this->assertArrayHasKey('type', $v->errors()->toArray());
    }

    public function test_messages_returns_non_empty_array(): void
    {
        $this->assertNotEmpty((new ProductRequest())->messages());
    }
}
