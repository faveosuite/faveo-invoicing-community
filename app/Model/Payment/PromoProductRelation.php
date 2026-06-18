<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\BaseModel;

/**
 * @property int $id
 * @property int $promotion_id
 * @property int $product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoProductRelation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoProductRelation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoProductRelation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoProductRelation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoProductRelation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoProductRelation whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoProductRelation wherePromotionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoProductRelation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PromoProductRelation extends BaseModel
{
    protected $table = 'promo_product_relations';

    protected $fillable = ['product_id', 'promotion_id'];
}
