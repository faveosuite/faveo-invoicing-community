<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\BaseModel;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAddonRelation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAddonRelation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAddonRelation query()
 *
 * @mixin \Eloquent
 */
class ProductAddonRelation extends BaseModel
{
    protected $table = 'product_addon_relations';

    protected $fillable = ['addon_id', 'product_id'];
}
