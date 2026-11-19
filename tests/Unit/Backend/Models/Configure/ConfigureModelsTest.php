<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models\Configure;

use App\Model\Configure\PluginCompatibleWithProducts;
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
}
