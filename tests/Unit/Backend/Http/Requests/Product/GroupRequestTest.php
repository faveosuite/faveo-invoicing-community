<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Product;

use App\Http\Requests\Product\GroupRequest;
use Tests\TestCase;

class GroupRequestTest extends TestCase
{
    private function rules(): array
    {
        return (new GroupRequest())->rules();
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new GroupRequest())->authorize());
    }

    public function test_name_is_required(): void
    {
        $v = validator(['pricing_templates_id' => 1], $this->rules());
        $this->assertArrayHasKey('name', $v->errors()->toArray());
    }

    public function test_pricing_templates_id_is_required(): void
    {
        $v = validator(['name' => 'Support'], $this->rules());
        $this->assertArrayHasKey('pricing_templates_id', $v->errors()->toArray());
    }

    public function test_headline_is_optional(): void
    {
        $v = validator(['name' => 'Support', 'pricing_templates_id' => 1], $this->rules());
        $this->assertArrayNotHasKey('headline', $v->errors()->toArray());
    }

    public function test_status_must_be_boolean_when_present(): void
    {
        $v = validator(['name' => 'S', 'pricing_templates_id' => 1, 'status' => 'yes'], $this->rules());
        $this->assertArrayHasKey('status', $v->errors()->toArray());
    }
}
