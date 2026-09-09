<?php

namespace App\Model\Product;

use App\BaseModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Product> $product
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

    /**
     * @return HasMany<Product, $this>
     */
    public function product(): HasMany
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
