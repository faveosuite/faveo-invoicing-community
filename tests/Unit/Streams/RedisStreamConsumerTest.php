<?php

namespace Tests\Unit\Streams;

use App\Streams\Adapters\RedisAdapterInterface;
use App\Streams\Exceptions\ConnectionException;
use App\Streams\RedisStreamConsumer;
use Exception;
use Mockery;
use Mockery\MockInterface;
use ReflectionClass;
use Tests\TestCase;

class RedisStreamConsumerTest extends TestCase
{
    private MockInterface $redisMock;

    private string $stream = 'test-stream';

    private string $group = 'test-group';

    private string $consumerName = 'test-consumer';

    protected function setUp(): void
    {
        parent::setUp();
        $this->redisMock = Mockery::mock(RedisAdapterInterface::class);
    }

    protected function tearDown(): void
    {
        // Reset the static shutdown flag
        $ref = new ReflectionClass(RedisStreamConsumer::class);
        $prop = $ref->getProperty('shouldShutdown');
        $prop->setValue(null, false);

        Mockery::close();
        parent::tearDown();
    }

    private function createConsumer(int $interval = 0, int $retryLimit = 3, int $batchSize = 10): RedisStreamConsumer
    {
        $reflection = new ReflectionClass(RedisStreamConsumer::class);
        $consumer = $reflection->newInstanceWithoutConstructor();

        $reflection->getProperty('stream')->setValue($consumer, $this->stream);
        $reflection->getProperty('group')->setValue($consumer, $this->group);
        $reflection->getProperty('consumer')->setValue($consumer, $this->consumerName);
        $reflection->getProperty('interval')->setValue($consumer, $interval);
        $reflection->getProperty('retryLimit')->setValue($consumer, $retryLimit);
        $reflection->getProperty('batchSize')->setValue($consumer, $batchSize);
        $reflection->getProperty('redis')->setValue($consumer, $this->redisMock);

        return $consumer;
    }

    private function triggerShutdownAfterCalls(int $callCount): void
    {
        $counter = 0;
        $this->redisMock
            ->shouldReceive('xreadgroup')
            ->andReturnUsing(function () use (&$counter, $callCount) {
                $counter++;
                if ($counter >= $callCount) {
                    $ref = new ReflectionClass(RedisStreamConsumer::class);
                    $ref->getProperty('shouldShutdown')->setValue(null, true);
                }

                return [];
            });
    }

    // ─── Constructor properties ──────────────────────────────────

    public function test_consumer_stores_properties(): void
    {
        $consumer = $this->createConsumer(interval: 2, retryLimit: 5, batchSize: 20);

        $this->assertEquals($this->stream, $consumer->stream);
        $this->assertEquals($this->group, $consumer->group);
        $this->assertEquals($this->consumerName, $consumer->consumer);
        $this->assertEquals(2, $consumer->interval);
        $this->assertEquals(5, $consumer->retryLimit);
        $this->assertEquals(20, $consumer->batchSize);
    }

    // ─── consume() ───────────────────────────────────────────────

    public function test_consume_processes_messages_and_acks(): void
    {
        $consumer = $this->createConsumer();
        $processed = [];

        $messagePayload = json_encode(['event' => 'test', 'payload' => 'data']);

        $this->redisMock->shouldReceive('xgroup')->andReturn(true);

        $callCount = 0;
        $this->redisMock
            ->shouldReceive('xreadgroup')
            ->andReturnUsing(function () use (&$callCount, $messagePayload) {
                $callCount++;
                if ($callCount === 1) {
                    return [
                        $this->stream => [
                            '100-0' => ['message' => $messagePayload],
                        ],
                    ];
                }
                // Shutdown after first batch
                $ref = new ReflectionClass(RedisStreamConsumer::class);
                $ref->getProperty('shouldShutdown')->setValue(null, true);

                return [];
            });

        $this->redisMock
            ->shouldReceive('xack')
            ->once()
            ->with($this->stream, $this->group, ['100-0'])
            ->andReturn(1);

        $consumer->consume(function ($data, $id) use (&$processed) {
            $processed[] = ['data' => $data, 'id' => $id];
        }, false);

        $this->assertCount(1, $processed);
        $this->assertEquals('test', $processed[0]['data']['event']);
        $this->assertEquals('100-0', $processed[0]['id']);
    }

    public function test_consume_handles_empty_responses(): void
    {
        $consumer = $this->createConsumer();

        $this->redisMock->shouldReceive('xgroup')->andReturn(true);
        $this->triggerShutdownAfterCalls(1);

        $callbackCalled = false;
        $consumer->consume(function () use (&$callbackCalled) {
            $callbackCalled = true;
        }, false);

        $this->assertFalse($callbackCalled);
    }

