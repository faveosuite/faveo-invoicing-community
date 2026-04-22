<?php

namespace App\License\tests\Backend\Controllers\AflCallbacks;

use App\License\Controllers\AflCallbacks\ConnectionTestController;
use App\License\Helpers\LicenseValidator;
use App\License\tests\Backend\LicenseTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ConnectionTestControllerTest extends LicenseTestCase
{
    #[Test]
    #[Group('license-callbacks')]
    public function connection_returns_ok_when_validator_accepts_request(): void
    {
        $validator = Mockery::mock(LicenseValidator::class);
        $validator->shouldReceive('isValidConnection')->once()->with(1, 'hash')->andReturn(true);

        $response = (new ConnectionTestController($validator))->connection($this->moduleRequest([
            'product_id' => 1,
            'connection_hash' => 'hash',
        ], 'POST'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('<connection_test>OK</connection_test>', $response->getContent());
        $this->assertSame('text/plain', $response->headers->get('Content-Type'));
    }

    #[Test]
    #[Group('license-callbacks')]
    public function connection_returns_failed_when_validator_rejects_request(): void
    {
        $validator = Mockery::mock(LicenseValidator::class);
        $validator->shouldReceive('isValidConnection')->once()->with(1, 'bad')->andReturn(false);

        $response = (new ConnectionTestController($validator))->connection($this->moduleRequest([
            'product_id' => 1,
            'connection_hash' => 'bad',
        ], 'POST'));

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('<connection_test>Failed</connection_test>', $response->getContent());
    }
}
