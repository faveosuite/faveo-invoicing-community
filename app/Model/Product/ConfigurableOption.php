<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\BaseModel;

class ConfigurableOption extends BaseModel
{
    protected $table = 'configurable_options';

    protected $fillable = ['group_id', 'type', 'title', 'options', 'price'];
}
