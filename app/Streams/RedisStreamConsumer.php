<?php

namespace App\Streams;

use App\Streams\Adapters\RedisAdapterInterface;
use App\Streams\Exceptions\ConnectionException;
use App\Streams\Exceptions\ConsumeException;
use App\Streams\Exceptions\MessageProcessingException;
use Closure;
use Exception;
use Illuminate\Support\Facades\Log;
use JsonException;

class RedisStreamConsumer
{
    /**
     * Whether a shutdown signal has been received.
     */
    private static bool $shouldShutdown = false;

    /**
     * The Redis adapter instance.
     */
    private readonly RedisAdapterInterface $redis;

    /**
     * Creates a new Redis Stream Consumer.
     *
     * @param  string  $stream  The Redis stream name
     * @param  string  $group  The consumer group name
     * @param  string  $consumer  The consumer name within the group
     * @param  int  $interval  The time to wait between polls in seconds
     * @param  int  $retryLimit  The number of retries for failed message processing
     * @param  int  $batchSize  The number of messages to read at once
     *
     * @throws ConnectionException If the Redis connection fails
     */
    public function __construct(
        public readonly string $stream,
        public readonly string $group,
        public readonly string $consumer,
        public readonly int $interval = 1,
        public readonly int $retryLimit = 3,
        public readonly int $batchSize = 1
    ) {
        $this->redis = RedisAdapterManager::create();
    }

    /**
     * Starts consuming messages from the Redis stream.
     *
     * @param  Closure  $callback  The callback to process each message
     * @param  bool  $stopOnSignal  Whether to stop the consumer on SIGTERM/SIGINT signals
     *
     * @throws ConsumeException If an error occurs during consumption
     * @throws ConnectionException If a Redis connection error occurs
     */
    final public function consume(Closure $callback, bool $stopOnSignal = true): void
    {
        // Setup consumer group if it doesn't exist
        try {
            $this->redis->xgroup('CREATE', $this->stream, $this->group, '0', true);
        } catch (Exception $e) {
            // Group already exists, which is fine
            Log::debug('Consumer group setup: '.$e->getMessage());
        }

        // Setup signal handling for graceful shutdown
        if ($stopOnSignal) {
            $this->setupSignalHandling();
        }

        $running = true;
        while ($running) {
            try {
                $messages = $this->redis->xreadgroup(
                    $this->group,
                    $this->consumer,
                    [$this->stream => '>'],
                    $this->batchSize
                );

                if ($messages) {
                    foreach ($messages as $stream => $entries) {
                        foreach ($entries as $id => $message) {
                            try {
                                $this->processMessage($id, $message, $callback);
                            } catch (MessageProcessingException $e) {
                                // Just log message processing exceptions and continue
                                // They will be reprocessed via the pending check
                                Log::error($e->getMessage());
                            }
                        }
                    }
                }

                // Check for pending messages that might need reprocessing
                $this->checkPendingMessages($callback);

                // Sleep between polls to avoid hammering Redis
                sleep($this->interval);
            } catch (ConnectionException $e) {
                Log::error('Redis connection error in consumer: '.$e->getMessage());
                // Wait a bit longer on connection error before retrying
                sleep($this->interval * 3);
            } catch (Exception $e) {
                Log::error('Error in Redis Stream consumer: '.$e->getMessage());
                // Wait a bit longer on error before retrying
                sleep($this->interval * 2);

                // Wrap in consumer exception for better error information
                throw new ConsumeException(
                    $this->stream,
                    $this->group,
                    $this->consumer,
                    $e->getMessage(),
                    0,
                    $e
                );
            }

            // Check if we should stop running (set by signal handler)
            if (self::$shouldShutdown) {
                $running = false;
                Log::info('Redis Stream consumer shutting down gracefully');
            }
        }
    }