    public function test_consume_recreates_group_on_nogroup_error(): void
    {
        $consumer = $this->createConsumer();

        // First xgroup call for initial setup
        $this->redisMock->shouldReceive('xgroup')->andReturn(true);

        $callCount = 0;
        $this->redisMock
            ->shouldReceive('xreadgroup')
            ->andReturnUsing(function () use (&$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    throw new Exception('NOGROUP No such consumer group');
                }
                $ref = new ReflectionClass(RedisStreamConsumer::class);
                $ref->getProperty('shouldShutdown')->setValue(null, true);

                return [];
            });

        $consumer->consume(function () {}, false);

        // If we got here without an exception, the NOGROUP error was handled
        $this->assertTrue(true);
    }

    public function test_consume_recreates_group_on_no_such_key_error(): void
    {
        $consumer = $this->createConsumer();

        $this->redisMock->shouldReceive('xgroup')->andReturn(true);

        $callCount = 0;
        $this->redisMock
            ->shouldReceive('xreadgroup')
            ->andReturnUsing(function () use (&$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    throw new Exception('ERR no such key');
                }
                $ref = new ReflectionClass(RedisStreamConsumer::class);
                $ref->getProperty('shouldShutdown')->setValue(null, true);

                return [];
            });

        $consumer->consume(function () {}, false);

        $this->assertTrue(true);
    }

    public function test_consume_rethrows_non_group_exceptions(): void
    {
        $consumer = $this->createConsumer();

        $this->redisMock->shouldReceive('xgroup')->andReturn(true);

        $this->redisMock
            ->shouldReceive('xreadgroup')
            ->once()
            ->andThrow(new Exception('Something unexpected'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Something unexpected');

        $consumer->consume(function () {}, false);
    }

    public function test_consume_swallows_message_processing_errors(): void
    {
        $consumer = $this->createConsumer();
        $messagePayload = json_encode(['event' => 'test', 'payload' => 'data']);

        $this->redisMock->shouldReceive('xgroup')->andReturn(true);

        // Return pending info for error case
        $this->redisMock->shouldReceive('xpending')
            ->andReturn([['100-0', $this->consumerName, 1000, 1]]);

        $callCount = 0;
        $this->redisMock
            ->shouldReceive('xreadgroup')
            ->andReturnUsing(function () use (&$callCount, $messagePayload) {
                $callCount++;
                if ($callCount === 1) {
                    return [
                        $this->stream => [
                            '100-0' => ['message' => $messagePayload],
                        ],
                    ];
                }
                $ref = new ReflectionClass(RedisStreamConsumer::class);
                $ref->getProperty('shouldShutdown')->setValue(null, true);

                return [];
            });

        // Callback throws - processMessageSafely should swallow it
        $consumer->consume(function () {
            throw new Exception('Callback error');
        }, false);

        // Should reach here without exception
        $this->assertTrue(true);
    }

    public function test_consume_skips_invalid_json_messages(): void
    {
        $consumer = $this->createConsumer();

        $this->redisMock->shouldReceive('xgroup')->andReturn(true);

        $callCount = 0;
        $this->redisMock
            ->shouldReceive('xreadgroup')
            ->andReturnUsing(function () use (&$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    return [
                        $this->stream => [
                            '100-0' => ['message' => 'not-valid-json{{{'],
                        ],
                    ];
                }
                $ref = new ReflectionClass(RedisStreamConsumer::class);
                $ref->getProperty('shouldShutdown')->setValue(null, true);

                return [];
            });

        $callbackCalled = false;
        $consumer->consume(function () use (&$callbackCalled) {
            $callbackCalled = true;
        }, false);

        // Callback should not have been called for invalid JSON
        $this->assertFalse($callbackCalled);
    }

    // ─── checkPendingMessages() ──────────────────────────────────

    public function test_pending_messages_acked_when_retry_limit_exceeded(): void
    {
        $consumer = $this->createConsumer(retryLimit: 3);

        $this->redisMock->shouldReceive('xgroup')->andReturn(true);

        // Set up so pending check runs on first iteration (pollCount % 10 === 0 won't happen on 1st call)
        // We need to use reflection to test checkPendingMessages directly
        $reflection = new ReflectionClass(RedisStreamConsumer::class);
        $method = $reflection->getMethod('checkPendingMessages');
        $method->setAccessible(true);

        // Pending message with 5 attempts (> retryLimit of 3)
        $this->redisMock
            ->shouldReceive('xpending')
            ->once()
            ->andReturn([
                ['msg-1', $this->consumerName, 60000, 5],
            ]);

        // Should ack the message since attempts >= retryLimit
        $this->redisMock
            ->shouldReceive('xack')
            ->once()
            ->with($this->stream, $this->group, ['msg-1'])
            ->andReturn(1);

        $method->invoke($consumer, function () {});

        // Mockery expectations verify xack was called (message was acknowledged and dropped)
        $this->assertTrue(true);
    }

    public function test_pending_messages_claimed_and_reprocessed(): void
    {
        $consumer = $this->createConsumer(retryLimit: 3);
        $messagePayload = json_encode(['event' => 'retry', 'payload' => 'data']);

        $reflection = new ReflectionClass(RedisStreamConsumer::class);
        $method = $reflection->getMethod('checkPendingMessages');
        $method->setAccessible(true);

        // Pending message with 1 attempt (< retryLimit)
        $this->redisMock
            ->shouldReceive('xpending')
            ->once()
            ->andReturn([
                ['msg-2', $this->consumerName, 60000, 1],
            ]);

        // Should claim the message
        $this->redisMock
            ->shouldReceive('xclaim')
            ->once()
            ->with($this->stream, $this->group, $this->consumerName, 60000, ['msg-2'])
            ->andReturn(['msg-2' => ['message' => $messagePayload]]);

        // Should ack after successful processing
        $this->redisMock
            ->shouldReceive('xack')
            ->once()
            ->with($this->stream, $this->group, ['msg-2'])
            ->andReturn(1);

        $processed = [];
        $method->invoke($consumer, function ($data, $id) use (&$processed) {
            $processed[] = $id;
        });

        $this->assertEquals(['msg-2'], $processed);
    }

    public function test_pending_check_rethrows_connection_exception(): void
    {
        $consumer = $this->createConsumer();

        $reflection = new ReflectionClass(RedisStreamConsumer::class);
        $method = $reflection->getMethod('checkPendingMessages');
        $method->setAccessible(true);

        $this->redisMock
            ->shouldReceive('xpending')
            ->once()
            ->andThrow(new ConnectionException('Connection lost'));

        $this->expectException(ConnectionException::class);

        $method->invoke($consumer, function () {});
    }

    public function test_pending_check_swallows_general_exceptions(): void
    {
        $consumer = $this->createConsumer();

        $reflection = new ReflectionClass(RedisStreamConsumer::class);
        $method = $reflection->getMethod('checkPendingMessages');
        $method->setAccessible(true);

        $this->redisMock
            ->shouldReceive('xpending')
            ->once()
            ->andThrow(new Exception('Some error'));

        // Should not throw
        $method->invoke($consumer, function () {});
        $this->assertTrue(true);
    }

    // ─── ensureConsumerGroup() ───────────────────────────────────

    public function test_ensure_consumer_group_creates_group(): void
    {
        $consumer = $this->createConsumer();

        $reflection = new ReflectionClass(RedisStreamConsumer::class);
        $method = $reflection->getMethod('ensureConsumerGroup');
        $method->setAccessible(true);

        $this->redisMock
            ->shouldReceive('xgroup')
            ->once()
            ->with('CREATE', $this->stream, $this->group, '0', true)
            ->andReturn(true);

        $method->invoke($consumer);
        $this->assertTrue(true);
    }

    public function test_ensure_consumer_group_ignores_already_exists_error(): void
    {
        $consumer = $this->createConsumer();

        $reflection = new ReflectionClass(RedisStreamConsumer::class);
        $method = $reflection->getMethod('ensureConsumerGroup');
        $method->setAccessible(true);

        $this->redisMock
            ->shouldReceive('xgroup')
            ->once()
            ->andThrow(new Exception('BUSYGROUP Consumer Group name already exists'));

        // Should not throw
        $method->invoke($consumer);
        $this->assertTrue(true);
    }

    // ─── getMessagePendingInfo() ─────────────────────────────────

    public function test_get_message_pending_info_returns_pending_data(): void
    {
        $consumer = $this->createConsumer();

        $reflection = new ReflectionClass(RedisStreamConsumer::class);
        $method = $reflection->getMethod('getMessagePendingInfo');
        $method->setAccessible(true);

        $this->redisMock
            ->shouldReceive('xpending')
            ->once()
            ->with($this->stream, $this->group, '100-0', '100-0', 1)
            ->andReturn([['100-0', $this->consumerName, 5000, 2]]);

        $result = $method->invoke($consumer, '100-0');

        $this->assertEquals(['100-0', $this->consumerName, 5000, 2], $result);
    }

    public function test_get_message_pending_info_returns_null_on_exception(): void
    {
        $consumer = $this->createConsumer();

        $reflection = new ReflectionClass(RedisStreamConsumer::class);
        $method = $reflection->getMethod('getMessagePendingInfo');
        $method->setAccessible(true);

        $this->redisMock
            ->shouldReceive('xpending')
            ->once()
            ->andThrow(new Exception('Error'));

        $result = $method->invoke($consumer, '100-0');

        $this->assertNull($result);
    }

    public function test_get_message_pending_info_returns_null_on_empty_result(): void
    {
        $consumer = $this->createConsumer();

        $reflection = new ReflectionClass(RedisStreamConsumer::class);
        $method = $reflection->getMethod('getMessagePendingInfo');
        $method->setAccessible(true);

        $this->redisMock
            ->shouldReceive('xpending')
            ->once()
            ->andReturn([]);

        $result = $method->invoke($consumer, '100-0');

        $this->assertNull($result);
    }
}
