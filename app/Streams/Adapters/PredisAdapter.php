<?php

namespace App\Streams\Adapters;

use Illuminate\Support\Facades\Redis;
use Predis\Command\RawCommand;

class PredisAdapter implements RedisAdapterInterface
{
    /**
     * Get the Redis connection.
     *
     * @param  string|null  $connection  The Redis connection name
     * @return mixed
     */
    public function connection(?string $connection = null): mixed
    {
        return Redis::connection($connection);
    }

    /**
     * Execute a raw Redis command on the streams connection.
     *
     * @param  array  $args  The command arguments
     * @return mixed
     */
    protected function executeRaw(array $args): mixed
    {
        return $this->connection('streams')->client()->executeCommand(
            RawCommand::create(...$args)
        );
    }

    /**
     * Add a message to a stream.
     *
     * @param  string  $stream  The stream key
     * @param  string  $id  The message ID ('*' for auto-generation)
     * @param  array  $message  The message fields and values
     * @param  array  $options  Additional options
     * @return string The message ID
     */
    public function xadd(string $stream, string $id, array $message, array $options = []): string
    {
        $args = ['XADD', $stream];

        if (isset($options['MAXLEN'])) {
            $args[] = 'MAXLEN';
            if ($options['MAXLEN'][0] === '~') {
                $args[] = '~';
            }
            $args[] = (string) $options['MAXLEN'][1];
        }

        $args[] = $id;

        foreach ($message as $field => $value) {
            $args[] = (string) $field;
            $args[] = (string) $value;
        }

        return $this->executeRaw($args);
    }

    /**
     * Delete a stream.
     *
     * @param  string  $stream  The stream key
     * @return int Number of streams deleted
     */
    public function del(string $stream): int
    {
        return $this->executeRaw(['DEL', $stream]);
    }

    /**
     * Get stream messages in a range.
     *
     * @param  string  $stream  The stream key
     * @param  string  $start  The start ID
     * @param  string  $end  The end ID
     * @param  int|null  $count  Maximum number of messages to return
     * @return array The messages
     */
    public function xrange(string $stream, string $start, string $end, ?int $count = null): array
    {
        $args = ['XRANGE', $stream, $start, $end];

        if ($count !== null) {
            $args[] = 'COUNT';
            $args[] = (string) $count;
        }

        $result = $this->executeRaw($args);

        return $this->parseStreamEntries($result);
    }

    /**
     * Create a consumer group.
     *
     * @param  string  $command  The XGROUP command (CREATE, DESTROY, etc.)
     * @param  string  $stream  The stream key
     * @param  string  $group  The group name
     * @param  string  $id  The ID to start consuming from
     * @param  bool  $mkstream  Whether to create the stream if it doesn't exist
     * @return mixed
     */
    public function xgroup(string $command, string $stream, string $group, string $id, bool $mkstream = false)
    {
        $args = ['XGROUP', $command, $stream, $group, $id];

        if ($mkstream) {
            $args[] = 'MKSTREAM';
        }

        return $this->executeRaw($args);
    }

    /**
     * Read messages from a stream as a consumer group.
     *
     * @param  string  $group  The group name
     * @param  string  $consumer  The consumer name
     * @param  array  $streams  The streams and IDs to read from
     * @param  int  $count  Maximum number of messages to return
     * @param  int|null  $block  Milliseconds to block for (null = non-blocking)
     * @return array The messages
     */
    public function xreadgroup(string $group, string $consumer, array $streams, int $count, ?int $block = null): array
    {
        $args = ['XREADGROUP', 'GROUP', $group, $consumer];

        if ($block !== null) {
            $args[] = 'BLOCK';
            $args[] = (string) $block;
        }

        $args[] = 'COUNT';
        $args[] = (string) $count;
        $args[] = 'STREAMS';

        foreach (array_keys($streams) as $name) {
            $args[] = $name;
        }
        foreach (array_values($streams) as $streamId) {
            $args[] = $streamId;
        }

        $result = $this->executeRaw($args);

        if (! $result) {
            return [];
        }

        // Raw response: [['stream', [['id', ['f1', 'v1', ...]], ...]], ...]
        // Expected:     ['stream' => ['id' => ['f1' => 'v1', ...], ...], ...]
        $parsed = [];
        foreach ($result as $streamData) {
            $streamName = $streamData[0];
            $parsed[$streamName] = $this->parseStreamEntries($streamData[1]);
        }

        return $parsed;
    }

    /**
     * Acknowledge a message.
     *
     * @param  string  $stream  The stream key
     * @param  string  $group  The group name
     * @param  array  $ids  The message IDs to acknowledge
     * @return int Number of messages acknowledged
     */
    public function xack(string $stream, string $group, array $ids): int
    {
        return $this->executeRaw(['XACK', $stream, $group, ...$ids]);
    }

