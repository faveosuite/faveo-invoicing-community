<?php

namespace App\Model\Payment;

use App\Model\Product\Product;
use App\BaseModel;

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
