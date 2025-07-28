<?php

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Model;

class Bussiness extends Model
{
    protected $table = 'bussinesses';

    protected $fillable = ['short', 'name'];
}
