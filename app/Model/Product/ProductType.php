<?php

namespace App\Model\Product;

use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    protected $table = 'product_types';

    protected $fillable = ['id', 'name', 'description'];
}
