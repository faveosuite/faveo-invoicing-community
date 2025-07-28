<?php

namespace App\Model\Mailjob;

use Illuminate\Database\Eloquent\Model;

class FaveoQueue extends Model
{
    protected $table = 'faveo_queues';

    protected $fillable = ['key', 'value', 'service_id'];
}
