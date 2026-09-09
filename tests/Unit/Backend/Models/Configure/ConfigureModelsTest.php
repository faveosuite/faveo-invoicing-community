<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models\Configure;

use App\Model\Configure\ConfigGroup;
use App\Model\Configure\ConfigOption;
use App\Model\Configure\ConfigOptionValue;
use App\Model\Configure\PluginCompatibleWithProducts;
use App\Model\Configure\ProductPluginGroup;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class ConfigureModelsTest extends TestCase
{
    // ───────────── ConfigGroup ─────────────

    public function test_config_group_table_name(): void
    {
        $this->assertSame('config_group', (new ConfigGroup())->getTable());
    }

    public function test_config_group_guarded_is_empty(): void
    {
        $this->assertSame([], (new ConfigGroup())->getGuarded());
    }

    public function test_config_group_config_options_relation(): void
    {
        $this->assertInstanceOf(HasMany::class, (new ConfigGroup())->configOptions());
    }

    // ───────────── ConfigOption ─────────────

    public function test_config_option_table_name(): void
    {
        $this->assertSame('config_option', (new ConfigOption())->getTable());
    }

    public function test_config_option_guarded_is_empty(): void
    {
        $this->assertSame([], (new ConfigOption())->getGuarded());
    }

    public function test_config_option_config_group_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new ConfigOption())->configGroup());
    }

    public function test_config_option_plan_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new ConfigOption())->plan());
    }

    public function test_config_option_config_option_values_relation(): void
    {
        $this->assertInstanceOf(HasMany::class, (new ConfigOption())->configOptionValues());
    }

    public function test_config_option_product_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new ConfigOption())->product());
    }

    // ───────────── ConfigOptionValue ─────────────

    public function test_config_option_value_table_name(): void
    {
        $this->assertSame('config_option_values', (new ConfigOptionValue())->getTable());
    }

    public function test_config_option_value_guarded_is_empty(): void
    {
        $this->assertSame([], (new ConfigOptionValue())->getGuarded());
    }

    public function test_config_option_value_config_option_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new ConfigOptionValue())->configOption());
    }

    // ───────────── PluginCompatibleWithProducts ─────────────

    public function test_plugin_compatible_with_products_table_name(): void
    {
        $this->assertSame('plugin_compatible_with_products', (new PluginCompatibleWithProducts())->getTable());
    }

    public function test_plugin_compatible_with_products_guarded_is_empty(): void
    {
        $this->assertSame([], (new PluginCompatibleWithProducts())->getGuarded());
    }

    public function test_plugin_compatible_with_products_product_comp_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new PluginCompatibleWithProducts())->productComp());
    }

    public function test_plugin_compatible_with_products_plugin_comp_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new PluginCompatibleWithProducts())->pluginComp());
    }

    // ───────────── ProductPluginGroup ─────────────

    public function test_product_plugin_group_table_name(): void
    {
        $this->assertSame('product_plugin_group', (new ProductPluginGroup())->getTable());
    }

    public function test_product_plugin_group_guarded_is_empty(): void
    {
        $this->assertSame([], (new ProductPluginGroup())->getGuarded());
    }

    public function test_product_plugin_group_product_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new ProductPluginGroup())->product());
    }

    public function test_product_plugin_group_plugin_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new ProductPluginGroup())->plugin());
    }
}
