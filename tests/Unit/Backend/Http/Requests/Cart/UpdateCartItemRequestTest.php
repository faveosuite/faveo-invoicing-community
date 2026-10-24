<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Cart;

use App\Http\Requests\Cart\UpdateCartItemRequest;
use Tests\TestCase;

class UpdateCartItemRequestTest extends TestCase
{
    private UpdateCartItemRequest $req;

    protected function setUp(): void
    {
        parent::setUp();
        $this->req = new UpdateCartItemRequest();
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue($this->req->authorize());
    }

    public function test_all_fields_are_optional(): void
    {
        // No required fields — empty submission is valid
        $v = validator([], $this->req->rules());
        $this->assertFalse($v->fails());
    }

    public function test_quantity_zero_fails(): void
    {
        $v = validator(['quantity' => 0], $this->req->rules());
        $this->assertArrayHasKey('quantity', $v->errors()->toArray());
    }

    public function test_quantity_one_passes(): void
    {
        $v = validator(['quantity' => 1], $this->req->rules());
        $this->assertArrayNotHasKey('quantity', $v->errors()->toArray());
    }

    public function test_agents_zero_fails(): void
    {
        $v = validator(['agents' => 0], $this->req->rules());
        $this->assertArrayHasKey('agents', $v->errors()->toArray());
    }

    public function test_domain_max_255_enforced(): void
    {
        $v = validator(['domain' => str_repeat('d', 256)], $this->req->rules());
        $this->assertArrayHasKey('domain', $v->errors()->toArray());
    }

    public function test_domain_accepts_host_or_ip(): void
    {
        foreach (['support.acme.com', 'acme.com', '10.0.0.5', 'acme.co.in', '1and1.com'] as $domain) {
            $v = validator(['domain' => $domain], $this->req->rules());
            $this->assertArrayNotHasKey('domain', $v->errors()->toArray(), $domain);
        }
    }

    public function test_domain_rejects_scheme_path_or_bare_word(): void
    {
        foreach (['http://acme.com', 'acme.com/path', 'acme', '-bad.com', '123.4444.889', '10.0.0'] as $domain) {
            $v = validator(['domain' => $domain], $this->req->rules());
            $this->assertArrayHasKey('domain', $v->errors()->toArray(), $domain);
        }
    }
}
