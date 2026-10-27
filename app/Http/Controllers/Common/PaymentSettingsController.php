<?php

namespace App\Http\Controllers\Common;

use App\ApiKey;
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
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    /**
     * @return array<mixed>
     */
    public function readConfigs(): array|string
    {
        $dir = app_path().DIRECTORY_SEPARATOR.'Plugins'.DIRECTORY_SEPARATOR;
        $plugins = array_filter(
            (array) scandir($dir),
            fn ($file): bool => $file !== '.' && $file !== '..' && is_dir($dir.DIRECTORY_SEPARATOR.$file),
        );

        if ($plugins === []) {
            return 'null';
        }

        // Each plugin folder that ships a config.php, in folder order.
        $config = [];
        foreach ($plugins as $plugin) {
            $path = $dir.$plugin.DIRECTORY_SEPARATOR.'config.php';
            if (file_exists($path)) {
                $config[] = $path;
            }
        }

        return $config;
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

            // Without its webhook secret a gateway's webhooks are rejected, so failed-payment
            // alerts and the backup payment confirmation never arrive. null = no webhooks used.
            $secretColumns = ['stripe' => 'stripe_webhook_secret', 'razorpay' => 'razorpay_webhook_secret'];
            $keys = ApiKey::first(array_values($secretColumns));
            foreach ($configs as &$config) {
                $column = $secretColumns[strtolower((string) $config['name'])] ?? null;
                $config['webhook_configured'] = $column === null ? null : filled($keys?->{$column});
            }
            unset($config);

            return successResponse('', array_values($configs));
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'), 500);
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
