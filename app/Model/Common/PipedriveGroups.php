<?php

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipedriveGroups extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_name',
    ];

}