    /**
     * Get pending messages information.
     *
     * @param  string  $stream  The stream key
     * @param  string  $group  The group name
     * @param  string  $start  The start ID
     * @param  string  $end  The end ID
     * @param  int  $count  Maximum number of messages to return
     * @param  string|null  $consumer  Filter by consumer
     * @return array Pending messages information
     */
    public function xpending(string $stream, string $group, string $start, string $end, int $count, ?string $consumer = null): array
    {
        $args = ['XPENDING', $stream, $group, $start, $end, (string) $count];

        if ($consumer !== null) {
            $args[] = $consumer;
        }

        $result = $this->executeRaw($args);

        return $result ?: [];
    }

    /**
     * Claim ownership of pending messages.
     *
     * @param  string  $stream  The stream key
     * @param  string  $group  The group name
     * @param  string  $consumer  The consumer name
     * @param  int  $minIdleTime  Minimum idle time in milliseconds
     * @param  array  $ids  The message IDs to claim
     * @param  array  $options  Additional options
     * @return array The claimed messages
     */
    public function xclaim(string $stream, string $group, string $consumer, int $minIdleTime, array $ids, array $options = []): array
    {
        $args = ['XCLAIM', $stream, $group, $consumer, (string) $minIdleTime, ...$ids];

        foreach ($options as $key => $value) {
            $args[] = (string) $key;
            if ($value !== null && $value !== true) {
                $args[] = (string) $value;
            }
        }

        $result = $this->executeRaw($args);

        return $this->parseStreamEntries($result);
    }

    /**
     * Delete messages from a stream by ID.
     *
     * @param  string  $stream  The stream key
     * @param  array  $ids  The message IDs to delete
     * @return int Number of messages deleted
     */
    public function xdel(string $stream, array $ids): int
    {
        return $this->executeRaw(['XDEL', $stream, ...$ids]);
    }

    /**
     * Trim a stream to a maximum length.
     *
     * @param  string  $stream  The stream key
     * @param  string  $maxlen  The maximum length strategy ('~' for approximate)
     * @param  int  $count  The maximum number of elements
     * @return int Number of messages deleted
     */
    public function xtrim(string $stream, string $maxlen, int $count): int
    {
        $args = ['XTRIM', $stream, 'MAXLEN'];

        if ($maxlen === '~') {
            $args[] = '~';
        }

        $args[] = (string) $count;

        return $this->executeRaw($args);
    }

    /**
     * Get stream information.
     *
     * @param  string  $command  The XINFO command (STREAM, GROUPS, CONSUMERS)
     * @param  string  $stream  The stream key
     * @param  mixed  ...$args  Additional arguments
     * @return array The stream information
     */
    public function xinfo(string $command, string $stream, ...$args): array
    {
        $rawArgs = ['XINFO', $command, $stream];

        foreach ($args as $arg) {
            $rawArgs[] = (string) $arg;
        }

        $result = $this->executeRaw($rawArgs);

        return $result ?: [];
    }

    /**
     * Create a pipeline for batch operations.
     *
     * @return mixed
     */
    public function pipeline()
    {
        return $this->connection('streams')->pipeline();
    }

    /**
     * Read messages from streams, optionally blocking until data is available.
     */
    public function xread(array $streams, int $count, ?int $block = null): array
    {
        $args = ['XREAD'];

        if ($block !== null) {
            $args[] = 'BLOCK';
            $args[] = (string) $block;
        }

        $args[] = 'COUNT';
        $args[] = (string) $count;
        $args[] = 'STREAMS';

        foreach (array_keys($streams) as $name) {
            $args[] = $name;
        }
        foreach (array_values($streams) as $lastId) {
            $args[] = $lastId;
        }

        $result = $this->executeRaw($args);

        if (! $result) {
            return [];
        }

        $parsed = [];
        foreach ($result as $streamData) {
            $streamName = $streamData[0];
            $parsed[$streamName] = $this->parseStreamEntries($streamData[1]);
        }

        return $parsed;
    }

    /**
     * Parse raw Redis stream entries into associative arrays.
     *
     * Converts [['id', ['f1', 'v1', 'f2', 'v2']], ...] to ['id' => ['f1' => 'v1', 'f2' => 'v2'], ...]
     *
     * @param  array|null  $entries  The raw stream entries
     * @return array The parsed entries
     */
    protected function parseStreamEntries(?array $entries): array
    {
        if (! $entries) {
            return [];
        }

        $parsed = [];
        foreach ($entries as $entry) {
            $id = $entry[0];
            $fields = $entry[1];
            $message = [];

            for ($i = 0, $len = count($fields); $i < $len; $i += 2) {
                $message[$fields[$i]] = $fields[$i + 1];
            }

            $parsed[$id] = $message;
        }

        return $parsed;
    }
}
