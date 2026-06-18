<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\BaseModel;

/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PromotionType extends BaseModel
{
    protected $table = 'promotion_types';

    protected $fillable = ['name'];
}
