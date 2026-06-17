<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\BaseModel;

class PromoProductRelation extends BaseModel
{
    protected $table = 'promo_product_relations';

    protected $fillable = ['product_id', 'promotion_id'];
}
