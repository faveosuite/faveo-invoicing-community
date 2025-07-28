<?php

namespace App\Model\Common;

use App\User;
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
        'country_iso',
        'mobile_code',
    ];
    protected $appends = ['formatted_sender_id'];

    public function readableStatus()
    {
        return $this->belongsTo(Msg91Status::class, 'status', 'status_code');
    }

    public function getFormattedSenderIdAttribute()
    {
        return strtoupper($this->sender_id);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')
            ->selectRaw("id, CONCAT(first_name, ' ', last_name) as full_name, email");
    }
}
