<?php

namespace App\License\Console\Commands;

use App\License\Models\License;
use App\Model\Product\Product;
use Illuminate\Console\Command;

/**
 * Backfills plugin access onto licenses that already exist.
 *
 * A new purchase links the product's mapped plugins (product_plugin_group) to its
 * license automatically. Licenses created before a plugin was mapped — or migrated
 * from the old license server — miss those links; run this to add them.
 *
 * Usage:
 *   php artisan link:license-to-plugin --product=1
 *       All licenses of product 1 get product 1's mapped plugins.
 *
 *   php artisan link:license-to-plugin --license=CODE1,CODE2
 *       Only these licenses get their own product's mapped plugins.
 *
 *   php artisan link:license-to-plugin --product=1 --plugin=10
 *   php artisan link:license-to-plugin --license=CODE1 --plugin=10
 *       Only plugin 10 is added (even if it isn't mapped to the product).
 *
 * --product or --license is required; given together, a license must match both.
 * Only adds links, never removes; safe to re-run (existing links are skipped).
 */
class LinkLicenseToPlugin extends Command
{
    protected $signature = 'link:license-to-plugin'
        .' {--product= : Comma-separated product IDs; links all their licenses}'
        .' {--license= : Comma-separated license codes; links only these licenses}'
        .' {--plugin= : Comma-separated plugin product IDs; omit to use each product\'s mapped plugins}';

    protected $description = 'Link existing licenses to plugins (the product\'s mapped plugins by default)';

    public function handle(): int
    {
        $productOption = $this->option('product');
        $licenseOption = $this->option('license');
        $pluginOption = $this->option('plugin');

        $products = $this->csv($productOption);
        $codes = $this->csv($licenseOption);
        $plugins = $this->csv($pluginOption);

        if ($products === [] && $codes === []) {
            $this->error('Give --product or --license.');

            return 1;
        }

        // A bad ID would hit the foreign key mid-run and leave some licenses linked, others not.
        $unknown = array_diff($plugins, Product::whereIn('id', $plugins)->pluck('id')->map(fn ($id): string => (string) $id)->all());
        if ($unknown !== []) {
            $this->error('Unknown plugin IDs: '.implode(',', $unknown));

            return 1;
        }

        $licenses = 0;
        $links = 0;

        License::with('product.bundledPlugins:id')
            ->when($products !== [], fn ($query) => $query->whereIn('product_id', $products))
            ->when($codes !== [], fn ($query) => $query->whereIn('license_code', $codes))
            ->chunkById(200, function ($chunk) use ($plugins, &$licenses, &$links): void {
                foreach ($chunk as $license) {
                    $ids = $plugins !== [] ? $plugins : $license->product->bundledPlugins->pluck('id')->all();

                    if ($ids === []) {
                        continue;
                    }

                    $links += count($license->addonProducts()->syncWithoutDetaching($ids)['attached']);
                    $licenses++;
                }
            });

        if ($licenses === 0) {
            $this->warn('No licenses (or no mapped plugins) found for the given options.');

            return 0;
        }

        $this->info("Added {$links} plugin links across {$licenses} licenses.");

        return 0;
    }

    /**
     * @return list<string>
     */
    private function csv(mixed $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }
}
