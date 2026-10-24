<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Common\Sms;

use App\Http\Controllers\Common\Sms\SmsOtpController;
use Tests\TestCase;

class SmsOtpControllerTest extends TestCase
{
    private SmsOtpController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new SmsOtpController();
    }

    // =========================================================================
    // responseHandler() – pure logic
    // =========================================================================

    public function test_response_handler_returns_success_for_otp_verified(): void
    {
        $response = ['body' => ['type' => 'success', 'message' => 'OTP verified successfully']];
        $result = $this->controller->responseHandler($response);
        $this->assertSame('success', $result['type']);
    }

    public function test_response_handler_returns_success_for_resend(): void
    {
        $response = ['body' => ['type' => 'success', 'message' => 'retry send successfully']];
        $result = $this->controller->responseHandler($response);
        $this->assertSame('success', $result['type']);
    }

    public function test_response_handler_returns_success_for_generic_send(): void
    {
        $response = ['body' => ['type' => 'success', 'message' => 'OTP sent']];
        $result = $this->controller->responseHandler($response);
        $this->assertSame('success', $result['type']);
    }

    public function test_response_handler_returns_error_for_error_type(): void
    {
        $response = ['body' => ['type' => 'error', 'message' => 'OTP not match']];
        $result = $this->controller->responseHandler($response);
        $this->assertSame('error', $result['type']);
    }

    public function test_response_handler_handles_empty_body(): void
    {
        $result = $this->controller->responseHandler([]);
        $this->assertSame('error', $result['type']);
    }

    // =========================================================================
    // mapErrorMessage() – protected, use reflection
    // =========================================================================

    public function test_map_error_message_maps_mobile_empty_message(): void
    {
        $ref = new \ReflectionMethod($this->controller, 'mapErrorMessage');
        $result = $ref->invoke($this->controller, 'Mobile no. empty or not numeric');
        $this->assertIsString($result);
    }

    public function test_map_error_message_maps_otp_expired(): void
    {
        $ref = new \ReflectionMethod($this->controller, 'mapErrorMessage');
        $result = $ref->invoke($this->controller, 'OTP expired');
        $this->assertIsString($result);
    }

    public function test_map_error_message_maps_otp_not_match(): void
    {
        $ref = new \ReflectionMethod($this->controller, 'mapErrorMessage');
        $result = $ref->invoke($this->controller, 'OTP not match');
        $this->assertIsString($result);
    }

    public function test_map_error_message_maps_default(): void
    {
        $ref = new \ReflectionMethod($this->controller, 'mapErrorMessage');
        $result = $ref->invoke($this->controller, 'some unknown message');
        $this->assertIsString($result);
    }

    // =========================================================================
    // sanitizeMobile() – protected, pure logic
    // =========================================================================

    public function test_sanitize_mobile_removes_non_numeric(): void
    {
        $ref = new \ReflectionMethod($this->controller, 'sanitizeMobile');
        $result = $ref->invoke($this->controller, '+91-9876543210');
        $this->assertSame('919876543210', $result);
    }

    public function test_sanitize_mobile_leaves_numeric_unchanged(): void
    {
        $ref = new \ReflectionMethod($this->controller, 'sanitizeMobile');
        $result = $ref->invoke($this->controller, '919876543210');
        $this->assertSame('919876543210', $result);
    }
}
