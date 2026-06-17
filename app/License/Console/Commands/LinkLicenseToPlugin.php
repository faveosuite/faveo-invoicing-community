<?php

namespace App\License\Console\Commands;

use App\License\Models\License;
use Illuminate\Console\Command;

class LinkLicenseToPlugin extends Command
{
    protected $signature = 'link:license-to-plugin {--product=} {--plugin=}';

    protected $description = 'Will link all the existing licenses to the plugins';

    public function handle(): int
    {
        $productOption = $this->option('product');
        $pluginOption = $this->option('plugin');

        if (! $productOption || ! $pluginOption) {
            $this->error('Both --product and --plugin options are required.');

            return 1;
        }

        $products = array_filter(explode(',', $productOption));
        $plugins = array_filter(explode(',', $pluginOption));

        if ($products === [] || $plugins === []) {
            $this->error('Invalid product or plugin values.');

            return 1;
        }

        foreach ($products as $product) {
            $licenses = License::where('product_id', $product)->get();

            if ($licenses->isEmpty()) {
                $this->warn('No licenses found for product ID: '.$product);
                continue;
            }

            foreach ($licenses as $license) {
                $license->licensePlugins()->createMany(
                    collect($plugins)->map(fn ($plugin): array => ['product_id' => $plugin])->all()
                );
            }
        }

        $this->info('Licenses successfully linked to plugins.');

        return 0;
    }
}
