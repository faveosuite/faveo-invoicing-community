<?php

use Illuminate\Support\Str;
use CacheDriver\HandleCacheController as advance;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache connection that gets used while
    | using this caching library. This connection is used when another is
    | not explicitly specified when executing a given caching function.
    | By default driver should be database to handle cache in multitenancy too
    */

    'default' => advance::value('DEFAULT', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    | Supported drivers: "apc", "array", "database", "file",
    |         "memcached", "redis", "dynamodb", "octane", "null"
    |
    */

    'stores' => [

        'apc' => [
            'driver' => 'apc',
        ],

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
            'lock_connection' => null,
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path().'/framework/cache',
        ],

        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => advance::value('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                advance::value('MEMCACHED_USERNAME'),
                advance::value('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => advance::value('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => advance::value('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => advance::value('CONNECTION_REDIS', 'default'),
            'lock_connection' => advance::value('LOCK_CONNECTION_REDIS', 'default'),
        ],

        'request' => [
            'driver' => 'array',
        ],

        'dynamodb' => [
            'driver' => 'dynamodb',
            'key' => advance::value('AWS_ACCESS_KEY_ID'),
            'secret' => advance::value('AWS_SECRET_ACCESS_KEY'),
            'region' => advance::value('AWS_DEFAULT_REGION', 'us-east-1'),
            'table' => advance::value('DYNAMODB_CACHE_TABLE', 'cache'),
            'endpoint' => advance::value('DYNAMODB_ENDPOINT'),
        ],

        'octane' => [
            'driver' => 'octane',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing the APC, database, memcached, Redis, or DynamoDB cache
    | stores there might be other applications using the same cache. For
    | that reason, you may prefix every cache key to avoid collisions.
    |
    */

    'prefix' => advance::value('PREFIX', Str::slug(config('app.url'), '_').'_cache_'),

];
