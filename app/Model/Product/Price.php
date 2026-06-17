<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\BaseModel;

class Price extends BaseModel
{
    protected $table = 'prices';

    protected $fillable = ['product_id', 'currency', 'price', 'sales_price'];
}
