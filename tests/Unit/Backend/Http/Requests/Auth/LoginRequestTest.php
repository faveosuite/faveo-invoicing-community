<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Auth;

use App\Http\Requests\Auth\LoginRequest;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    private LoginRequest $req;

    protected function setUp(): void
    {
        parent::setUp();
        $this->req = new LoginRequest();
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue($this->req->authorize());
    }

    public function test_rules_define_email_username_required(): void
    {
        $rules = $this->req->rules();
        $this->assertArrayHasKey('email_username', $rules);
        $this->assertContains('required', $rules['email_username']);
    }

    public function test_rules_define_password_required(): void
    {
        $rules = $this->req->rules();
        $this->assertArrayHasKey('password1', $rules);
        $this->assertContains('required', $rules['password1']);
    }

    public function test_validation_fails_when_email_username_missing(): void
    {
        $v = validator(['password1' => 'secret'], $this->req->rules());
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('email_username', $v->errors()->toArray());
    }

    public function test_validation_fails_when_password_missing(): void
    {
        $v = validator(['email_username' => 'user@test.com'], $this->req->rules());
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('password1', $v->errors()->toArray());
    }

    public function test_messages_returns_non_empty_array(): void
    {
        $this->assertNotEmpty($this->req->messages());
    }
}
