<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Model\Plugin;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * @return array<mixed>
     */
    public function fetchConfig(): array|JsonResponse
    {
        $configs = $this->readConfigs();

        $plugs = new Plugin;
        $fields = [];
        $attributes = [];
        try {
            if ($configs != 'null') {
                foreach ((array) $configs as $key => $config) {
                    $fields[$key] = include $config;
                }
            }

            foreach ($fields as $key => $field) {
                $plug = $plugs->where('name', $field['name'])->select(['path', 'status'])->orderBy('name')->get();

                if ($plug->isNotEmpty()) {
                    foreach ($plug as $value) {
                        $attributes[$key]['path'] = $value['path'];
                        $attributes[$key]['status'] = $value['status'];
                    }
                } else {
                    $attributes[$key]['path'] = $field['name'];
                    $attributes[$key]['status'] = 0;
                }

                $attributes[$key]['name'] = $field['name'];
                $attributes[$key]['settings'] = $field['settings'];
                $attributes[$key]['description'] = $field['description'];
                $attributes[$key]['website'] = $field['website'];
                $attributes[$key]['version'] = $field['version'];
                $attributes[$key]['author'] = $field['author'];
                $attributes[$key]['supported_currencies'] = $field['supported_currencies'];
            }

            return $attributes;
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * @return array<mixed>
     */
    public function readConfigs(): array|string
    {
        $dir = app_path().DIRECTORY_SEPARATOR.'Plugins'.DIRECTORY_SEPARATOR;
        $directories = scandir($dir);
        $files = [];
        foreach ($directories as $key => $file) {
            if ($file === '.') {
                continue;
            }

            if ($file === '..') {
                continue;
            }

            if (is_dir($dir.DIRECTORY_SEPARATOR.$file)) {
                $files[$key] = $file;
            }
        }

        $config = [];
        $plugins = [];
        if ($files !== []) {
            foreach ($files as $key => $file) {
                $plugin = $dir.$file;
                $plugins[$key] = array_diff(scandir($plugin), ['.', '..', 'ServiceProvider.php']);
                $plugins[$key]['file'] = $plugin;
            }

            foreach ($plugins as $plugin) {
                $dir = $plugin['file'];
                if ($dh = opendir($dir)) {
                    while (($file = readdir($dh)) !== false) {
                        if ($file === 'config.php') {
                            $config[] = $dir.DIRECTORY_SEPARATOR.$file;
                        }
                    }

                    closedir($dh);
                }
            }

            return $config;
        }

        return 'null';
    }

    public function updatePaymentStatus(Request $request): JsonResponse
    {
        Plugin::updateOrCreate(
            ['name' => $request->name],
            [
                'path' => $request->name,
                'status' => (bool) $request->status,
            ]
        );

        return successResponse(__('message.status_change'));
    }

    public function getPaymentGatewayList(): JsonResponse
    {
        try {
            $configs = $this->fetchConfig();
            if ($configs instanceof JsonResponse) {
                return $configs;
            }

            return successResponse('', array_values($configs));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage(), 500);
        }
    }

    /**
     * @return array<mixed>
     */
    public function getPaymentPluginMap(): array
    {
        static $pluginMap = null;

        if ($pluginMap === null) {
            $values = $this->fetchConfig();
            $pluginMap = [];

            foreach (is_array($values) ? $values : [] as $plugin) {
                $name = strtolower((string) $plugin['name']);
                $pluginMap[$name] = [
                    'supported_currencies' => $plugin['supported_currencies'] ?? [],
                ];
            }
        }

        return $pluginMap;
    }
}
