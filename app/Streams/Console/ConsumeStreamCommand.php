<?php

namespace App\Streams\Console;

use App\Streams\Exceptions\ConnectionException;
use App\Streams\Exceptions\ConsumeException;
use App\Streams\Exceptions\MessageProcessingException;
use App\Streams\License\LicenseStreamHandler;
use App\Streams\RedisStreamConsumer;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ConsumeStreamCommand extends Command
{
    /**
     * Map of stream names to their default handler classes.
     */
    protected array $streamHandlers = [
        'license_request' => LicenseStreamHandler::class,
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis-stream:consume
                           {--stream=license_request : The stream to consume}
                           {--group=streaming_group : The consumer group name}
                           {--consumer=consumer_1 : The consumer name}
                           {--handler= : The event handler class}
                           {--interval=1 : Polling interval in seconds}
                           {--batch=10 : Batch size to read at once}
                           {--retries=3 : Number of retries for failed messages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start consuming messages from a Redis stream';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $stream = $this->option('stream');
        $group = $this->option('group');
        $consumer = $this->option('consumer');
        $interval = (int) $this->option('interval');
        $batchSize = (int) $this->option('batch');
        $retryLimit = (int) $this->option('retries');
        $handlerClass = $this->option('handler') ?? $this->streamHandlers[$stream] ?? null;

        if (! $handlerClass) {
            $this->error("No handler found for stream '{$stream}'. Pass --handler or register it in \$streamHandlers.");

            return 1;
        }

        if (! class_exists($handlerClass)) {
            $this->error("Handler class {$handlerClass} not found!");

            return 1;
        }

        $handler = app($handlerClass);

        if (! method_exists($handler, 'handle')) {
            $this->error("Handler class {$handlerClass} must have a handle method!");

            return 1;
        }

        // Create a configured consumer instance
        $consumer = new RedisStreamConsumer(
            $stream,
            $group,
            $consumer,
            $interval,
            $retryLimit,
            $batchSize
        );

        $this->info("Starting Redis Stream consumer for stream '{$stream}'");
        $this->info('Press Ctrl+C to stop');

        try {
            // Start consuming messages
            $consumer->consume(function ($data, $messageId) use ($handler) {
                $this->processMessage($data, $messageId, $handler);
            }, true);

            return 0;
        } catch (ConnectionException $e) {
            $this->error('Redis connection error: '.$e->getMessage());
            Log::error('Redis connection error in command: '.$e->getMessage());

            return 2;
        } catch (ConsumeException $e) {
            $this->error('Error consuming messages: '.$e->getMessage());
            Log::error('Redis Stream consumer error: '.$e->getMessage());

            return 1;
        } catch (Exception $e) {
            $this->error('Unexpected error: '.$e->getMessage());
            Log::error('Unexpected error in stream consumer command: '.$e->getMessage());

            return 3;
        }
    }

    /**
     * Process a message from the stream.
     *
     * @param  array  $data  The message data
     * @param  string  $messageId  The message ID
     * @param  object|null  $handler  The optional event handler
     * @return void
     */
    protected function processMessage(array $data, string $messageId, $handler = null): void
    {
        $event = $data['event'] ?? 'unknown';

        $this->line("<info>[{$event}]</info> Processing message {$messageId}");

        if ($handler) {
            try {
                $handler->handle($data, $messageId);
            } catch (Exception $e) {
                $this->error('Handler error: '.$e->getMessage());
                Log::error('Redis Stream handler error: '.$e->getMessage(), [
                    'event' => $event,
                    'message_id' => $messageId,
                ]);

                // Wrap in MessageProcessingException for better error tracking
                throw new MessageProcessingException(
                    $data['stream'] ?? 'unknown',
                    $messageId,
                    1,
                    'Handler error: '.$e->getMessage(),
                    0,
                    $e
                );
            }
        } else {
            // Simple debug output when no handler is provided
            $this->line(json_encode($data, JSON_PRETTY_PRINT));
        }
    }
}
