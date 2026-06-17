<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\BaseModel;

class Service extends BaseModel
{
    protected $table = 'services';

    protected $fillable = ['name', 'description'];
}
