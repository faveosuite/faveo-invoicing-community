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
    ];
    protected $appends = ['readable_status', 'formatted_sender_id'];

    const STATUS_MAP = [
        '1' => 'Delivered',
        '2' => 'Failed',
        '9' => 'NDNC',
        '16' => 'Rejected',
        '25' => 'Rejected',
        '17' => 'Blocked number',
    ];

    public function getReadableStatusAttribute()
    {
        return self::STATUS_MAP[$this->status] ?? $this->status;
    }

    public function getFormattedSenderIdAttribute()
    {
        return strtoupper($this->sender_id);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')
            ->selectRaw("id, CONCAT(first_name, ' ', last_name) as full_name");
    }

}
