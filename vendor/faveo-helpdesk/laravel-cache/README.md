![Faveo_Logo](https://github.com/ladybirdweb/advance-cache/assets/88606418/5bfce44f-1e01-473c-846c-d6048c7e605c)


# Handle multiple cache drivers

## Installation and Usage Instructions

## What It Does
This package allows you to choose cache driver from application without contacting server or any other files.

## Installation

```php

composer require faveo-helpdesk/laravel-cache

```
Publish the package

```php

php artisan vendor:pubish --force --tag=advance-cache

```
## Usage Instructions

How to update a cache driver ?

```php
use CacheDriver\HandleCacheController;

$cache = new HandleCacheController();

// Store file driver
$cache->modify(['DEFAULT' => 'file']);

// Store database driver
$cache->modify(['DEFAULT' => 'database']);

// Store array driver
$cache->modify(['DEFAULT' => 'array']);

// Store redis driver
$cache->modify(['DEFAULT' => 'redis', 'CONNECTION_REDIS' => 'default']);

// Store memcached driver
$cache->modify(['DEFAULT' => 'memcached', 'MEMCACHED_HOST' => '', 'MEMCACHED_PORT' => '', 'MEMCACHED_PERSISTENT_ID' => '', 'MEMCACHED_USERNAME' => '', 'MEMCACHED_PASSWORD' => '']);

// Store dynamodb driver
$cache->modify(['DEFAULT' => 'dynamodb', 'AWS_ACCESS_KEY_ID' => '', 'AWS_SECRET_ACCESS_KEY' => '', 'AWS_DEFAULT_REGION' => '', 'DYNAMODB_CACHE_TABLE' => '', 'DYNAMODB_ENDPOINT' => '']);
```

How to get a speific Constant Value with Default value ?

```php
use CacheDriver\HandleCacheController;

$cache = new HandleCacheController();

// Gets a default driver. If constant was not there, 'database' value will be picked. Not only DEFAULT constant but also serach all kind of cache Constant value
$cache::value('DEFAULT', 'database');
```

How to get a all values from speific driver ?

```php
use CacheDriver\HandleCacheController;

$cache = new HandleCacheController();

// Passes a driver like file, database, array, redis, memcached and dynamodb
$cache::all('file');
```

## License

The MIT License (MIT).
