<?php

namespace Tests\Unit\Streams\Exceptions;

use App\Streams\Exceptions\ConnectionException;
use App\Streams\Exceptions\ConsumeException;
use App\Streams\Exceptions\MessageProcessingException;
use App\Streams\Exceptions\PublishException;
use App\Streams\Exceptions\RedisStreamException;
use Exception;
use Tests\TestCase;

class RedisStreamExceptionTest extends TestCase
{
    // ─── RedisStreamException ────────────────────────────────────

    public function test_redis_stream_exception_default_message(): void
    {
        $e = new RedisStreamException;
        $this->assertEquals('Redis Stream error', $e->getMessage());
    }

    public function test_redis_stream_exception_custom_message(): void
    {
        $e = new RedisStreamException('Custom error', 42);
        $this->assertEquals('Custom error', $e->getMessage());
        $this->assertEquals(42, $e->getCode());
    }

    public function test_redis_stream_exception_with_previous(): void
    {
        $previous = new Exception('root cause');
        $e = new RedisStreamException('Wrapper', 0, $previous);
        $this->assertSame($previous, $e->getPrevious());
    }

    // ─── ConnectionException ─────────────────────────────────────

    public function test_connection_exception_default_message(): void
    {
        $e = new ConnectionException;
        $this->assertEquals('Redis connection error', $e->getMessage());
    }

    public function test_connection_exception_custom_message(): void
    {
        $e = new ConnectionException('Connection refused on port 6379');
        $this->assertEquals('Connection refused on port 6379', $e->getMessage());
    }

    public function test_connection_exception_extends_redis_stream_exception(): void
    {
        $e = new ConnectionException;
        $this->assertInstanceOf(RedisStreamException::class, $e);
    }

    // ─── PublishException ────────────────────────────────────────

    public function test_publish_exception_includes_stream_name(): void
    {
        $e = new PublishException('my-stream');
        $this->assertStringContainsString("stream 'my-stream'", $e->getMessage());
    }

    public function test_publish_exception_includes_detail_message(): void
    {
        $e = new PublishException('my-stream', 'Write timeout');
        $this->assertStringContainsString('Write timeout', $e->getMessage());
        $this->assertStringContainsString("stream 'my-stream'", $e->getMessage());
    }

    public function test_publish_exception_without_detail_message(): void
    {
        $e = new PublishException('my-stream', '');
        $this->assertEquals("Failed to publish message to stream 'my-stream'", $e->getMessage());
    }

    public function test_publish_exception_with_previous(): void
    {
        $previous = new Exception('root');
        $e = new PublishException('s', 'msg', 0, $previous);
        $this->assertSame($previous, $e->getPrevious());
    }

    public function test_publish_exception_extends_redis_stream_exception(): void
    {
        $e = new PublishException('s');
        $this->assertInstanceOf(RedisStreamException::class, $e);
    }

    // ─── ConsumeException ────────────────────────────────────────

    public function test_consume_exception_includes_stream_group_consumer(): void
    {
        $e = new ConsumeException('events', 'grp1', 'worker1');
        $msg = $e->getMessage();

        $this->assertStringContainsString("stream 'events'", $msg);
        $this->assertStringContainsString('group: grp1', $msg);
        $this->assertStringContainsString('consumer: worker1', $msg);
    }

    public function test_consume_exception_includes_detail_message(): void
    {
        $e = new ConsumeException('events', 'grp1', 'worker1', 'Timeout');
        $this->assertStringContainsString('Timeout', $e->getMessage());
    }

    public function test_consume_exception_without_detail_message(): void
    {
        $e = new ConsumeException('events', 'grp1', 'worker1', '');
        $this->assertEquals(
            "Failed to consume messages from stream 'events' (group: grp1, consumer: worker1)",
            $e->getMessage()
        );
    }

    public function test_consume_exception_extends_redis_stream_exception(): void
    {
        $e = new ConsumeException('s', 'g', 'c');
        $this->assertInstanceOf(RedisStreamException::class, $e);
    }

    // ─── MessageProcessingException ──────────────────────────────

    public function test_message_processing_exception_includes_details(): void
    {
        $e = new MessageProcessingException('events', 'msg-123', 2);
        $msg = $e->getMessage();

        $this->assertStringContainsString('msg-123', $msg);
        $this->assertStringContainsString("stream 'events'", $msg);
        $this->assertStringContainsString('attempt 2', $msg);
    }

    public function test_message_processing_exception_includes_error_detail(): void
    {
        $e = new MessageProcessingException('events', 'msg-1', 1, 'Parse error');
        $this->assertStringContainsString('Parse error', $e->getMessage());
    }

    public function test_message_processing_exception_without_detail_message(): void
    {
        $e = new MessageProcessingException('events', 'msg-1', 1, '');
        $this->assertEquals(
            "Failed to process message msg-1 from stream 'events' (attempt 1)",
            $e->getMessage()
        );
    }

    public function test_message_processing_exception_exposes_properties(): void
    {
        $e = new MessageProcessingException('my-stream', 'id-42', 3, 'err');

        $this->assertEquals('my-stream', $e->stream);
        $this->assertEquals('id-42', $e->messageId);
        $this->assertEquals(3, $e->attempt);
    }

    public function test_message_processing_exception_default_attempt(): void
    {
        $e = new MessageProcessingException('s', 'id');
        $this->assertEquals(1, $e->attempt);
    }

    public function test_message_processing_exception_extends_redis_stream_exception(): void
    {
        $e = new MessageProcessingException('s', 'id');
        $this->assertInstanceOf(RedisStreamException::class, $e);
    }
}
