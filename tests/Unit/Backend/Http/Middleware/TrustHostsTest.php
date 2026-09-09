<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Middleware;

use App\Http\Middleware\TrustHosts;
use Tests\TestCase;

class TrustHostsTest extends TestCase
{
    public function test_hosts_returns_array(): void
    {
        $middleware = new TrustHosts($this->app);
        $hosts = $middleware->hosts();

        $this->assertIsArray($hosts);
    }
}
