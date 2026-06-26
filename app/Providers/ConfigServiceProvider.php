<?php

namespace App\Providers;

use DB;
use Exception;
use Illuminate\Support\Facades\Crypt;
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

        $this->loadAppConfig();
        $this->overrideCloudConfigFromDb();
    }

    private function loadAppConfig(): void
    {
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

            $bool = fn (string $key): bool => (bool) ($rows->get($key)->option_value ?? false);
            $debugOn = $bool('debugging:app_debug');

            config([
                'app.debug'                   => $debugOn,
                'debugbar.force_allow_enable'  => $debugOn, // Debugbar v4 blocks itself in non-local envs
                'pulse.enabled'               => $bool('debugging:pulse_enabled'),
                'clockwork.enable'            => $bool('debugging:clockwork_enable'),
                'app.sentry_reporting'        => $bool('sentry:crash_reporting'),
                'sentry.traces_sample_rate'   => $rows->get('sentry:performance_monitoring')?->option_value ? 0.1 : 0,
            ]);

            if ($cacheDriver = $rows->get('cache:driver')?->option_value) {
                config(['cache.default' => $cacheDriver]);
            }
        } catch (Throwable) {
            // Fall back to .env values — app still boots correctly
        }
    }

    private function overrideCloudConfigFromDb(): void
    {
        try {
            $cloud = DB::table('faveo_cloud')->where('id', 1)->first();
            if (! $cloud) {
                return;
            }

            $plain = [
                'cloud_job_url'               => 'custom.cloud_job_url',
                'cloud_job_url_normal'        => 'custom.cloud_job_url_normal',
                'cloud_user'                  => 'custom.cloud_user',
                'cloud_delete_job_url_normal' => 'custom.cloud_delete_job_url_normal',
                'cloud_delete_job_url_custom' => 'custom.cloud_delete_job_url_custom',
            ];

            $encrypted = [
                'cloud_auth'          => 'custom.cloud_auth',
                'cloud_oauth_token'   => 'custom.cloud_oauth_token',
                'google_chat_webhook' => 'custom.google_chat',
            ];

            foreach ($plain as $column => $configKey) {
                if (filled($cloud->{$column})) {
                    config([$configKey => $cloud->{$column}]);
                }
            }

            foreach ($encrypted as $column => $configKey) {
                if (filled($cloud->{$column})) {
                    config([$configKey => Crypt::decrypt($cloud->{$column})]);
                }
            }
        } catch (Throwable) {
            // DB unavailable, or decryption failed — fall back to ENV values
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
