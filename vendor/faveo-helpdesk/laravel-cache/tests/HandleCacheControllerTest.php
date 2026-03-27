<?php

namespace Tests;

use CacheDriver\HandleCacheController;

class HandleCacheControllerTest extends \PHPUnit\Framework\TestCase
{
    private function reset()
    {
        file_put_contents(__DIR__.'/../src/constant.ini', "DEFAULT=file");
    }

    public function test_modify_array_cache()
    {
        $this->reset();

        $cache = new HandleCacheController();

        $cache->modify(['default' => 'array']);

        $this->assertEquals('array', $cache::value('DEFAULT'));
    }

    public function test_modify_database_cache()
    {
        $this->reset();

        $cache = new HandleCacheController();

        $cache->modify(['default' => 'database']);

        $this->assertEquals('database', $cache::value('DEFAULT'));
    }

    public function test_modify_redis_cache()
    {
        $this->reset();

        $cache = new HandleCacheController();

        $cache->modify(['default' => 'redis', 'connection_redis' => 'default']);

        $this->assertEquals('redis', $cache::value('DEFAULT'));
        $this->assertEquals('default', $cache::value('CONNECTION_REDIS'));
    }

    public function test_modify_memcached_cache()
    {
        $this->reset();

        $cache = new HandleCacheController();

        $cache->modify(['default' => 'memcached', 'memcached_host' => 'localhost', 'memcached_port' => '123']);

        $this->assertEquals('memcached', $cache::value('DEFAULT'));
        $this->assertEquals('localhost', $cache::value('MEMCACHED_HOST'));
        $this->assertEquals('123', $cache::value('MEMCACHED_PORT'));
    }

    public function test_modify_dynamodb_cache()
    {
        $this->reset();

        $cache = new HandleCacheController();

        $cache->modify(['default' => 'dynamodb', 'aws_access_key_id' => 'id', 'aws_secret_access_key' => 'secret', 'aws_default_region' => 'usa', 'dynamodb_cache_table' => 'cache', 'dynamodb_endpoint' => 'google.com']);

        $this->assertEquals('dynamodb', $cache::value('DEFAULT'));
        $this->assertEquals('id', $cache::value('AWS_ACCESS_KEY_ID'));
        $this->assertEquals('secret', $cache::value('AWS_SECRET_ACCESS_KEY'));
        $this->assertEquals('usa', $cache::value('AWS_DEFAULT_REGION'));
        $this->assertEquals('cache', $cache::value('DYNAMODB_CACHE_TABLE'));
        $this->assertEquals('google.com', $cache::value('DYNAMODB_ENDPOINT'));
    }
}