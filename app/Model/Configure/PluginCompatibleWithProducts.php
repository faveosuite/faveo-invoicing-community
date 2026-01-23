<?php

namespace App\Model\Configure;

use App\BaseModel;
use App\Model\Product\Product;

class PluginCompatibleWithProducts extends BaseModel
{
    protected $table = 'plugin_compatible_with_products';

    protected $guarded = [];

    // Define the relationship with Product (as product)
    public function productComp()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Define the relationship with Product (as plugin)
    public function pluginComp()
    {
        return $this->belongsTo(Product::class, 'plugin_id');
    }
}
