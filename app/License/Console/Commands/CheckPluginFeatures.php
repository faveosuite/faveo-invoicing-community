<?php

declare(strict_types=1);

namespace App\License\Console\Commands;

use App\License\Models\License;
use App\Services\EntitlementService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Proves the move from plugin links (license_plugins) to features is safe before access
 * switches over: for every license, the old answer ("linked to plugin X") must match the new one
 * ("the license has X's feature").
 *
 *  - lost:   linked today but the feature isn't granted (the customer would lose the plugin) — must be 0
 *  - gained: the feature is granted but there was no link (e.g. a plan that bundles the plugin
 *            but the license was never linked) — shown so it can be reviewed
 *
 * Usage: php artisan license:check-plugin-features [--show=20]
 */
class CheckPluginFeatures extends Command
{
    protected $signature = 'license:check-plugin-features {--show=20 : How many examples of each difference to print}';

    protected $description = 'Compare plugin links with plugin features for every license (must report 0 lost)';

    public function handle(EntitlementService $service): int
    {
        $features = DB::table('products as p')->join('entitlements as e', 'e.id', '=', 'p.unlocks_entitlement_id')->pluck('e.code', 'p.id');
        $pluginOf = $features->flip();
        $show = (int) $this->option('show');
        $checked = 0;
        $lost = [];
        $gained = [];

        License::where(fn ($q) => $q->whereIn('id', DB::table('license_plugins')->select('license_id'))
            ->orWhereNotNull('entitlement_base')->orWhereNotNull('entitlement_overrides'))
            ->chunkById(500, function ($licenses) use ($service, $features, $pluginOf, &$checked, &$lost, &$gained): void {
                $links = DB::table('license_plugins')->whereIn('license_id', $licenses->modelKeys())->get(['license_id', 'product_id'])->groupBy('license_id');
                foreach ($licenses as $license) {
                    $granted = array_keys(array_filter($service->resolve($license), fn ($value): bool => $value === true));
                    $linked = collect($links[$license->id] ?? [])->pluck('product_id')->all();

                    foreach ($linked as $pluginId) {
                        $checked++;
                        $code = $features[$pluginId] ?? null;
                        if ($code === null || ! in_array($code, $granted, true)) {
                            $lost[] = "license {$license->id} ({$license->license_code}): plugin {$pluginId} ".($code === null ? 'has no feature' : "feature {$code} not granted");
                        }
                    }
                    foreach (array_intersect($granted, $features->all()) as $code) {
                        if (! in_array($pluginOf[$code], $linked)) {
                            $gained[] = "license {$license->id} ({$license->license_code}): {$code} granted, no link";
                        }
                    }
                }
            });

        $this->info("Plugin links checked: {$checked}");
        $this->line('Lost (must be 0): '.count($lost));
        foreach (array_slice($lost, 0, $show) as $line) {
            $this->error("  {$line}");
        }
        $this->line('Gained (review): '.count($gained));
        foreach (array_slice($gained, 0, $show) as $line) {
            $this->warn("  {$line}");
        }

        return $lost === [] ? self::SUCCESS : self::FAILURE;
    }
}
