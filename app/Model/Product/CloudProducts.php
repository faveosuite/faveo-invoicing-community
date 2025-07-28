<?php

namespace App\Model\Product;

use App\BaseModel;
use App\Model\Payment\Plan;

class CloudProducts extends BaseModel
{
    protected $table = 'cloud_products';

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class, 'cloud_product');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'cloud_free_plan');
    }
}
