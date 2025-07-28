<?php

namespace App\Model\Configure;

use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PluginCompatibleWithProducts extends Model
{
    use HasFactory;

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
