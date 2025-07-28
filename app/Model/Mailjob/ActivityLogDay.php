<?php

namespace App\Model\Mailjob;

use Illuminate\Database\Eloquent\Model;

class ActivityLogDay extends Model
{
    protected $table = 'activity_log_days';

    protected $fillable = ['days'];
}
