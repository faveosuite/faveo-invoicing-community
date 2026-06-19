<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\BaseModel;

/**
 * @property int $id
 * @property int $product_id
 * @property int $bundle_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundleRelation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundleRelation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundleRelation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundleRelation whereBundleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundleRelation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundleRelation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundleRelation whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBundleRelation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ProductBundleRelation extends BaseModel
{
    protected $table = 'product_bundle_relations';

    protected $fillable = ['product_id', 'bundle_id'];
}
