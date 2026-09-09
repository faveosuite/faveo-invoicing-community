<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Payment;

use App\Http\Requests\Payment\TaxRequest;
use Tests\TestCase;

class TaxRequestTest extends TestCase
{
    private TaxRequest $req;

    protected function setUp(): void
    {
        parent::setUp();
        $this->req = new TaxRequest();
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue($this->req->authorize());
    }

    public function test_rules_define_all_required_fields(): void
    {
        $rules = $this->req->rules();
        foreach (['name', 'rate', 'level', 'country', 'state'] as $field) {
            $this->assertArrayHasKey($field, $rules, "Missing rule for: $field");
            $this->assertContains('required', (array) $rules[$field]);
        }
    }

    public function test_validation_fails_when_name_missing(): void
    {
        $v = validator(['rate' => 10, 'level' => 1, 'country' => 'US', 'state' => 'CA'], $this->req->rules());
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('name', $v->errors()->toArray());
    }

    public function test_validation_fails_when_rate_missing(): void
    {
        $v = validator(['name' => 'GST', 'level' => 1, 'country' => 'US', 'state' => 'CA'], $this->req->rules());
        $this->assertArrayHasKey('rate', $v->errors()->toArray());
    }

    public function test_validation_fails_when_rate_is_not_numeric(): void
    {
        $v = validator(['name' => 'GST', 'rate' => 'ten', 'level' => 1, 'country' => 'US', 'state' => 'CA'], $this->req->rules());
        $this->assertArrayHasKey('rate', $v->errors()->toArray());
    }

    public function test_validation_fails_when_level_is_not_integer(): void
    {
        $v = validator(['name' => 'GST', 'rate' => 10, 'level' => 'high', 'country' => 'US', 'state' => 'CA'], $this->req->rules());
        $this->assertArrayHasKey('level', $v->errors()->toArray());
    }

    public function test_validation_passes_with_complete_valid_data(): void
    {
        $v = validator(['name' => 'GST', 'rate' => 18.0, 'level' => 1, 'country' => 'IN', 'state' => 'KA'], $this->req->rules());
        $this->assertFalse($v->fails());
    }

    public function test_messages_return_non_empty_array(): void
    {
        $this->assertNotEmpty($this->req->messages());
    }
}
