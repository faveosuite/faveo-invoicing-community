<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\User;

use App\Http\Requests\User\ClientRequest;
use Tests\TestCase;

class ClientRequestTest extends TestCase
{
    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new ClientRequest())->authorize());
    }

    public function test_put_rules_contain_required_keys(): void
    {
        $request = new ClientRequest();
        $request->setMethod('PUT');
        $rules = $request->rules();

        $this->assertArrayHasKey('first_name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('country', $rules);
        $this->assertArrayHasKey('position', $rules);
    }

    public function test_patch_rules_contain_required_keys(): void
    {
        $request = ClientRequest::create('/users/5/edit', 'PATCH');
        $request = ClientRequest::createFrom($request, new ClientRequest());
        $rules = $request->rules();

        $this->assertArrayHasKey('first_name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('user_name', $rules);
    }

    public function test_default_method_returns_empty_rules(): void
    {
        $request = new ClientRequest();
        $request->setMethod('GET');

        $this->assertSame([], $request->rules());
    }

    public function test_messages_returns_expected_keys(): void
    {
        $messages = (new ClientRequest())->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('first_name.required', $messages);
        $this->assertArrayHasKey('email.required', $messages);
        $this->assertArrayHasKey('country.required', $messages);
    }
}
