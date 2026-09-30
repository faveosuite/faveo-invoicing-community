<?php

namespace App\Model\Common;

use App\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $mobile_number
 * @property string $mobile_code
 * @property string|null $request_id
 * @property int|null $status
 * @property string|null $date
 * @property string|null $sender_id
 * @property string|null $failure_reason
 * @property int|null $user_id
 * @property string|null $country_iso
 * @property string|null $source
 * @property string|null $action
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $formatted_sender_id
 * @property-read Msg91Status|null $readableStatus
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MsgDeliveryReports newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MsgDeliveryReports newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MsgDeliveryReports query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MsgDeliveryReports whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MsgDeliveryReports whereCountryIso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MsgDeliveryReports whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MsgDeliveryReports whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MsgDeliveryReports whereFailureReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MsgDeliveryReports whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MsgDeliveryReports whereMobileCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MsgDeliveryReports whereMobileNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MsgDeliveryReports whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MsgDeliveryReports whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MsgDeliveryReports whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MsgDeliveryReports whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MsgDeliveryReports whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MsgDeliveryReports whereUserId($value)
 *
 * @mixin \Eloquent
 */
class MsgDeliveryReports extends Model
{
    /**
     * @use HasFactory<Factory>
     */
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
        'source',
        'action',
    ];

    protected $appends = ['formatted_sender_id'];

    /**
     * @return BelongsTo<Msg91Status, $this>
     */
    public function readableStatus(): BelongsTo
    {
        return $this->belongsTo(Msg91Status::class, 'status', 'status_code');
    }

    /**
     * @return Attribute<mixed, mixed>
     */
    protected function formattedSenderId(): Attribute
    {
        return Attribute::make(get: function () {
            return strtoupper((string) $this->sender_id);
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')
            ->withTrashed()
            ->selectRaw("id, CONCAT(first_name, ' ', last_name) as full_name, email");
    }
}
