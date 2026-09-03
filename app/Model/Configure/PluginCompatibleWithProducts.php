<?php

declare(strict_types=1);

namespace App\Model\Configure;

use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $plugin_id
 * @property int $product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product $pluginComp
 * @property-read Product $productComp
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PluginCompatibleWithProducts newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PluginCompatibleWithProducts newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PluginCompatibleWithProducts query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PluginCompatibleWithProducts whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PluginCompatibleWithProducts whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PluginCompatibleWithProducts wherePluginId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PluginCompatibleWithProducts whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PluginCompatibleWithProducts whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PluginCompatibleWithProducts extends Model
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    protected $table = 'plugin_compatible_with_products';

    protected $guarded = [];

    // Define the relationship with Product (as product)
    /**
     * @return BelongsTo<Product, $this>
     */
    public function productComp(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Define the relationship with Product (as plugin)
    /**
     * @return BelongsTo<Product, $this>
     */
    public function pluginComp(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'plugin_id');
    }
}
