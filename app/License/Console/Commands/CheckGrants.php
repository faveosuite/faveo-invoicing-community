<?php

declare(strict_types=1);

namespace App\License\Console\Commands;

use App\License\Models\License;
use App\Model\Product\Grant;
use App\Services\EntitlementService;
use App\Services\LegacyDates;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Proves the move to grants gives every license the same answer as before:
 *
 *  - lost:   linked to a plugin (license_plugins) but its feature isn't granted — the customer
 *            would lose the plugin. Must be 0.
 *  - dates:  the updates / support grants differ from the license's old date columns (or are
 *            missing). Must be 0.
 *  - gained: a plugin feature is granted with no link (e.g. a plan that bundles it) — shown for review.
 *
 * Usage: php artisan license:check-grants [--show=20]
 */
class CheckGrants extends Command
{
    protected $signature = 'license:check-grants {--show=20 : How many examples of each difference to print}';

    protected $description = 'Compare grants with plugin links and license dates for every license (must report 0 lost and 0 dates)';

    public function handle(EntitlementService $service): int
    {
        $features = DB::table('products as p')->join('entitlements as e', 'e.id', '=', 'p.unlocks_entitlement_id')->pluck('e.code', 'p.id');
        $pluginOf = $features->flip();
        $show = (int) $this->option('show');
        $checked = 0;
        $lost = [];
        $gained = [];
        $dates = [];

        License::chunkById(500, function ($licenses) use ($service, $features, $pluginOf, &$checked, &$lost, &$gained, &$dates): void {
            $ids = $licenses->modelKeys();
            $links = DB::table('license_plugins')->whereIn('license_id', $ids)->get(['license_id', 'product_id'])->groupBy('license_id');
            $dateGrants = Grant::whereIn('license_id', $ids)->where('source', Grant::DATE)->get()->groupBy('license_id');
            $withGrants = Grant::whereIn('license_id', $ids)->where('source', '!=', Grant::DATE)->distinct()->pluck('license_id')->flip();

            foreach ($licenses as $license) {
                $checked++;
                $have = collect($dateGrants[$license->id] ?? [])->mapWithKeys(fn (Grant $g): array => [$g->code => $g->ends_at?->toDateString()]);
                foreach (LegacyDates::COLUMNS as $code => [$column]) {
                    $expected = EntitlementService::day($license->{$column});
                    if (! $have->has($code) || $have[$code] !== $expected) {
                        $dates[] = "license {$license->id} ({$license->license_code}): {$code} ".($have->has($code) ? "grant {$have[$code]}" : 'grant missing').", column {$expected}";
                    }
                }

                $linked = collect($links[$license->id] ?? [])->pluck('product_id')->all();
                if ($linked === [] && ! isset($withGrants[$license->id])) {
                    continue;
                }
                $granted = array_keys(array_filter($service->resolve($license), fn ($value): bool => $value === true));
                foreach ($linked as $pluginId) {
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

        $this->info("Licenses checked: {$checked}");
        $this->report('Lost (must be 0)', $lost, $show, 'error');
        $this->report('Dates (must be 0)', $dates, $show, 'error');
        $this->report('Gained (review)', $gained, $show, 'warn');

        return $lost === [] && $dates === [] ? self::SUCCESS : self::FAILURE;
    }

    /** @param list<string> $lines */
    private function report(string $title, array $lines, int $show, string $style): void
    {
        $this->line($title.': '.count($lines));
        foreach (array_slice($lines, 0, $show) as $line) {
            $this->{$style}("  {$line}");
        }
    }
}
