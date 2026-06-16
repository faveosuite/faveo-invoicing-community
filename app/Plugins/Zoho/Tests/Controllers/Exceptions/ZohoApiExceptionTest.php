<?php

namespace App\Plugins\Zoho\Tests\Controllers\Exceptions;

use Exception;
use App\Plugins\Zoho\Controllers\Exceptions\ZohoApiException;
use Tests\DBTestCase;

class ZohoApiExceptionTest extends DBTestCase
{
    public function test_it_can_be_instantiated_with_error_id_and_message()
    {
        $exception = new ZohoApiException('test_error', 'Test error message');

        $this->assertInstanceOf(ZohoApiException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function test_it_stores_error_id()
    {
        $exception = new ZohoApiException('custom_error_id', 'Error occurred');

        $this->assertEquals('custom_error_id', $exception->getErrorId());
    }

    public function test_it_stores_error_message()
    {
        $exception = new ZohoApiException('error_id', 'This is the error message');

        $this->assertEquals('This is the error message', $exception->getMessage());
    }

    public function test_it_can_be_thrown_and_caught(): never
    {
        $this->expectException(ZohoApiException::class);
        $this->expectExceptionMessage('Test exception');

        throw new ZohoApiException('test', 'Test exception');
    }

    public function test_it_can_be_caught_as_generic_exception()
    {
        try {
            throw new ZohoApiException('error', 'Message');
        } catch (Exception $e) {
            $this->assertInstanceOf(ZohoApiException::class, $e);
            $this->assertEquals('error', $e->getErrorId());
        }
    }

    public function test_it_allows_different_error_ids_for_different_errors()
    {
        $exception1 = new ZohoApiException('invalid_token', 'Token is invalid');
        $exception2 = new ZohoApiException('rate_limit', 'Rate limit exceeded');

        $this->assertEquals('invalid_token', $exception1->getErrorId());
        $this->assertEquals('rate_limit', $exception2->getErrorId());
    }
}
