<?php

namespace App\Streams;

use App\Streams\Adapters\RedisAdapterInterface;
use App\Streams\Exceptions\ConnectionException;
use App\Streams\Exceptions\MessageProcessingException;
use Closure;
use Exception;
use JsonException;

class RedisStreamConsumer
{
    private static bool $shouldShutdown = false;

    private readonly RedisAdapterInterface $redis;

    public function __construct(
        public readonly string $stream,
        public readonly string $group,
        public readonly string $consumer,
        public readonly int $interval = 1,
        public readonly int $retryLimit = 3,
        public readonly int $batchSize = 10
    ) {
        $this->redis = RedisAdapterManager::create();
    }

    final public function consume(Closure $callback, bool $stopOnSignal = true): void
    {
        $this->ensureConsumerGroup();

        if ($stopOnSignal) {
            $this->setupSignalHandling();
        }

        $pollCount = 1;
        $pendingCheckInterval = 10;

        while (! self::$shouldShutdown) {
            try {
                if ($pollCount % $pendingCheckInterval === 0) {
                    $this->ensureConsumerGroup();
                }

                $messages = $this->redis->xreadgroup(
                    $this->group,
                    $this->consumer,
                    [$this->stream => '>'],
                    $this->batchSize
                );

                $hadMessages = false;

                if ($messages) {
                    foreach ($messages as $entries) {
                        foreach ($entries as $id => $message) {
                            $this->processMessageSafely($id, $message, $callback);
                        }
                    }
                    $hadMessages = true;
                }

                if ($pollCount % $pendingCheckInterval === 0) {
                    $this->checkPendingMessages($callback);
                }

                $pollCount = ($pollCount + 1) % $pendingCheckInterval;

                if (! $hadMessages) {
                    sleep($this->interval);
                }
            } catch (ConnectionException $e) {
                sleep($this->interval * 3);
            } catch (Exception $e) {
                if ($this->isGroupOrStreamError($e)) {
                    $this->ensureConsumerGroup();
                    sleep($this->interval);

                    continue;
                }

                throw $e;
            }
        }
    }

    private function processMessageSafely(string $id, array $message, Closure $callback): void
    {
        try {
            $this->processMessage($id, $message, $callback);
        } catch (MessageProcessingException) {
        }
    }

    private function processMessage(string $id, array $message, Closure $callback): void
    {
        try {
            $data = json_decode($message['message'], true, 512, JSON_THROW_ON_ERROR);
            $callback($data, $id);
            $this->redis->xack($this->stream, $this->group, [$id]);
        } catch (JsonException) {
            return;
        } catch (ConnectionException $e) {
            throw $e;
        } catch (Exception $e) {

            $pendingInfo = $this->getMessagePendingInfo($id);

            throw new MessageProcessingException(
                $this->stream,
                $id,
                $pendingInfo[3] ?? 1,
                $e->getMessage(),
                0,
                $e
            );
        }
    }

    private function checkPendingMessages(Closure $callback): void
    {
        try {
            $pending = $this->redis->xpending(
                $this->stream,
                $this->group,
                '-',
                '+',
                $this->batchSize,
                $this->consumer
            );

            foreach ($pending as $message) {
                if ($message[3] >= $this->retryLimit) {
                    $this->redis->xack($this->stream, $this->group, [$message[0]]);

                    continue;
                }

                $claimed = $this->redis->xclaim(
                    $this->stream,
                    $this->group,
                    $this->consumer,
                    60000,
                    [$message[0]]
                );

                foreach ($claimed as $id => $data) {
                    $this->processMessageSafely($id, $data, $callback);
                }
            }
        } catch (ConnectionException $e) {
            throw $e;
        } catch (Exception) {
        }
    }

    private function ensureConsumerGroup(): void
    {
        try {
            $this->redis->xgroup('CREATE', $this->stream, $this->group, '0', true);
        } catch (Exception) {
            // Group already exists
        }
    }

    private function isGroupOrStreamError(Exception $e): bool
    {
        $msg = strtolower($e->getMessage());

        return str_contains($msg, 'nogroup') || str_contains($msg, 'no such key');
    }

    private function setupSignalHandling(): void
    {
        if (! extension_loaded('pcntl')) {
            return;
        }

        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, fn () => self::$shouldShutdown = true);
        pcntl_signal(SIGINT, fn () => self::$shouldShutdown = true);
    }

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
        } catch (Exception) {
            return null;
        }
    }
}
