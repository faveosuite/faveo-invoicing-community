<?php

namespace App\Streams;

use App\Streams\Adapters\PhpRedisAdapter;
use App\Streams\Adapters\PredisAdapter;
use App\Streams\Adapters\RedisAdapterInterface;
use App\Streams\Exceptions\ConnectionException;
use Illuminate\Support\Facades\Redis;

class RedisAdapterManager
{
    /**
     * Create a Redis adapter based on the driver in use.
     *
     * @return RedisAdapterInterface
     *
     * @throws ConnectionException
     */
    public static function create(): RedisAdapterInterface
    {
        $driver = Redis::connection('streams')->client();
        $driverName = get_class($driver);

        // PhpRedis adapter
        if ($driverName === 'Redis' || str_contains($driverName, 'PhpRedis')) {
            return new PhpRedisAdapter();
        }

        // Predis adapter
        if (str_contains($driverName, 'Predis')) {
            return new PredisAdapter();
        }

        throw new ConnectionException("Unsupported Redis driver: {$driverName}. Supported drivers are PhpRedis and Predis.");
    }
}
