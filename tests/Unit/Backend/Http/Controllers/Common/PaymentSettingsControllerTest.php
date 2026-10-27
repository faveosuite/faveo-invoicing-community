<?php

namespace Tests\Unit\Backend\Http\Controllers\Common;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class PaymentSettingsControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    public function test_get_payment_gateway_list_returns_200(): void
    {
        $response = $this->getJson('/payment-gateway-list');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_gateway_list_flags_a_missing_webhook_secret(): void
    {
        \App\ApiKey::query()->update(['stripe_webhook_secret' => 'whsec_test', 'razorpay_webhook_secret' => '']);

        $gateways = collect($this->getJson('/payment-gateway-list')->assertOk()->json('data'))->keyBy('name');

        $this->assertTrue($gateways['Stripe']['webhook_configured']);
        $this->assertFalse($gateways['Razorpay']['webhook_configured']);
    }

    public function test_fetch_config_can_be_called_directly(): void
    {
        $controller = new \App\Http\Controllers\Common\PaymentSettingsController;

        $result = $controller->fetchConfig();

        $this->assertIsArray($result);
    }

    public function test_get_payment_plugin_map_returns_array(): void
    {
        $controller = new \App\Http\Controllers\Common\PaymentSettingsController;

        $result = $controller->getPaymentPluginMap();

        $this->assertIsArray($result);
    }

    public function test_read_configs_returns_result(): void
    {
        $controller = new \App\Http\Controllers\Common\PaymentSettingsController;

        $result = $controller->readConfigs();

        $this->assertTrue(is_array($result) || is_string($result));
    }

    /** readConfigs() against a throwaway app path whose Plugins folder holds $layout (dir => has config.php). */
    private function readConfigsFrom(array $layout, array $looseFiles = []): array|string
    {
        $app = sys_get_temp_dir().'/plugins_'.uniqid();
        \Illuminate\Support\Facades\File::makeDirectory($app.'/Plugins', recursive: true);
        foreach ($layout as $plugin => $hasConfig) {
            \Illuminate\Support\Facades\File::makeDirectory($app.'/Plugins/'.$plugin);
            \Illuminate\Support\Facades\File::put($app.'/Plugins/'.$plugin.'/ServiceProvider.php', '<?php');
            if ($hasConfig) {
                \Illuminate\Support\Facades\File::put($app.'/Plugins/'.$plugin.'/config.php', '<?php return [];');
            }
        }
        foreach ($looseFiles as $file) {
            \Illuminate\Support\Facades\File::put($app.'/Plugins/'.$file, 'x');
        }

        $original = app_path();
        $this->app->useAppPath($app);
        try {
            $result = (new \App\Http\Controllers\Common\PaymentSettingsController)->readConfigs();
        } finally {
            $this->app->useAppPath($original);
            \Illuminate\Support\Facades\File::deleteDirectory($app);
        }

        return is_array($result) ? array_map(fn ($path) => str_replace($app, 'APP', $path), $result) : $result;
    }

    public function test_read_configs_lists_each_plugin_config_in_folder_order(): void
    {
        $this->assertSame(
            ['APP/Plugins/Razorpay/config.php', 'APP/Plugins/Stripe/config.php'],
            $this->readConfigsFrom(['Stripe' => true, 'Helper' => false, 'Razorpay' => true], ['notes.txt']),
        );
    }

    public function test_read_configs_is_an_empty_list_without_configs_and_null_without_plugins(): void
    {
        $this->assertSame([], $this->readConfigsFrom(['Helper' => false]));
        $this->assertSame('null', $this->readConfigsFrom([], ['notes.txt']));
    }
}
