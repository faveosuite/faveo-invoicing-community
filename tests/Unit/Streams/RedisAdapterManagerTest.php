<?php

namespace Tests\Unit\Streams;

use App\Streams\Adapters\PhpRedisAdapter;
use App\Streams\Adapters\PredisAdapter;
use App\Streams\Exceptions\ConnectionException;
use App\Streams\RedisAdapterManager;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Tests\TestCase;

class RedisAdapterManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RedisAdapterManager::reset();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function mockRedisDriverClass(string $className): void
    {
        // Create an anonymous class whose name won't match PhpRedis or Predis
        // For matching, we need the class name to contain the expected string
        $connectionMock = Mockery::mock();

        if ($className === 'Redis') {
            // PhpRedis extension returns a \Redis instance
            $client = Mockery::mock();
            // Override get_class behavior by using a named mock
            $connectionMock->shouldReceive('client')->andReturn(new class {
                // get_class on this returns an anonymous class name, not 'Redis'
            });
        } else {
            $connectionMock->shouldReceive('client')->andReturnUsing(function () use ($className) {
                return new class($className)
                {
                    public function __construct(public string $name)
                    {
                    }
                };
            });
        }

        Redis::shouldReceive('connection')
            ->with('streams')
            ->andReturn($connectionMock);
    }

    public function test_create_returns_php_redis_adapter_for_phpredis_driver(): void
    {
        // Simulate a client whose class name contains 'PhpRedis'
        $connectionMock = Mockery::mock();
        $connectionMock->shouldReceive('client')->andReturn(
            Mockery::namedMock('App_PhpRedis_Client', \stdClass::class)
        );

        Redis::shouldReceive('connection')
            ->with('streams')
            ->andReturn($connectionMock);

        $adapter = RedisAdapterManager::create();

        $this->assertInstanceOf(PhpRedisAdapter::class, $adapter);
    }

    public function test_create_returns_predis_adapter_for_predis_driver(): void
    {
        $connectionMock = Mockery::mock();
        $connectionMock->shouldReceive('client')->andReturn(
            Mockery::namedMock('App_Predis_Client', \stdClass::class)
        );

        Redis::shouldReceive('connection')
            ->with('streams')
            ->andReturn($connectionMock);

        $adapter = RedisAdapterManager::create();

        $this->assertInstanceOf(PredisAdapter::class, $adapter);
    }

    public function test_create_throws_connection_exception_for_unsupported_driver(): void
    {
        $connectionMock = Mockery::mock();
        $connectionMock->shouldReceive('client')->andReturn(new \stdClass);

        Redis::shouldReceive('connection')
            ->with('streams')
            ->andReturn($connectionMock);

        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('Unsupported Redis driver');

        RedisAdapterManager::create();
    }
}
