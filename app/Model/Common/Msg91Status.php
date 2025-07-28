<?php

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Msg91Status extends Model
{
    use HasFactory;

    protected $fillable = ['status_code', 'status_label'];
}
