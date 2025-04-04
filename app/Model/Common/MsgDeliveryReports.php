<?php

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsgDeliveryReports extends Model
{
    use HasFactory;

    protected $fillable = [
        'mobile_number',
        'request_id',
        'status',
        'date',
        'sender_id',
        'failure_reason',
        'user_id',
    ];
}
