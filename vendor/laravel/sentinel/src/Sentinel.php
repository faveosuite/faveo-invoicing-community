<?php

namespace Laravel\Sentinel;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string|null getDefaultDriver()
 * @method static mixed driverOrFallback(string|null $driver)
 * @method static mixed driver(string|null $driver = null)
 * @method static \Laravel\Sentinel\SentinelManager extend(string $driver, \Closure $callback)
 * @method static array getDrivers()
 * @method static \Illuminate\Contracts\Container\Container getContainer()
 * @method static \Laravel\Sentinel\SentinelManager setContainer(\Illuminate\Contracts\Container\Container $container)
 * @method static \Laravel\Sentinel\SentinelManager forgetDrivers()
 *
 * @see SentinelManager
 */
class Sentinel extends Facade
{
    /** {@inheritdoc} */
    protected static function getFacadeAccessor()
    {
        return SentinelManager::class;
    }
}
