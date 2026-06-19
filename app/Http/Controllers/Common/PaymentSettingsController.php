<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Model\Plugin;
use Exception;
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
    public function fetchConfig(): array|\Illuminate\Http\JsonResponse
    {
        $configs = $this->readConfigs();

        $plugs = new Plugin();
        $fields = [];
        $attributes = [];
        try {
            if ($configs != 'null') {
                foreach ($configs as $key => $config) {
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
                //opendir($dir);
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

    public function statusPlugin(string $slug): mixed
    {
        $plugs = new Plugin();
        $plug = $plugs->where('name', $slug)->first();

        if (! $plug) {
            $app = base_path().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'app.php';
            $str = '
\'App\Plugins\\'.$slug."\\ServiceProvider',";
            $line_i_am_looking_for = 102;
            $lines = file($app, FILE_IGNORE_NEW_LINES);
            $lines[$line_i_am_looking_for] = $str;
            file_put_contents($app, implode("\n", $lines));
            $plugs->create(['name' => $slug, 'path' => $slug, 'status' => 1]);

            return back()->with('success', __('message.status_change'));
        }

        $status = $plug->status;

        if ($status == 0) {
            $plug->status = 1;

            $app = base_path().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'app.php';
            $str = '
\'App\Plugins\\'.$slug."\\ServiceProvider',";
            $line_i_am_looking_for = 102;
            $lines = file($app, FILE_IGNORE_NEW_LINES);
            $lines[$line_i_am_looking_for] = $str;
            file_put_contents($app, implode("\n", $lines));
        } elseif ($status == 1) {
            $plug->status = 0;
            /*
             * remove service provider from app.php
             */
            $str = '
\'App\Plugins\\'.$slug."\\ServiceProvider',";
            $path_to_file = base_path().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'app.php';

            $file_contents = (string) file_get_contents($path_to_file);
            $file_contents = str_replace($str, '//', $file_contents);
            file_put_contents($path_to_file, $file_contents);
        }

        $plug->save();

        return back()->with('success', __('message.status_change'));
    }

    public function updatePaymentStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $plugs = new Plugin();
        $name = $request->input('name');
        $status = $request->input('status');
        $plug = $plugs->where('name', $name)->first();
        $app = base_path().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'app.php';
        $str = '
\'App\Plugins\\'.$name."\\ServiceProvider',";
        $line_i_am_looking_for = 102;
        $lines = file($app, FILE_IGNORE_NEW_LINES);
        $lines[$line_i_am_looking_for] = $str;
        if (! $plug) {
            file_put_contents($app, implode("\n", $lines));
            $plugs->create(['name' => $name, 'path' => $name, 'status' => 1]);

            return successResponse(__('message.status_change'));
        }

        if ($status) {
            $plug->status = 1;
            file_put_contents($app, implode("\n", $lines));
        } else {
            $plug->status = 0;
            $file_contents = (string) file_get_contents($app);
            $file_contents = str_replace($str, '//', $file_contents);
            file_put_contents($app, $file_contents);
        }

        $plug->save();

        return successResponse(__('message.status_change'));
    }

    public function getPaymentGatewayList(): \Illuminate\Http\JsonResponse
    {
        try {
            $configs = $this->fetchConfig();
            if ($configs instanceof \Illuminate\Http\JsonResponse) {
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

            foreach ($values as $plugin) {
                $name = strtolower((string) $plugin['name']);
                $pluginMap[$name] = [
                    'supported_currencies' => $plugin['supported_currencies'] ?? [],
                ];
            }
        }

        return $pluginMap;
    }
}
