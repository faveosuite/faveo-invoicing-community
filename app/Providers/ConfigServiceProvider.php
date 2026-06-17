<?php

namespace App\Providers;

use DB;
use Exception;
use Illuminate\Support\ServiceProvider;
use Log;
use Override;
use Sentry\State\HubInterface;
use Throwable;

class ConfigServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        if (! isInstall()) {
            return;
        }

        try {
            // Runs in register() (not boot()) so Debugbar, Clockwork, and Pulse read the
            // correct values when their own boot() methods run — all register() calls
            // complete before any boot() is invoked.
            //
            // \DB::table() instead of Eloquent: Model::$resolver is null until
            // DatabaseServiceProvider::boot(), which hasn't run yet at this stage.
            $rows = DB::table('common_settings')
                ->whereIn('option_name', ['debugging', 'sentry', 'cache'])
                ->get()
                ->keyBy(fn ($r): string => sprintf('%s:%s', $r->option_name, $r->optional_field));

            $bool = fn (string $key): bool => (bool) ($rows->get($key)?->option_value ?? false);
            $debugOn = $bool('debugging:app_debug');

            config([
                'app.debug' => $debugOn,
                'debugbar.force_allow_enable' => $debugOn, // Debugbar v4 blocks itself in non-local envs
                'pulse.enabled' => $bool('debugging:pulse_enabled'),
                'clockwork.enable' => $bool('debugging:clockwork_enable'),
                'app.sentry_reporting' => $bool('sentry:crash_reporting'),
                'sentry.traces_sample_rate' => $rows->get('sentry:performance_monitoring')?->option_value ? 0.1 : 0,
            ]);

            if ($cacheDriver = $rows->get('cache:driver')?->option_value) {
                config(['cache.default' => $cacheDriver]);
            }
        } catch (Throwable) {
            // Fall back to .env values — app still boots correctly
        }
    }

    public function boot(): void
    {
        if (! isInstall()) {
            return;
        }

        try {
            if ($this->app->bound(HubInterface::class)) {
                $this->app->make(HubInterface::class)
                    ->getClient()
                    ?->getOptions()
                    ->setTracesSampleRate(config('sentry.traces_sample_rate') ?: null);
            }
        } catch (Exception $exception) {
            Log::warning('ConfigServiceProvider: Sentry config failed — '.$exception->getMessage());
        }
    }
}
