<?php

namespace App\Streams;

use App\Streams\Adapters\PhpRedisAdapter;
use App\Streams\Adapters\PredisAdapter;
use App\Streams\Adapters\RedisAdapterInterface;
use App\Streams\Exceptions\ConnectionException;
use Illuminate\Support\Facades\Redis;

class RedisAdapterManager
{
    private static ?RedisAdapterInterface $instance = null;

    /**
     * Create or return the cached Redis adapter based on the driver in use.
     *
     * @return RedisAdapterInterface
     *
     * @throws ConnectionException
     */
    public static function create(): RedisAdapterInterface
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $driver = Redis::connection('streams')->client();
        $driverName = get_class($driver);

        // PhpRedis adapter
        if ($driverName === 'Redis' || str_contains($driverName, 'PhpRedis')) {
            self::$instance = new PhpRedisAdapter();

            return self::$instance;
        }

        // Predis adapter
        if (str_contains($driverName, 'Predis')) {
            self::$instance = new PredisAdapter();

            return self::$instance;
        }

        throw new ConnectionException("Unsupported Redis driver: {$driverName}. Supported drivers are PhpRedis and Predis.");
    }

    /**
     * Reset the cached instance (useful for testing).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
