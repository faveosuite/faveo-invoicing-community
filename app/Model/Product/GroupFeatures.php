<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\BaseModel;

class GroupFeatures extends BaseModel
{
    protected $table = 'group_features';

    protected $fillable = ['group_id', 'features'];
}
