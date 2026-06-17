<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\BaseModel;
use App\Model\Product\Product;

class TaxProductRelation extends BaseModel
{
    protected $table = 'tax_product_relations';

    protected $fillable = ['product_id', 'tax_class_id'];

    public function tax()
    {
        return $this->belongsTo(TaxClass::class, 'tax_class_id');
    }

    public function product()
    {
        return $this->hasMany(Product::class, 'product_id');
    }
}
