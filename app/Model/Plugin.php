<?php

namespace App\Model;

use App\BaseModel;

class Plugin extends BaseModel
{
    protected $table = 'plugins';

    protected $fillable = ['name', 'path', 'status', 'type'];
}
