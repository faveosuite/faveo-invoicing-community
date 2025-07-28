<?php

namespace App\Model\Common;

use App\BaseModel;

class Timezone extends BaseModel
{
    protected $table = 'timezone';

    protected $fillable = ['id', 'name', 'location'];

    public $timestamps = false;
}
