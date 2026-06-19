<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Model\Common\CommonSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Memcached;
use Predis\Client;
use Redis;
use RuntimeException;
use Throwable;

class CacheSettingsController extends Controller
{
    private const array DRIVERS = [
        ['name' => 'File',      'short_name' => 'file'],
        ['name' => 'Database',  'short_name' => 'database'],
        ['name' => 'Redis',     'short_name' => 'redis'],
        ['name' => 'Memcached', 'short_name' => 'memcached'],
        ['name' => 'DynamoDB',  'short_name' => 'dynamodb'],
    ];

    public function getDriverData(): \Illuminate\Http\JsonResponse
    {
        $active = CommonSettings::where('option_name', 'cache')
            ->where('optional_field', 'driver')
            ->value('option_value') ?? 'file';

        $drivers = collect(self::DRIVERS)->map(fn ($d): array => [
            'DriverDetails' => [
                'id' => $d['short_name'],
                'name' => ['text' => $d['name'], 'link' => $this->hasForm($d['short_name'])],
                'status' => [
                    'label' => $d['short_name'] === $active ? __('message.active') : __('message.inactive'),
                    'code' => $d['short_name'] === $active ? 1 : 0,
                ],
                'configured' => $this->isConfigured($d['short_name']),
                'action' => ['type' => $d['short_name'] === $active ? 'activated' : 'activate'],
            ],
        ]);

        return successResponse('', [
            'drivers' => ['data' => $drivers->values(), 'total' => count(self::DRIVERS)],
            'active_driver' => $active,
        ]);
    }

    public function getFormByDriver(string $driver): \Illuminate\Http\JsonResponse
    {
        return successResponse('', [
            'driver' => $driver,
            'fields' => $this->formFields($driver),
        ]);
    }

    public function update(Request $request, string $driver): \Illuminate\Http\JsonResponse
    {
        if ($this->formFields($driver) === []) {
            return errorResponse(__('message.no_fields_to_update'), 422);
        }

        $data = collect($request->except('_token'))
            ->mapWithKeys(fn ($value, $key): array => [strtoupper((string) $key) => $value ?? ''])
            ->all();

        $error = $this->testConnection($driver, $data);
        if ($error) {
            return errorResponse($error, 422);
        }

        setEnvValue($data);

        Artisan::call('config:clear');

        return successResponse(__('message.updated_successfully'));
    }

    public function activate(string $driver): \Illuminate\Http\JsonResponse
    {
        if (collect(self::DRIVERS)->pluck('short_name')->doesntContain($driver)) {
            return errorResponse(__('message.invalid_driver'), 422);
        }

        if ($this->hasForm($driver) && ! $this->isConfigured($driver)) {
            return errorResponse(__('message.activate_configure_first', ['name' => ucfirst($driver)]), 422);
        }

        if ($this->hasForm($driver)) {
            $error = $this->testConnection($driver, []);
            if ($error) {
                return errorResponse($error, 422);
            }
        }

        CommonSettings::upsert(
            [['option_name' => 'cache', 'optional_field' => 'driver', 'option_value' => $driver, 'status' => '']],
            ['option_name', 'optional_field'],
            ['option_value']
        );

        Artisan::call('config:clear');

        return successResponse(__('message.updated_successfully'));
    }

    private function hasForm(string $driver): bool
    {
        return in_array($driver, ['redis', 'memcached', 'dynamodb'], strict: true);
    }

    /**
     * @param  array<mixed>  $data
     */
    private function testConnection(string $driver, array $data): ?string
    {
        try {
            match ($driver) {
                'redis' => $this->testRedis(
                    $data['REDIS_HOST'] ?? config('database.redis.default.host', '127.0.0.1'),
                    (int) ($data['REDIS_PORT'] ?? config('database.redis.default.port', 6379)),
                    $data['REDIS_PASSWORD'] ?? config('database.redis.default.password', '')
                ),
                'memcached' => $this->testMemcached(
                    $data['MEMCACHED_HOST'] ?? config('cache.stores.memcached.servers.0.host', '127.0.0.1'),
                    (int) ($data['MEMCACHED_PORT'] ?? config('cache.stores.memcached.servers.0.port', 11211))
                ),
                default => null,
            };
        } catch (Throwable $throwable) {
            return $throwable->getMessage();
        }

        return null;
    }

