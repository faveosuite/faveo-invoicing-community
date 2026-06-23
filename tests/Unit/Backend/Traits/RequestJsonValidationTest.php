<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Traits;

use App\Traits\RequestJsonValidation;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RequestJsonValidationTest extends TestCase
{
    private function subject(): object
    {
        return new class {
            use RequestJsonValidation;

            public function callFailedValidation(\Illuminate\Contracts\Validation\Validator $v): void
            {
                $this->failedValidation($v);
            }

            public function callFailedAuthorization(): never
            {
                $this->failedAuthorization();
            }
        };
    }

    // --- messages() ---

    public function test_messages_returns_non_empty_array(): void
    {
        $result = $this->subject()->messages();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_messages_defines_wildcard_required_message(): void
    {
        $messages = $this->subject()->messages();

        $this->assertArrayHasKey('*.required', $messages);
        $this->assertSame('This field is required', $messages['*.required']);
    }

    public function test_messages_defines_required_if_message(): void
    {
        $messages = $this->subject()->messages();

        $this->assertArrayHasKey('*.required_if', $messages);
    }

    // --- failedValidation(): throws HttpResponseException with 412 status ---

    public function test_failed_validation_throws_http_response_exception(): void
    {
        $validator = Validator::make([], ['name' => 'required']);

        $this->expectException(HttpResponseException::class);

        $this->subject()->callFailedValidation($validator);
    }

    public function test_failed_validation_response_has_412_status(): void
    {
        $validator = Validator::make([], ['name' => 'required', 'email' => 'required|email']);

        try {
            $this->subject()->callFailedValidation($validator);
            $this->fail('Expected HttpResponseException');
        } catch (HttpResponseException $e) {
            $this->assertSame(412, $e->getResponse()->getStatusCode());
        }
    }

    public function test_failed_validation_response_contains_field_errors(): void
    {
        $validator = Validator::make([], ['name' => 'required']);

        try {
            $this->subject()->callFailedValidation($validator);
        } catch (HttpResponseException $e) {
            $body = json_decode((string) $e->getResponse()->getContent(), true);
            $this->assertFalse($body['success']);
            $this->assertArrayHasKey('name', (array) ($body['message'] ?? []));
        }
    }

    // --- failedAuthorization(): throws HttpResponseException with 400 status ---

    public function test_failed_authorization_throws_http_response_exception(): void
    {
        $this->expectException(HttpResponseException::class);

        $this->subject()->callFailedAuthorization();
    }

    public function test_failed_authorization_response_has_400_status(): void
    {
        try {
            $this->subject()->callFailedAuthorization();
            $this->fail('Expected HttpResponseException');
        } catch (HttpResponseException $e) {
            $this->assertSame(400, $e->getResponse()->getStatusCode());
        }
    }

    
}