    /**
     * Process a single message from the stream.
     *
     * @param  string  $id  The message ID
     * @param  array  $message  The message data
     * @param  Closure  $callback  The callback to process the message
     * @return void
     *
     * @throws MessageProcessingException If message processing fails
     * @throws ConnectionException If a Redis connection error occurs
     */
    private function processMessage(string $id, array $message, Closure $callback): void
    {
        try {
            $data = json_decode($message['message'], true, 512, JSON_THROW_ON_ERROR);
            $callback($data, $id);
            $this->redis->xack($this->stream, $this->group, [$id]);
        } catch (JsonException $e) {
            Log::error("Failed to decode message {$id}: ".$e->getMessage());
            $this->redis->xack($this->stream, $this->group, [$id]);
        } catch (ConnectionException $e) {
            // Don't acknowledge on connection error - retry later
            Log::error("Redis connection error while processing message {$id}: ".$e->getMessage());
            throw $e;
        } catch (Exception $e) {
            Log::error("Error processing message {$id}: ".$e->getMessage());

            // Convert to MessageProcessingException for better error handling
            $pendingInfo = $this->getMessagePendingInfo($id);
            $attempt = $pendingInfo ? $pendingInfo[3] : 1;

            // Don't acknowledge - message will be reprocessed on pending check
            throw new MessageProcessingException(
                $this->stream,
                $id,
                $attempt,
                $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Check for pending messages that might need reprocessing.
     *
     * @param  Closure  $callback  The callback to process each message
     * @return void
     *
     * @throws ConsumeException If an error occurs during processing pending messages
     * @throws ConnectionException If a Redis connection error occurs
     */
    private function checkPendingMessages(Closure $callback): void
    {
        try {
            // Get pending messages for this consumer
            $pending = $this->redis->xpending(
                $this->stream,
                $this->group,
                '-',
                '+',
                10,
                $this->consumer
            );

            if (! empty($pending)) {
                foreach ($pending as $message) {
                    // Check if message has exceeded retry limit
                    if ($message[3] >= $this->retryLimit) {
                        // Acknowledge the message to stop reprocessing
                        $this->redis->xack($this->stream, $this->group, [$message[0]]);
                        Log::warning("Message {$message[0]} exceeded retry limit and was skipped");
                        continue;
                    }

                    // Claim and process the message
                    $claimed = $this->redis->xclaim(
                        $this->stream,
                        $this->group,
                        $this->consumer,
                        0,
                        [$message[0]]
                    );

                    if (! empty($claimed)) {
                        foreach ($claimed as $id => $data) {
                            try {
                                $this->processMessage($id, $data, $callback);
                            } catch (MessageProcessingException $e) {
                                // Just log and continue, we'll try again next time
                                Log::error($e->getMessage());
                            }
                        }
                    }
                }
            }
        } catch (ConnectionException $e) {
            Log::error('Redis connection error while checking pending messages: '.$e->getMessage());
            throw $e;
        } catch (Exception $e) {
            Log::error('Error checking pending messages: '.$e->getMessage());

            throw new ConsumeException(
                $this->stream,
                $this->group,
                $this->consumer,
                'Failed to process pending messages: '.$e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Setup signal handling for graceful shutdown.
     *
     * @return void
     */
    private function setupSignalHandling(): void
    {
        if (extension_loaded('pcntl')) {
            pcntl_async_signals(true);

            pcntl_signal(SIGTERM, function () {
                self::$shouldShutdown = true;
            });

            pcntl_signal(SIGINT, function () {
                self::$shouldShutdown = true;
            });
        }
    }

    /**
     * Get information about a pending message.
     *
     * @param  string  $messageId  The message ID to get information for
     * @return array|null The pending message information or null if not found
     */
    private function getMessagePendingInfo(string $messageId): ?array
    {
        try {
            $pending = $this->redis->xpending(
                $this->stream,
                $this->group,
                $messageId,
                $messageId,
                1
            );

            return $pending[0] ?? null;
        } catch (Exception $e) {
            Log::warning("Could not get pending info for message {$messageId}: ".$e->getMessage());

            return null;
        }
    }
}
