<?php

namespace App\Plugins\Zoho\Tests\Controllers\Exceptions;

use App\Plugins\Zoho\Controllers\Exceptions\ZohoAccountsApiException;
use App\Plugins\Zoho\Controllers\Exceptions\ZohoApiException;
use Tests\DBTestCase;

class ZohoAccountsApiExceptionTest extends DBTestCase
{
    
    public function test_it_extends_zoho_api_exception()
    {
        $exception = ZohoAccountsApiException::invalidClient();

        $this->assertInstanceOf(ZohoApiException::class, $exception);
        $this->assertInstanceOf(ZohoAccountsApiException::class, $exception);
    }

    
    public function test_it_creates_invalid_client_exception()
    {
        $exception = ZohoAccountsApiException::invalidClient();

        $this->assertEquals('invalid_client', $exception->getErrorId());
        $this->assertStringContainsString('client_id', $exception->getMessage());
        $this->assertStringContainsString('datacenter location', $exception->getMessage());
    }

    
    public function test_it_creates_invalid_client_secret_exception()
    {
        $exception = ZohoAccountsApiException::invalidClientSecret();

        $this->assertEquals('invalid_client_secret', $exception->getErrorId());
        $this->assertStringContainsString('client_secret', $exception->getMessage());
        $this->assertStringContainsString('missing or invalid', $exception->getMessage());
    }

    
    public function test_it_creates_invalid_code_exception()
    {
        $exception = ZohoAccountsApiException::invalidCode();

        $this->assertEquals('invalid_code', $exception->getErrorId());
        $this->assertStringContainsString('authorization code', $exception->getMessage());
        $this->assertStringContainsString('invalid or expired', $exception->getMessage());
    }

    
    public function test_invalid_client_exception_can_be_thrown()
    {
        $this->expectException(ZohoAccountsApiException::class);
        $this->expectExceptionMessage('client_id');

        throw ZohoAccountsApiException::invalidClient();
    }

    
    public function test_invalid_client_secret_exception_can_be_thrown()
    {
        $this->expectException(ZohoAccountsApiException::class);
        $this->expectExceptionMessage('client_secret');

        throw ZohoAccountsApiException::invalidClientSecret();
    }

    
    public function test_invalid_code_exception_can_be_thrown()
    {
        $this->expectException(ZohoAccountsApiException::class);
        $this->expectExceptionMessage('authorization code');

        throw ZohoAccountsApiException::invalidCode();
    }

    
    public function test_it_can_be_caught_as_zoho_api_exception()
    {
        try {
            throw ZohoAccountsApiException::invalidClient();
        } catch (ZohoApiException $e) {
            $this->assertInstanceOf(ZohoAccountsApiException::class, $e);
            $this->assertEquals('invalid_client', $e->getErrorId());
        }
    }

    
    public function test_it_can_be_caught_as_generic_exception()
    {
        try {
            throw ZohoAccountsApiException::invalidClientSecret();
        } catch (\Exception $e) {
            $this->assertInstanceOf(ZohoAccountsApiException::class, $e);
            $this->assertEquals('invalid_client_secret', $e->getErrorId());
        }
    }

    
    public function test_each_static_method_returns_unique_error_id()
    {
        $client = ZohoAccountsApiException::invalidClient();
        $secret = ZohoAccountsApiException::invalidClientSecret();
        $code = ZohoAccountsApiException::invalidCode();

        $this->assertNotEquals($client->getErrorId(), $secret->getErrorId());
        $this->assertNotEquals($client->getErrorId(), $code->getErrorId());
        $this->assertNotEquals($secret->getErrorId(), $code->getErrorId());
    }

    
    public function test_it_provides_helpful_error_messages()
    {
        $client = ZohoAccountsApiException::invalidClient();
        $secret = ZohoAccountsApiException::invalidClientSecret();
        $code = ZohoAccountsApiException::invalidCode();

        $this->assertNotEmpty($client->getMessage());
        $this->assertNotEmpty($secret->getMessage());
        $this->assertNotEmpty($code->getMessage());
    }
}
