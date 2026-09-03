<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\BaseModel;
use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int $tax_class_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Product> $product
 * @property-read int|null $product_count
 * @property-read TaxClass $tax
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxProductRelation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxProductRelation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxProductRelation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxProductRelation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxProductRelation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxProductRelation whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxProductRelation whereTaxClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxProductRelation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class TaxProductRelation extends BaseModel
{
    protected $table = 'tax_product_relations';

    protected $fillable = ['product_id', 'tax_class_id'];

    /**
     * @return BelongsTo<TaxClass, $this>
     */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class, 'tax_class_id');
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function product(): HasMany
    {
        return $this->hasMany(Product::class, 'product_id');
    }
}
