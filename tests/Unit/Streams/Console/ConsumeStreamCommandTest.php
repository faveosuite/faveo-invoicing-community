<?php

namespace Tests\Unit\Streams\Console;

use App\Streams\Console\ConsumeStreamCommand;
use App\Streams\Exceptions\MessageProcessingException;
use Exception;
use Illuminate\Console\OutputStyle;
use ReflectionClass;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class ConsumeStreamCommandTest extends TestCase
{
    private function createCommandWithOutput(ConsumeStreamCommand $command, BufferedOutput $buffer): void
    {
        $outputStyle = new OutputStyle(new ArrayInput([]), $buffer);
        $command->setOutput($outputStyle);
    }

    // ─── Command validation ──────────────────────────────────────

    public function test_command_fails_with_unknown_stream_and_no_handler(): void
    {
        $this->artisan('redis-stream:consume', [
            '--stream' => 'unknown_stream',
            '--handler' => '',
        ])->assertExitCode(1);
    }

    public function test_command_fails_with_nonexistent_handler_class(): void
    {
        $this->artisan('redis-stream:consume', [
            '--handler' => 'App\\NonExistent\\HandlerClass',
        ])->assertExitCode(1);
    }

    public function test_command_fails_when_handler_has_no_handle_method(): void
    {
        $this->artisan('redis-stream:consume', [
            '--handler' => \stdClass::class,
        ])->assertExitCode(1);
    }

    // ─── processMessage() ────────────────────────────────────────

    public function test_process_message_calls_handler(): void
    {
        $command = new ConsumeStreamCommand;
        $reflection = new ReflectionClass($command);
        $method = $reflection->getMethod('processMessage');
        $method->setAccessible(true);

        $handler = new class
        {
            public array $received = [];

            public function handle(array $data, string $messageId): void
            {
                $this->received[] = ['data' => $data, 'id' => $messageId];
            }
        };

        $buffer = new BufferedOutput;
        $this->createCommandWithOutput($command, $buffer);

        $method->invoke($command, ['event' => 'test.event', 'payload' => 'data'], 'msg-1', $handler);

        $this->assertCount(1, $handler->received);
        $this->assertEquals('test.event', $handler->received[0]['data']['event']);
        $this->assertEquals('msg-1', $handler->received[0]['id']);
    }

    public function test_process_message_wraps_handler_exception_in_message_processing_exception(): void
    {
        $command = new ConsumeStreamCommand;
        $reflection = new ReflectionClass($command);
        $method = $reflection->getMethod('processMessage');
        $method->setAccessible(true);

        $handler = new class
        {
            public function handle(array $data, string $messageId): void
            {
                throw new Exception('Handler failed');
            }
        };

        $buffer = new BufferedOutput;
        $this->createCommandWithOutput($command, $buffer);

        $this->expectException(MessageProcessingException::class);
        $this->expectExceptionMessage('Handler error: Handler failed');

        $method->invoke($command, ['event' => 'test', 'stream' => 'my-stream'], 'msg-1', $handler);
    }

    public function test_process_message_outputs_json_when_no_handler(): void
    {
        $command = new ConsumeStreamCommand;
        $reflection = new ReflectionClass($command);
        $method = $reflection->getMethod('processMessage');
        $method->setAccessible(true);

        $buffer = new BufferedOutput;
        $this->createCommandWithOutput($command, $buffer);

        $data = ['event' => 'debug.event', 'payload' => 'test'];
        $method->invoke($command, $data, 'msg-1', null);

        $outputContent = $buffer->fetch();
        $this->assertStringContainsString('debug.event', $outputContent);
        $this->assertStringContainsString('msg-1', $outputContent);
    }

    public function test_process_message_extracts_event_name_for_output(): void
    {
        $command = new ConsumeStreamCommand;
        $reflection = new ReflectionClass($command);
        $method = $reflection->getMethod('processMessage');
        $method->setAccessible(true);

        $buffer = new BufferedOutput;
        $this->createCommandWithOutput($command, $buffer);

        $handler = new class
        {
            public function handle(array $data, string $messageId): void {}
        };

        $method->invoke($command, ['event' => 'order.placed'], 'msg-5', $handler);

        $outputContent = $buffer->fetch();
        $this->assertStringContainsString('order.placed', $outputContent);
        $this->assertStringContainsString('msg-5', $outputContent);
    }

    public function test_process_message_uses_unknown_event_when_event_key_missing(): void
    {
        $command = new ConsumeStreamCommand;
        $reflection = new ReflectionClass($command);
        $method = $reflection->getMethod('processMessage');
        $method->setAccessible(true);

        $buffer = new BufferedOutput;
        $this->createCommandWithOutput($command, $buffer);

        $handler = new class
        {
            public function handle(array $data, string $messageId): void {}
        };

        $method->invoke($command, ['payload' => 'data'], 'msg-1', $handler);

        $outputContent = $buffer->fetch();
        $this->assertStringContainsString('unknown', $outputContent);
    }

    // ─── Command defaults ────────────────────────────────────────

    public function test_default_stream_handlers_includes_license_request(): void
    {
        $command = new ConsumeStreamCommand;
        $reflection = new ReflectionClass($command);
        $prop = $reflection->getProperty('streamHandlers');
        $prop->setAccessible(true);

        $handlers = $prop->getValue($command);

        $this->assertArrayHasKey('license_request', $handlers);
        $this->assertEquals(\App\Streams\License\LicenseStreamHandler::class, $handlers['license_request']);
    }
}
