<?php

namespace App\Streams;

use App\Streams\Adapters\RedisAdapterInterface;
use App\Streams\Exceptions\ConnectionException;
use App\Streams\Exceptions\PublishException;
use Exception;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

class RedisStreamProducer
{
    /**
     * The Redis adapter instance.
     */
    private readonly RedisAdapterInterface $redis;

    /**
     * Creates a new Redis Stream Producer.
     *
     * @param  string  $stream  The Redis stream name
     * @param  int|null  $maxLen  The maximum length of the stream (approximate)
     * @param  bool  $useExactMaxLen  Whether to use exact maxlen (slower) instead of approximate (~)
     *
     * @throws ConnectionException If the Redis connection fails
     */
    public function __construct(
        public readonly string $stream,
        public readonly ?int $maxLen = null,
        public readonly bool $useExactMaxLen = false
    ) {
        $this->redis = RedisAdapterManager::create();
    }

    /**
     * Publishes a message to the Redis stream.
     *
     * @param  string  $event  The event type/name
     * @param  string|array  $payload  The event payload data
     * @param  array  $options  Additional options for the message
     * @return string The message ID assigned by Redis
     *
     * @throws JsonException If JSON encoding fails
     * @throws RuntimeException|ConnectionException If publishing to Redis fails
     */
    final public function publish(string $event, string|array $payload, array $options = []): string
    {
        try {
            $data = [
                'event' => $event,
                'payload' => $payload,
                'timestamp' => now()->toDateTimeString(),
            ];

            // Add any additional metadata
            if (! empty($options)) {
                $data = array_merge($data, $options);
            }

            // Create Redis command options
            $params = ['message' => json_encode($data, JSON_THROW_ON_ERROR)];

            // Handle MAXLEN option for stream trimming
            $streamOptions = [];
            if ($this->maxLen !== null && $this->maxLen > 0) {
                $approximate = $this->useExactMaxLen ? '' : '~';
                $streamOptions['MAXLEN'] = [$approximate, $this->maxLen];
            }

            return $this->redis->xadd($this->stream, '*', $params, $streamOptions);
        } catch (JsonException $e) {
            throw new JsonException('Failed to encode message for Redis Stream: '.$e->getMessage(), $e->getCode(), $e);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'connection')) {
                throw new ConnectionException('Connection to Redis failed: '.$e->getMessage(), 0, $e);
            }

            throw new PublishException($this->stream, $e->getMessage(), 0, $e);
        }
    }

    /**
     * Publishes a batch of messages to the Redis stream in a pipeline.
     *
     * @param  array  $messages  Array of messages, each with 'event' and 'payload' keys
     * @return array Array of message IDs
     *
     * @throws RuntimeException If publishing to Redis fails
     */
    public function publishBatch(array $messages): array
    {
        if (empty($messages)) {
            return [];
        }

        try {
            $ids = [];
            $streamOptions = [];
            if ($this->maxLen !== null && $this->maxLen > 0) {
                $approximate = $this->useExactMaxLen ? '' : '~';
                $streamOptions['MAXLEN'] = [$approximate, $this->maxLen];
            }

            foreach ($messages as $message) {
                if (! isset($message['event']) || ! isset($message['payload'])) {
                    throw new InvalidArgumentException('Each message must have event and payload keys');
                }

                $data = [
                    'event' => $message['event'],
                    'payload' => $message['payload'],
                    'timestamp' => $message['timestamp'] ?? now()->toDateTimeString(),
                ];

                if (isset($message['options']) && is_array($message['options'])) {
                    $data = array_merge($data, $message['options']);
                }

                $params = ['message' => json_encode($data, JSON_THROW_ON_ERROR)];

                $ids[] = $this->redis->xadd($this->stream, '*', $params, $streamOptions);
            }

            return $ids;
        } catch (JsonException $e) {
            throw new JsonException('Failed to encode messages for Redis Stream batch: '.$e->getMessage(), $e->getCode(), $e);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'connection')) {
                throw new ConnectionException('Connection to Redis failed during batch publish: '.$e->getMessage(), 0, $e);
            }

            throw new PublishException($this->stream, 'Error publishing batch: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Trims the stream to a specific length.
     *
     * @param  int  $maxLen  The maximum length to trim the stream to
     * @param  bool  $exact  Whether to use exact trimming (slower) or approximate
     * @return int The number of messages deleted
     *
     * @throws RuntimeException If trimming the stream fails
     */
    public function trim(int $maxLen, bool $exact = false): int
    {
        if ($maxLen <= 0) {
            throw new InvalidArgumentException('The maximum length must be greater than zero');
        }

        try {
            $approximate = $exact ? '' : '~';

            return $this->redis->xtrim($this->stream, $approximate, $maxLen);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'connection')) {
                throw new ConnectionException('Connection to Redis failed during stream trimming: '.$e->getMessage(), 0, $e);
            }

            throw new PublishException($this->stream, 'Error trimming stream: '.$e->getMessage(), 0, $e);
        }
    }
}
