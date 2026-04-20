<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Sentry\State\HubInterface;

class ConfigServiceProvider extends ServiceProvider
{
    public function register()
    {
        config([
            //
        ]);
    }

    public function boot()
    {
        if (! isInstall()) {
            return;
        }

        try {
            $settings = \Cache::rememberForever('debugging_settings', function () {
                return [
                    'app.debug'                 => (bool) commonSettings('debugging', 'app_debug'),
                    'pulse.enabled'             => (bool) commonSettings('debugging', 'pulse_enabled'),
                    'clockwork.enable'          => (bool) commonSettings('debugging', 'clockwork_enable'),
                    'app.sentry_reporting'      => (bool) commonSettings('sentry', 'crash_reporting'),
                    'sentry.traces_sample_rate' => commonSettings('sentry', 'performance_monitoring') ? 0.1 : 0,
                ];
            });

            config($settings);

            $tracesRate = $settings['sentry.traces_sample_rate'];
            if ($this->app->bound(HubInterface::class)) {
                $client = $this->app->make(HubInterface::class)->getClient();
                if ($client !== null) {
                    $client->getOptions()->setTracesSampleRate($tracesRate ?: null);
                }
            }

        } catch (\Exception $e) {
            \Log::warning('ConfigServiceProvider: failed to load debugging settings — ' . $e->getMessage());
        }
    }
}