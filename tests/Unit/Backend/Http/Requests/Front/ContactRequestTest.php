<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Front;

use App\Http\Requests\Front\ContactRequest;
use Tests\TestCase;

class ContactRequestTest extends TestCase
{
    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new ContactRequest())->authorize());
    }

    public function test_contact_us_rules_contain_expected_keys(): void
    {
        $request = ContactRequest::create('/contact-us', 'POST');
        $request = ContactRequest::createFrom($request, new ContactRequest());
        $rules = $request->rules();

        $this->assertArrayHasKey('conName', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('contact', $rules);
    }

    public function test_demo_request_rules_contain_expected_keys(): void
    {
        $request = ContactRequest::create('/demo-request', 'POST');
        $request = ContactRequest::createFrom($request, new ContactRequest());
        $rules = $request->rules();

        $this->assertArrayHasKey('demoname', $rules);
        $this->assertArrayHasKey('demoemail', $rules);
        $this->assertArrayHasKey('demo', $rules);
    }

    public function test_other_path_returns_empty_rules(): void
    {
        $request = ContactRequest::create('/other-path', 'POST');
        $request = ContactRequest::createFrom($request, new ContactRequest());

        $this->assertSame([], $request->rules());
    }

    public function test_messages_returns_expected_keys(): void
    {
        $messages = (new ContactRequest())->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('conName.required', $messages);
        $this->assertArrayHasKey('demoname.required', $messages);
    }
}
