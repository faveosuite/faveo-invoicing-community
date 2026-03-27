<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Streams\License\LicenseStreamHandler;
use App\Streams\StreamConfig;
use Illuminate\Http\Request;

class RedisStreamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index()
    {
        $currentValues = [
            'stream_redis_host' => StreamConfig::value('STREAM_REDIS_HOST', env('REDIS_HOST', '127.0.0.1')),
            'stream_redis_port' => StreamConfig::value('STREAM_REDIS_PORT', env('REDIS_PORT', '6379')),
            'stream_redis_username' => StreamConfig::value('STREAM_REDIS_USERNAME', ''),
            'stream_redis_password' => StreamConfig::value('STREAM_REDIS_PASSWORD', ''),
            'stream_redis_database' => StreamConfig::value('STREAM_REDIS_DATABASE', '2'),
        ];

        return view('themes.default1.redis-stream.index', compact('currentValues'));
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'stream_redis_host' => 'required|string',
                'stream_redis_port' => 'required|numeric',
                'stream_redis_database' => 'required|numeric',
            ]);

            $this->testConnection($request->all());

            StreamConfig::modify($request->only([
                'stream_redis_host',
                'stream_redis_port',
                'stream_redis_username',
                'stream_redis_password',
                'stream_redis_database',
            ]));

            return successResponse(__('message.redis_stream_config_updated'));
        } catch (\Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function testStreamEvent()
    {
        try {
            LicenseStreamHandler::ping();

            return successResponse(__('message.redis_stream_ping_success'));
        } catch (\Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.stream_test_timeout'));
        }
    }

    private function testConnection($args)
    {
        $host = checkArray('stream_redis_host', $args) ?: '127.0.0.1';
        $port = (int) (checkArray('stream_redis_port', $args) ?: 6379);
        $password = checkArray('stream_redis_password', $args) ?: null;
        $database = (int) (checkArray('stream_redis_database', $args) ?: 2);
        $username = checkArray('stream_redis_username', $args) ?: null;

        $client = config('database.redis.client', 'phpredis');

        if ($client === 'phpredis') {
            throw_if(
                ! extension_loaded('redis'),
                new \Exception(__('message.redis_extension_not_installed'))
            );

            $redis = new \Redis();
            $redis->connect($host, $port, 5);

            if ($password) {
                $redis->auth($username ? [$username, $password] : $password);
            }

            $redis->select($database);
            $redis->ping();
            $redis->close();
        } else {
            $config = [
                'host' => $host,
                'port' => $port,
                'database' => $database,
            ];

            if ($password) {
                $config['password'] = $password;
            }
            if ($username) {
                $config['username'] = $username;
            }

            $predis = new \Predis\Client($config);
            $predis->ping();
            $predis->disconnect();
        }
    }
}
