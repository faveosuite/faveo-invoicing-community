<?php

namespace Tests\Unit\Backend\Helper;

use Cache;
use Illuminate\Cache\Lock;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ThrottleApiRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_first_request_does_not_wait(): void
    {
        $start = microtime(as_float: true);

        throttleApiRequest('https://api.example.com/users', 60, 60);

        $elapsed = microtime(as_float: true) - $start;

        $this->assertLessThan(0.1, $elapsed, 'First request should not wait');
    }

    public function test_cache_key_is_set_after_first_request(): void
    {
        $url = 'https://api.example.com/users';
        $endpoint = parse_url($url, PHP_URL_HOST);
        $key = 'api_rate_next_allowed_'.md5($endpoint);

        throttleApiRequest($url, 60, 60);

        $this->assertNotNull(Cache::get($key), 'Cache key should be set after first request');
    }

    public function test_second_request_waits_approximately_one_interval(): void
    {
        $url = 'https://api.example.com/data';

        // 1 request per second => interval = 1 second
        // First request: no wait
        throttleApiRequest($url, 1, 1);

        $start = microtime(as_float: true);
        throttleApiRequest($url, 1, 1);
        $elapsed = microtime(as_float: true) - $start;

        // Should wait approximately 1 second
        $this->assertGreaterThan(0.8, $elapsed, 'Second request should wait ~1 second');
        $this->assertLessThan(1.5, $elapsed, 'Second request should not wait too long');
    }

    public function test_default_parameters_are60_requests_per60_seconds(): void
    {
        $url = 'https://api.example.com/endpoint';
        $endpoint = parse_url($url, PHP_URL_HOST);
        $key = 'api_rate_next_allowed_'.md5($endpoint);

        throttleApiRequest($url);

        $nextAllowed = Cache::get($key);
        $now = microtime(as_float: true);

        // interval = 60/60 = 1 second
        $this->assertGreaterThan($now - 0.1, $nextAllowed);
        $this->assertLessThan($now + 1.5, $nextAllowed);
    }

    public function test_different_hosts_are_throttled_independently(): void
    {
        $url1 = 'https://api.example.com/users';
        $url2 = 'https://api.other.com/users';

        $key1 = 'api_rate_next_allowed_'.md5(parse_url($url1, PHP_URL_HOST));
        $key2 = 'api_rate_next_allowed_'.md5(parse_url($url2, PHP_URL_HOST));

        // 1 request per second for tight throttling
        throttleApiRequest($url1, 1, 1);
        throttleApiRequest($url2, 1, 1);

        // Both should have their own cache keys
        $this->assertNotNull(Cache::get($key1));
        $this->assertNotNull(Cache::get($key2));

        // Second call to url2 should not be affected by url1's throttle timing
        $start = microtime(as_float: true);
        throttleApiRequest($url2, 1, 1);
        $elapsed = microtime(as_float: true) - $start;

        // url2 had only 1 prior call, so it should wait ~1s (the interval)
        $this->assertGreaterThan(0.8, $elapsed);
    }

    public function test_same_host_different_paths_share_throttle_by_default(): void
    {
        $url1 = 'https://api.example.com/v1/users';
        $url2 = 'https://api.example.com/v2/users';

        $host = parse_url($url1, PHP_URL_HOST);

        // perSite: true (default) — same host shares one key
        $this->assertEquals(
            'api_rate_next_allowed_'.md5($host),
            'api_rate_next_allowed_'.md5(parse_url($url2, PHP_URL_HOST))
        );
    }

    public function test_per_api_flag_throttles_by_endpoint(): void
    {
        $url1 = 'https://api.example.com/v1/users';
        $url2 = 'https://api.example.com/v2/users';

        $endpoint1 = parse_url($url1, PHP_URL_HOST).parse_url($url1, PHP_URL_PATH);
        $endpoint2 = parse_url($url2, PHP_URL_HOST).parse_url($url2, PHP_URL_PATH);
        $key1 = 'api_rate_next_allowed_'.md5($endpoint1);
        $key2 = 'api_rate_next_allowed_'.md5($endpoint2);

        throttleApiRequest($url1, 60, 60, perSite: false);
        throttleApiRequest($url2, 60, 60, perSite: false);

        // Different paths should have separate cache keys
        $this->assertNotNull(Cache::get($key1));
        $this->assertNotNull(Cache::get($key2));
        $this->assertNotEquals(md5($endpoint1), md5($endpoint2));
    }

    public function test_query_parameters_do_not_affect_throttle_key(): void
    {
        $url1 = 'https://api.example.com/users?page=1';
        $url2 = 'https://api.example.com/users?page=2';

        $endpoint1 = parse_url($url1, PHP_URL_HOST).parse_url($url1, PHP_URL_PATH);
        $endpoint2 = parse_url($url2, PHP_URL_HOST).parse_url($url2, PHP_URL_PATH);

        // Query strings are stripped by parse_url with PHP_URL_PATH
        $this->assertEquals(md5($endpoint1), md5($endpoint2));
    }

    public function test_cache_exception_does_not_throw(): void
    {
        Cache::shouldReceive('lock')->andThrow(new RuntimeException('Cache down'));

        // Should not throw, the function should silently return
        throttleApiRequest('https://api.example.com/users');

        $this->assertTrue(condition: true, message: 'Function should not throw when cache fails');
    }

    public function test_lock_timeout_exception_does_not_throw(): void
    {
        $mockLock = Mockery::mock(Lock::class);
        $mockLock->shouldReceive('block')->andThrow(new LockTimeoutException);

        Cache::shouldReceive('lock')->andReturn($mockLock);

        throttleApiRequest('https://api.example.com/users');

        $this->assertTrue(condition: true, message: 'Function should not throw on lock timeout');
    }

    public function test_interval_calculation_higher_rate_means_small_interval(): void
    {
        $url = 'https://api.example.com/fast';
        $endpoint = parse_url($url, PHP_URL_HOST);
        $key = 'api_rate_next_allowed_'.md5($endpoint);

        $beforeCall = microtime(as_float: true);
        // 100 requests per 10 seconds => interval = 0.1 seconds
        throttleApiRequest($url, 100, 10);
        $nextAllowed = Cache::get($key);

        // nextAllowed should be ~ now + 0.1
        $this->assertEqualsWithDelta($beforeCall + 0.1, $nextAllowed, 0.15);
    }

    public function test_consecutive_calls_stack_wait_times(): void
    {
        $url = 'https://api.example.com/stack';
        $endpoint = parse_url($url, PHP_URL_HOST);
        $key = 'api_rate_next_allowed_'.md5($endpoint);

        // 10 requests per 10 seconds => interval = 1 second
        throttleApiRequest($url, 10, 10);
        $first = Cache::get($key);

        throttleApiRequest($url, 10, 10);
        $second = Cache::get($key);

        throttleApiRequest($url, 10, 10);
        $third = Cache::get($key);

        // Each call should push the nextAllowed further by ~1 second
        $this->assertGreaterThan($first, $second);
        $this->assertGreaterThan($second, $third);
        $this->assertEqualsWithDelta(1.0, $second - $first, 0.3);
        $this->assertEqualsWithDelta(1.0, $third - $second, 0.3);
    }

    public function test_expired_slot_does_not_cause_wait(): void
    {
        $url = 'https://api.example.com/expired';
        $endpoint = parse_url($url, PHP_URL_HOST);
        $key = 'api_rate_next_allowed_'.md5($endpoint);

        // Set nextAllowed to a time in the past
        Cache::put($key, microtime(as_float: true) - 10, 300);

        $start = microtime(as_float: true);
        throttleApiRequest($url, 60, 60);
        $elapsed = microtime(as_float: true) - $start;

        $this->assertLessThan(0.1, $elapsed, 'Expired slot should not cause waiting');
    }

    public function test_cache_key_uses_host_by_default(): void
    {
        $url = 'https://payments.stripe.com/v1/charges';
        $expectedKey = 'api_rate_next_allowed_'.md5('payments.stripe.com');

        throttleApiRequest($url);

        $this->assertNotNull(Cache::get($expectedKey));
    }

    public function test_cache_put_stores_for300_seconds(): void
    {
        // Use a spy to verify the TTL passed to Cache::put
        Cache::shouldReceive('lock')->andReturnUsing(function () {
            $mockLock = Mockery::mock(Lock::class);
            $mockLock->shouldReceive('block')->andReturnUsing(function ($timeout, $callback): void {
                $callback();
            });

            return $mockLock;
        });

        Cache::shouldReceive('get')->andReturn(microtime(as_float: true));
        Cache::shouldReceive('put')->once()->withArgs(fn ($key, $value, $ttl): bool => $ttl === 300)->andReturnTrue();

        throttleApiRequest('https://api.example.com/ttl');
    }
}
