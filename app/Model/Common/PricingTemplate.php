<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\Model\Product\ProductGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $data
 * @property string $image
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ProductGroup> $productGroups
 * @property-read int|null $product_groups_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PricingTemplate whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PricingTemplate extends Model
{
    protected string $tables = 'pricing_templates';

    protected $fillable = ['data', 'image', 'name'];

    /**
     * @return HasMany<ProductGroup, $this>
     */
    public function productGroups(): HasMany
    {
        return $this->hasMany(ProductGroup::class);
    }
}
