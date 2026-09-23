<?php

namespace Laravel\Sentinel;

use Illuminate\Support\Manager;
use Laravel\Sentinel\Drivers\Laravel;

class SentinelManager extends Manager
{
    /**
     * Create default "laravel" driver.
     *
     * @return Laravel
     */
    protected function createLaravelDriver()
    {
        /** @phpstan-ignore argument.type */
        return new Laravel(fn () => $this->getContainer());
    }

    /**
     * Get the default driver name.
     *
     * @return string|null
     */
    public function getDefaultDriver()
    {
        return 'laravel';
    }

    /**
     * Get a driver instance or fallback to default
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function driverOrFallback(?string $driver)
    {
        return rescue(function () use ($driver) {
            return $this->driver($driver);
        }, value(function () {
            return $this->driver();
        }), false);
    }
}
