<?php

namespace App\License\tests\Backend\Console;

use App\License\Models\License;
use App\License\tests\Backend\LicenseTestCase;
use App\Model\Configure\ProductPluginGroup;
use App\Model\Product\Product;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('link-license-to-plugin')]
class LinkLicenseToPluginTest extends LicenseTestCase
{
    private Product $product;

    private Product $mappedPlugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = $this->createProduct();
        $this->mappedPlugin = $this->createProduct();
        ProductPluginGroup::create(['product_id' => $this->product->id, 'plugin_id' => $this->mappedPlugin->id]);
    }

    // Has its own mapped plugin, so it would get linked if the --product filter leaked.
    private function licenseOfOtherProduct(): License
    {
        $otherProduct = $this->createProduct();
        ProductPluginGroup::create(['product_id' => $otherProduct->id, 'plugin_id' => $this->createProduct()->id]);

        return $this->createLicense(['product_id' => $otherProduct->id]);
    }

    private function pluginIds(License $license): array
    {
        return $license->addonProducts()->pluck('products.id')->sort()->values()->all();
    }

    #[Test]
    public function product_option_links_mapped_plugins_to_every_license_of_that_product_only(): void
    {
        $first = $this->createLicense(['product_id' => $this->product->id]);
        $second = $this->createLicense(['product_id' => $this->product->id]);
        $otherProduct = $this->licenseOfOtherProduct();

        $this->artisan('link:license-to-plugin', ['--product' => $this->product->id])
            ->expectsOutput('Added 2 plugin links across 2 licenses.')
            ->assertExitCode(0);

        $this->assertSame([$this->mappedPlugin->id], $this->pluginIds($first));
        $this->assertSame([$this->mappedPlugin->id], $this->pluginIds($second));
        $this->assertSame([], $this->pluginIds($otherProduct));
    }

    #[Test]
    public function license_option_links_only_the_given_licenses(): void
    {
        $first = $this->createLicense(['product_id' => $this->product->id]);
        $second = $this->createLicense(['product_id' => $this->product->id]);
        $untouched = $this->createLicense(['product_id' => $this->product->id]);

        $this->artisan('link:license-to-plugin', ['--license' => $first->license_code.', '.$second->license_code])
            ->expectsOutput('Added 2 plugin links across 2 licenses.')
            ->assertExitCode(0);

        $this->assertSame([$this->mappedPlugin->id], $this->pluginIds($first));
        $this->assertSame([$this->mappedPlugin->id], $this->pluginIds($second));
        $this->assertSame([], $this->pluginIds($untouched));
    }

    #[Test]
    public function plugin_option_adds_only_the_given_plugin_even_if_not_mapped(): void
    {
        $extraPlugin = $this->createProduct();
        $license = $this->createLicense(['product_id' => $this->product->id]);

        $this->artisan('link:license-to-plugin', ['--license' => $license->license_code, '--plugin' => $extraPlugin->id])
            ->assertExitCode(0);

        $this->assertSame([$extraPlugin->id], $this->pluginIds($license));
    }

    #[Test]
    public function product_and_license_together_link_only_licenses_matching_both(): void
    {
        $matching = $this->createLicense(['product_id' => $this->product->id]);
        $sameProduct = $this->createLicense(['product_id' => $this->product->id]);
        $otherProduct = $this->licenseOfOtherProduct();

        $this->artisan('link:license-to-plugin', [
            '--product' => $this->product->id,
            '--license' => $matching->license_code.','.$otherProduct->license_code,
        ])->assertExitCode(0);

        $this->assertSame([$this->mappedPlugin->id], $this->pluginIds($matching));
        $this->assertSame([], $this->pluginIds($sameProduct));
        $this->assertSame([], $this->pluginIds($otherProduct));
    }

    #[Test]
    public function rerun_skips_existing_links_without_duplicating(): void
    {
        $license = $this->createLicense(['product_id' => $this->product->id]);

        $this->artisan('link:license-to-plugin', ['--product' => $this->product->id])->assertExitCode(0);
        $this->artisan('link:license-to-plugin', ['--product' => $this->product->id])
            ->expectsOutput('Added 0 plugin links across 1 licenses.')
            ->assertExitCode(0);

        $this->assertSame(1, $license->addonProducts()->count());
    }

    #[Test]
    public function product_without_mapped_plugins_links_nothing_and_warns(): void
    {
        $license = $this->createLicense();

        $this->artisan('link:license-to-plugin', ['--product' => $license->product_id])
            ->expectsOutput('No licenses (or no mapped plugins) found for the given options.')
            ->assertExitCode(0);

        $this->assertSame([], $this->pluginIds($license));
    }

    #[Test]
    public function fails_without_product_or_license(): void
    {
        $this->artisan('link:license-to-plugin', ['--plugin' => $this->mappedPlugin->id])
            ->expectsOutput('Give --product or --license.')
            ->assertExitCode(1);
    }

    #[Test]
    public function unknown_plugin_id_fails_before_linking_anything(): void
    {
        $license = $this->createLicense(['product_id' => $this->product->id]);

        $this->artisan('link:license-to-plugin', [
            '--license' => $license->license_code,
            '--plugin' => $this->mappedPlugin->id.',999999999',
        ])
            ->expectsOutput('Unknown plugin IDs: 999999999')
            ->assertExitCode(1);

        $this->assertSame([], $this->pluginIds($license));
    }
}
