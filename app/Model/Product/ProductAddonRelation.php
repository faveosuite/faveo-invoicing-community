<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\BaseModel;

class ProductAddonRelation extends BaseModel
{
    protected $table = 'product_addon_relations';

    protected $fillable = ['addon_id', 'product_id'];
}
