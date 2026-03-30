<?php

namespace Tests\Unit\Streams;

use App\Streams\Adapters\RedisAdapterInterface;
use App\Streams\Exceptions\ConnectionException;
use App\Streams\Exceptions\PublishException;
use App\Streams\RedisStreamProducer;
use Exception;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use JsonException;
use Mockery;
use Mockery\MockInterface;
use ReflectionClass;
use Tests\TestCase;

class RedisStreamProducerTest extends TestCase
{
    private MockInterface $redisMock;

    private string $streamName = 'test-stream';

    protected function setUp(): void
    {
        parent::setUp();
        $this->redisMock = Mockery::mock(RedisAdapterInterface::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Create a RedisStreamProducer instance with a mocked Redis adapter
     * bypassing the constructor's RedisAdapterManager::create() call.
     */
    private function createProducer(?int $maxLen = null, bool $useExactMaxLen = false): RedisStreamProducer
    {
        $reflection = new ReflectionClass(RedisStreamProducer::class);
        $producer = $reflection->newInstanceWithoutConstructor();

        $streamProp = $reflection->getProperty('stream');
        $streamProp->setValue($producer, $this->streamName);

        $maxLenProp = $reflection->getProperty('maxLen');
        $maxLenProp->setValue($producer, $maxLen);

        $exactProp = $reflection->getProperty('useExactMaxLen');
        $exactProp->setValue($producer, $useExactMaxLen);

        $redisProp = $reflection->getProperty('redis');
        $redisProp->setValue($producer, $this->redisMock);

        return $producer;
    }

    // ─── publish() ───────────────────────────────────────────────

    public function test_publish_with_string_payload(): void
    {
        Carbon::setTestNow('2026-01-15 10:00:00');

        $this->redisMock
            ->shouldReceive('xadd')
            ->once()
            ->withArgs(function ($stream, $id, $params, $options) {
                $decoded = json_decode($params['message'], true);

                return $stream === $this->streamName
                    && $id === '*'
                    && $decoded['event'] === 'user.created'
                    && $decoded['payload'] === 'test-data'
                    && $decoded['timestamp'] === '2026-01-15 10:00:00'
                    && $options === [];
            })
            ->andReturn('1234567890-0');

        $producer = $this->createProducer();
        $result = $producer->publish('user.created', 'test-data');

        $this->assertEquals('1234567890-0', $result);
    }

    public function test_publish_with_array_payload(): void
    {
        Carbon::setTestNow('2026-01-15 10:00:00');

        $payload = ['name' => 'John', 'email' => 'john@example.com'];

        $this->redisMock
            ->shouldReceive('xadd')
            ->once()
            ->withArgs(function ($stream, $id, $params, $options) use ($payload) {
                $decoded = json_decode($params['message'], true);

                return $decoded['event'] === 'user.created'
                    && $decoded['payload'] === $payload;
            })
            ->andReturn('1234567890-0');

        $producer = $this->createProducer();
        $result = $producer->publish('user.created', $payload);

        $this->assertEquals('1234567890-0', $result);
    }

    public function test_publish_with_additional_options(): void
    {
        Carbon::setTestNow('2026-01-15 10:00:00');

        $options = ['priority' => 'high', 'source' => 'api'];

        $this->redisMock
            ->shouldReceive('xadd')
            ->once()
            ->withArgs(function ($stream, $id, $params, $streamOptions) {
                $decoded = json_decode($params['message'], true);

                return $decoded['priority'] === 'high'
                    && $decoded['source'] === 'api'
                    && $decoded['event'] === 'order.placed';
            })
            ->andReturn('1234567890-0');

        $producer = $this->createProducer();
        $result = $producer->publish('order.placed', 'data', $options);

        $this->assertEquals('1234567890-0', $result);
    }

    public function test_publish_with_approximate_maxlen(): void
    {
        $this->redisMock
            ->shouldReceive('xadd')
            ->once()
            ->withArgs(function ($stream, $id, $params, $options) {
                return isset($options['MAXLEN'])
                    && $options['MAXLEN'][0] === '~'
                    && $options['MAXLEN'][1] === 1000;
            })
            ->andReturn('1234567890-0');

        $producer = $this->createProducer(maxLen: 1000);
        $result = $producer->publish('test.event', 'data');

        $this->assertEquals('1234567890-0', $result);
    }

    public function test_publish_with_exact_maxlen(): void
    {
        $this->redisMock
            ->shouldReceive('xadd')
            ->once()
            ->withArgs(function ($stream, $id, $params, $options) {
                return isset($options['MAXLEN'])
                    && $options['MAXLEN'][0] === ''
                    && $options['MAXLEN'][1] === 500;
            })
            ->andReturn('1234567890-0');

        $producer = $this->createProducer(maxLen: 500, useExactMaxLen: true);
        $result = $producer->publish('test.event', 'data');

        $this->assertEquals('1234567890-0', $result);
    }

    public function test_publish_without_maxlen_sends_empty_stream_options(): void
    {
        $this->redisMock
            ->shouldReceive('xadd')
            ->once()
            ->withArgs(function ($stream, $id, $params, $options) {
                return $options === [];
            })
            ->andReturn('1234567890-0');

        $producer = $this->createProducer(maxLen: null);
        $result = $producer->publish('test.event', 'data');

        $this->assertEquals('1234567890-0', $result);
    }

    public function test_publish_throws_connection_exception_on_connection_error(): void
    {
        $this->redisMock
            ->shouldReceive('xadd')
            ->once()
            ->andThrow(new Exception('Redis connection refused'));

        $producer = $this->createProducer();

        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('Connection to Redis failed');

        $producer->publish('test.event', 'data');
    }

    public function test_publish_throws_publish_exception_on_general_error(): void
    {
        $this->redisMock
            ->shouldReceive('xadd')
            ->once()
            ->andThrow(new Exception('Some Redis error'));

        $producer = $this->createProducer();

        $this->expectException(PublishException::class);
        $this->expectExceptionMessage("Failed to publish message to stream '{$this->streamName}'");

        $producer->publish('test.event', 'data');
    }

    public function test_publish_throws_json_exception_on_encode_failure(): void
    {
        $producer = $this->createProducer();

        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('Failed to encode message for Redis Stream');

        // NAN cannot be JSON-encoded
        $producer->publish('test.event', ['value' => NAN]);
    }

    // ─── publishBatch() ──────────────────────────────────────────

    public function test_publish_batch_returns_empty_array_for_empty_input(): void
    {
        $producer = $this->createProducer();
        $result = $producer->publishBatch([]);

        $this->assertEquals([], $result);
    }

    public function test_publish_batch_publishes_multiple_messages(): void
    {
        Carbon::setTestNow('2026-01-15 10:00:00');

        $messages = [
            ['event' => 'user.created', 'payload' => ['id' => 1]],
            ['event' => 'user.updated', 'payload' => ['id' => 2]],
            ['event' => 'user.deleted', 'payload' => ['id' => 3]],
        ];

        $this->redisMock
            ->shouldReceive('xadd')
            ->times(3)
            ->andReturn('100-0', '100-1', '100-2');

        $producer = $this->createProducer();
        $ids = $producer->publishBatch($messages);

        $this->assertCount(3, $ids);
        $this->assertEquals(['100-0', '100-1', '100-2'], $ids);
    }

    public function test_publish_batch_uses_custom_timestamp_when_provided(): void
    {
        $messages = [
            [
                'event' => 'test.event',
                'payload' => 'data',
                'timestamp' => '2025-06-01 12:00:00',
            ],
        ];

        $this->redisMock
            ->shouldReceive('xadd')
            ->once()
            ->withArgs(function ($stream, $id, $params, $options) {
                $decoded = json_decode($params['message'], true);

                return $decoded['timestamp'] === '2025-06-01 12:00:00';
            })
            ->andReturn('100-0');

        $producer = $this->createProducer();
        $ids = $producer->publishBatch($messages);

        $this->assertEquals(['100-0'], $ids);
    }

    public function test_publish_batch_merges_per_message_options(): void
    {
        $messages = [
            [
                'event' => 'test.event',
                'payload' => 'data',
                'options' => ['priority' => 'high'],
            ],
        ];

        $this->redisMock
            ->shouldReceive('xadd')
            ->once()
            ->withArgs(function ($stream, $id, $params, $options) {
                $decoded = json_decode($params['message'], true);

                return $decoded['priority'] === 'high';
            })
            ->andReturn('100-0');

        $producer = $this->createProducer();
        $ids = $producer->publishBatch($messages);

        $this->assertEquals(['100-0'], $ids);
    }

    public function test_publish_batch_with_maxlen(): void
    {
        $messages = [
            ['event' => 'test.event', 'payload' => 'data'],
        ];

        $this->redisMock
            ->shouldReceive('xadd')
            ->once()
            ->withArgs(function ($stream, $id, $params, $options) {
                return isset($options['MAXLEN'])
                    && $options['MAXLEN'][0] === '~'
                    && $options['MAXLEN'][1] === 2000;
            })
            ->andReturn('100-0');

        $producer = $this->createProducer(maxLen: 2000);
        $ids = $producer->publishBatch($messages);

        $this->assertEquals(['100-0'], $ids);
    }

    public function test_publish_batch_throws_exception_for_missing_event(): void
    {
        $messages = [
            ['payload' => 'data'],
        ];

        $producer = $this->createProducer();

        $this->expectException(PublishException::class);
        $this->expectExceptionMessage('Each message must have event and payload keys');

        $producer->publishBatch($messages);
    }

    public function test_publish_batch_throws_exception_for_missing_payload(): void
    {
        $messages = [
            ['event' => 'test.event'],
        ];

        $producer = $this->createProducer();

        $this->expectException(PublishException::class);
        $this->expectExceptionMessage('Each message must have event and payload keys');

        $producer->publishBatch($messages);
    }

    public function test_publish_batch_throws_connection_exception_on_connection_error(): void
    {
        $messages = [
            ['event' => 'test.event', 'payload' => 'data'],
        ];

        $this->redisMock
            ->shouldReceive('xadd')
            ->once()
            ->andThrow(new Exception('Redis connection timeout'));

        $producer = $this->createProducer();

        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('Connection to Redis failed during batch publish');

        $producer->publishBatch($messages);
    }

    public function test_publish_batch_throws_publish_exception_on_general_error(): void
    {
        $messages = [
            ['event' => 'test.event', 'payload' => 'data'],
        ];

        $this->redisMock
            ->shouldReceive('xadd')
            ->once()
            ->andThrow(new Exception('Unexpected Redis error'));

        $producer = $this->createProducer();

        $this->expectException(PublishException::class);
        $this->expectExceptionMessage('Error publishing batch');

        $producer->publishBatch($messages);
    }

    public function test_publish_batch_throws_json_exception_on_encode_failure(): void
    {
        $messages = [
            ['event' => 'test.event', 'payload' => ['value' => NAN]],
        ];

        $producer = $this->createProducer();

        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('Failed to encode messages for Redis Stream batch');

        $producer->publishBatch($messages);
    }

    // ─── trim() ──────────────────────────────────────────────────

    public function test_trim_with_approximate_mode(): void
    {
        $this->redisMock
            ->shouldReceive('xtrim')
            ->once()
            ->with($this->streamName, '~', 1000)
            ->andReturn(50);

        $producer = $this->createProducer();
        $result = $producer->trim(1000);

        $this->assertEquals(50, $result);
    }

    public function test_trim_with_exact_mode(): void
    {
        $this->redisMock
            ->shouldReceive('xtrim')
            ->once()
            ->with($this->streamName, '', 500)
            ->andReturn(25);

        $producer = $this->createProducer();
        $result = $producer->trim(500, exact: true);

        $this->assertEquals(25, $result);
    }

    public function test_trim_throws_invalid_argument_for_zero_maxlen(): void
    {
        $producer = $this->createProducer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The maximum length must be greater than zero');

        $producer->trim(0);
    }

    public function test_trim_throws_invalid_argument_for_negative_maxlen(): void
    {
        $producer = $this->createProducer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The maximum length must be greater than zero');

        $producer->trim(-10);
    }

    public function test_trim_throws_connection_exception_on_connection_error(): void
    {
        $this->redisMock
            ->shouldReceive('xtrim')
            ->once()
            ->andThrow(new Exception('Lost connection to Redis'));

        $producer = $this->createProducer();

        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('Connection to Redis failed during stream trimming');

        $producer->trim(100);
    }

    public function test_trim_throws_publish_exception_on_general_error(): void
    {
        $this->redisMock
            ->shouldReceive('xtrim')
            ->once()
            ->andThrow(new Exception('Stream does not exist'));

        $producer = $this->createProducer();

        $this->expectException(PublishException::class);
        $this->expectExceptionMessage('Error trimming stream');

        $producer->trim(100);
    }

    // ─── Constructor properties ──────────────────────────────────

    public function test_producer_stores_stream_name(): void
    {
        $producer = $this->createProducer();
        $this->assertEquals($this->streamName, $producer->stream);
    }

    public function test_producer_stores_maxlen(): void
    {
        $producer = $this->createProducer(maxLen: 5000);
        $this->assertEquals(5000, $producer->maxLen);
    }

    public function test_producer_stores_use_exact_maxlen(): void
    {
        $producer = $this->createProducer(useExactMaxLen: true);
        $this->assertTrue($producer->useExactMaxLen);
    }

    public function test_producer_defaults_maxlen_to_null(): void
    {
        $producer = $this->createProducer();
        $this->assertNull($producer->maxLen);
    }

    public function test_producer_defaults_use_exact_maxlen_to_false(): void
    {
        $producer = $this->createProducer();
        $this->assertFalse($producer->useExactMaxLen);
    }
}