<?php

namespace App\Modules\License\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfuFailedUpdates extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $primaryKey = 'failed_update_id';
}
