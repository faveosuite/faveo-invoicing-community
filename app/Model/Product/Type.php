<?php

namespace App\Model\Product;

use App\BaseModel;
use Override;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Product\Product> $product
 * @property-read int|null $product_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Type extends BaseModel
{
    protected $table = 'product_types';

    protected $fillable = ['name', 'description'];

    public function product(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class);
    }

    #[Override]
    public function delete()
    {
        $this->Product()->delete();

        return parent::delete();
    }
}
