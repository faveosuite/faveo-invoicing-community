<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models\Configure;

use App\Model\Configure\PluginCompatibleWithProducts;
use App\Model\Configure\ProductPluginGroup;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class ConfigureModelsTest extends TestCase
{
    // ───────────── PluginCompatibleWithProducts ─────────────

    public function test_plugin_compatible_with_products_table_name(): void
    {
        $this->assertSame('plugin_compatible_with_products', new PluginCompatibleWithProducts()->getTable());
    }

    public function test_plugin_compatible_with_products_guarded_is_empty(): void
    {
        $this->assertSame([], new PluginCompatibleWithProducts()->getGuarded());
    }

    public function test_plugin_compatible_with_products_product_comp_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, new PluginCompatibleWithProducts()->productComp());
    }

    public function test_plugin_compatible_with_products_plugin_comp_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, new PluginCompatibleWithProducts()->pluginComp());
    }

    // ───────────── ProductPluginGroup ─────────────

    public function test_product_plugin_group_table_name(): void
    {
        $this->assertSame('product_plugin_group', new ProductPluginGroup()->getTable());
    }

    public function test_product_plugin_group_guarded_is_empty(): void
    {
        $this->assertSame([], new ProductPluginGroup()->getGuarded());
    }

    public function test_product_plugin_group_product_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, new ProductPluginGroup()->product());
    }

    public function test_product_plugin_group_plugin_relation(): void
    {
        $this->assertInstanceOf(BelongsTo::class, new ProductPluginGroup()->plugin());
    }
}
