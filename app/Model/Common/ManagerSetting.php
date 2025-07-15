<?php

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Model;

class ManagerSetting extends Model
{
    protected $fillable = [
        'manager_role',
        'auto_assign',
    ];
}
