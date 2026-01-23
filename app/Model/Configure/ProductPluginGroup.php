<?php

namespace App\Model\Configure;

use App\BaseModel;
use App\Model\Product\Product;

class ProductPluginGroup extends BaseModel
{
    protected $table = 'product_plugin_group';

    protected $guarded = [];

    // Define the relationship with Product (as product)
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Define the relationship with Product (as plugin)
    public function plugin()
    {
        return $this->belongsTo(Product::class, 'plugin_id');
    }
}
