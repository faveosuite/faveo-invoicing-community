<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use CacheDriver\HandleCacheController;
use Illuminate\Cache\RedisStore;
use Illuminate\Contracts\Redis\Factory;
use Illuminate\Http\Request;

class CacheController extends Controller
{
    public $drivers;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $this->drivers = collect([
            ['driver' => 'file', 'name' => 'File', 'config' => false],
            ['driver' => 'database', 'name' => 'Database', 'config' => false],
            ['driver' => 'redis', 'name' => 'Redis', 'config' => true, 'tooltip' => 'cache_redis_tooltip'],
        ]);
    }

    public function index()
    {
        try {
            return view('themes.default1.cache.index');
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getCacheDrivers()
    {
        try {
            $currentDriver = config('cache.default');

            $drivers = $this->drivers->map(function ($driver) use ($currentDriver) {
                $driver['status'] = $currentDriver === $driver['driver'];

                return $driver;
            });

            return \DataTables::of($drivers)
                ->addColumn('name', function ($driver) {
                    $html = $driver['name'];

                    if (isset($driver['tooltip'])) {
                        $tooltip = __('message.'.$driver['tooltip']);
                        $html .= ' <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="'.$tooltip.'"></i>';
                    }

                    return $html;
                })
                ->addColumn('status', function ($driver) {
                    if ($driver['status']) {
                        return "<span class='badge badge-primary' style='background-color:darkcyan !important;'>".__('message.active').'</span>';
                    }

                    return "<span class='badge badge-primary' style='background-color:crimson !important;'>".__('message.inactive').'</span>';
                })
                ->addColumn('action', function ($driver) {
                    $html = '';

                    if ($driver['config']) {
                        $title = $driver['status'] ? __('message.configured') : __('message.configure');
                        $html .= '<a href="'.route('cache.edit', $driver['driver']).'" class="btn btn-default table_btn" data-toggle="tooltip" data-placement="top" title="'.$title.'"><i class="fas fa-cog"></i></a>';
                    } elseif ($driver['status']) {
                        $html .= '<button class="btn btn-default table_btn" disabled data-toggle="tooltip" data-placement="top" title="'.__('message.activated').'"><i class="fas fa-check-circle"></i></button>';
                    } else {
                        $html .= '<button class="btn btn-default table_btn" onclick="activateCacheDriver(\''.$driver['driver'].'\')" data-toggle="tooltip" data-placement="top" title="'.__('message.activate').'"><i class="fas fa-check-circle"></i></button>';
                    }

                    return $html;
                })
                ->rawColumns(['name', 'status', 'action'])
                ->make(true);
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function edit($driver)
    {
        try {
            $driverConfig = $this->drivers->firstWhere('driver', $driver);

            if (! $driverConfig || ! $driverConfig['config']) {
                return redirect()->route('cache.index')->with('fails', __('message.invalid_cache_driver'));
            }

            $config = HandleCacheController::all($driver);
            $currentValues = [
                'connection_redis' => HandleCacheController::value('CONNECTION_REDIS', 'default'),
            ];

            return view('themes.default1.cache.edit', compact('driver', 'driverConfig', 'config', 'currentValues'));
        } catch (\Exception $ex) {
            return redirect()->route('cache.index')->with('fails', $ex->getMessage());
        }
    }

    public function update(Request $request, $driver)
    {
        try {
            $driverConfig = $this->drivers->firstWhere('driver', $driver);

            if (! $driverConfig || ! $driverConfig['config']) {
                return errorResponse(__('message.invalid_cache_driver'));
            }

            if ($driver === 'redis') {
                $request->validate([
                    'connection_redis' => 'required|string',
                ]);

                $this->checkRedisConnection($request->all());
            }

            $cache = new HandleCacheController();

            $data = array_merge(
                ['default' => $driver],
                $request->except('_token')
            );

            $cache->modify($data);

            return successResponse(__('message.cache_driver_updated'));
        } catch (\Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function activate(Request $request, $driver)
    {
        try {
            $driverConfig = $this->drivers->firstWhere('driver', $driver);

            if (! $driverConfig) {
                return errorResponse(__('message.invalid_cache_driver'));
            }

            // Redis requires configuration before activation
            if ($driverConfig['config'] && $driver === 'redis') {
                $connectionRedis = HandleCacheController::value('CONNECTION_REDIS');
                if (empty($connectionRedis)) {
                    return errorResponse(__('message.activate_configure_first', ['name' => $driverConfig['name']]));
                }

                $this->checkRedisConnection([
                    'default' => 'redis',
                    'connection_redis' => $connectionRedis,
                ]);
            }

            $cache = new HandleCacheController();
            $cache->modify(['default' => $driver]);

            return successResponse(__('message.activated_successfully', ['name' => $driverConfig['name']]));
        } catch (\Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    private function checkRedisConnection($args)
    {
        $connection = checkArray('connection_redis', $args);

        throw_if(
            ! extension_loaded('redis') && ! class_exists(\Predis\Client::class),
            new \Exception(__('message.redis_extension_not_installed'))
        );

        new RedisStore(app(Factory::class), '', $connection ?: 'cache')->put('test', 'test', 100);
    }
}
