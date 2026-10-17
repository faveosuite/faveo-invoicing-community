<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\BaseModel;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property string $currency
 * @property int $subscription
 * @property string $price
 * @property string $sales_price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereSalesPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereSubscription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Price extends BaseModel
{
    protected $table = 'prices';

    protected $fillable = ['product_id', 'currency', 'price', 'sales_price'];
}