    private function testRedis(string $host, int $port, string $password): void
    {
        if (extension_loaded('redis')) {
            $redis = new Redis();
            if (! $redis->connect($host, $port, 3)) {
                throw new RuntimeException(sprintf('Could not connect to Redis at %s:%d', $host, $port));
            }

            if ($password !== '' && $password !== '0') {
                $redis->auth($password);
            }

            $redis->ping();
            $redis->close();
        } elseif (class_exists(Client::class)) {
            $client = new Client([
                'host' => $host,
                'port' => $port,
                'password' => $password ?: null,
                'timeout' => 3,
            ]);
            $client->ping();
        } else {
            throw new RuntimeException(__('message.extension_required_error', ['extension' => 'redis']));
        }
    }

    private function testMemcached(string $host, int $port): void
    {
        if (! extension_loaded('memcached')) {
            throw new RuntimeException(__('message.extension_required_error', ['extension' => 'memcached']));
        }

        $memcached = new Memcached();
        $memcached->addServer($host, $port);

        $stats = $memcached->getStats();

        if (empty($stats) || ! isset($stats[sprintf('%s:%d', $host, $port)])) {
            throw new RuntimeException(sprintf('Could not connect to Memcached at %s:%d', $host, $port));
        }
    }

    private function isConfigured(string $driver): bool
    {
        return match ($driver) {
            'redis' => ! empty(config('database.redis.default.host')),
            'memcached' => ! empty(config('cache.stores.memcached.servers.0.host')),
            'dynamodb' => ! empty(config('filesystems.disks.s3.key')) && ! empty(config('filesystems.disks.s3.secret')),
            default => true, // file, database need no credentials
        };
    }

    /**
     * @return array<mixed>
     */
    private function formFields(string $driver): array
    {
        return match ($driver) {
            'redis' => [
                $this->field('Host', 'REDIS_HOST', config('database.redis.default.host', '127.0.0.1'), required: true),
                $this->field('Port', 'REDIS_PORT', config('database.redis.default.port', 6379), required: true),
                $this->field('Password', 'REDIS_PASSWORD', '', required: false, type: 'password'),
            ],
            'memcached' => [
                $this->field('Host', 'MEMCACHED_HOST', config('cache.stores.memcached.servers.0.host', '127.0.0.1'), required: true),
                $this->field('Port', 'MEMCACHED_PORT', config('cache.stores.memcached.servers.0.port', 11211), required: true),
                $this->field('Persistent ID', 'MEMCACHED_PERSISTENT_ID', config('cache.stores.memcached.persistent_id', ''), required: false),
                $this->field('SASL Username', 'MEMCACHED_USERNAME', config('cache.stores.memcached.sasl.0', ''), required: false),
                $this->field('SASL Password', 'MEMCACHED_PASSWORD', '', required: false, type: 'password'),
            ],
            'dynamodb' => [
                $this->field('AWS Key ID', 'AWS_ACCESS_KEY_ID', config('filesystems.disks.s3.key', ''), required: true),
                $this->field('AWS Secret', 'AWS_SECRET_ACCESS_KEY', '', required: true, type: 'password'),
                $this->field('Region', 'AWS_DEFAULT_REGION', config('filesystems.disks.s3.region', 'us-east-1'), required: true),
                $this->field('Cache Table', 'DYNAMODB_CACHE_TABLE', config('cache.stores.dynamodb.table', 'cache'), required: true),
                $this->field('Endpoint', 'DYNAMODB_ENDPOINT', config('cache.stores.dynamodb.endpoint', ''), required: false),
            ],
            default => [],
        };
    }

    /**
     * @return array<mixed>
     */
    private function field(string $label, string $name, mixed $value, bool $required, string $type = 'text'): array
    {
        return compact('label', 'name', 'value', 'required', 'type');
    }
}
