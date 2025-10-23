<?php

namespace App\Model\Product;

use Illuminate\Database\Eloquent\Model;

class ProductUpload extends Model
{
    protected $table = 'product_uploads';

    protected $fillable = ['product_id', 'title', 'description', 'version', 'file', 'is_private', 'is_restricted', 'release_type',
        'dependencies'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(\App\Model\Order\Order::class);
    }

    public function getDependenciesAttribute($value)
    {
        return json_decode($value);
    }
}
