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
 * @property int $product_id
 * @property int $plugin_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product $plugin
 * @property-read Product $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPluginGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPluginGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPluginGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPluginGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPluginGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPluginGroup wherePluginId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPluginGroup whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPluginGroup whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ProductPluginGroup extends Model
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    protected $table = 'product_plugin_group';

    protected $guarded = [];

    // Define the relationship with Product (as product)
    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Define the relationship with Product (as plugin)
    /**
     * @return BelongsTo<Product, $this>
     */
    public function plugin(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'plugin_id');
    }
}
