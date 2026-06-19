<?php

namespace App\Model\Product;

use App\BaseModel;
use Override;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Product\ProductAddonRelation> $relation
 * @property-read int|null $relation_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Addon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Addon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Addon query()
 *
 * @mixin \Eloquent
 */
class Addon extends BaseModel
{
    protected $table = 'addons';

    protected $fillable = ['product', 'subscription', 'name',
        'description', 'regular_price', 'selling_price', 'tax_addon',
        'show_on_order', 'auto_active_payment', 'suspend_parent', ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ProductAddonRelation, $this>
     */
    public function relation(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductAddonRelation::class);
    }

    #[Override]
    public function delete()
    {
        $this->relation()->delete();

        return parent::delete();
    }
}
