<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfuNotifications extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $primaryKey = 'notification_id';

    public $timestamps = false;
}
