<?php

namespace App\Model\Product;

use App\BaseModel;
use Override;

/**
 * @property int $id
 * @property string $name
 * @property string $valid_from
 * @property string $valid_till
 * @property int $uses
 * @property int $maximum_uses
 * @property int $allow-promotion
 * @property int $show
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Product\ProductBundleRelation> $relation
 * @property-read int|null $relation_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle whereAllowPromotion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle whereMaximumUses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle whereShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle whereUses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle whereValidFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundle whereValidTill($value)
 * @mixin \Eloquent
 */
class ProductBundle extends BaseModel
{
    protected $table = 'product_bundles';

    protected $fillable = ['name', 'valid_from', 'valid_till', 'uses', 'maximum_uses', 'allow-promotion', 'show'];

    public function relation()
    {
        return $this->hasMany(ProductBundleRelation::class, 'bundle_id');
    }

    #[Override]
    public function delete()
    {
        $this->relation()->delete();

        return parent::delete();
    }
}
