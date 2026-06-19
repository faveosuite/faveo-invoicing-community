<?php

declare(strict_types=1);

namespace App;

use App\Model\Order\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $date
 * @property string|null $from
 * @property string|null $to
 * @property string $subject
 * @property string $body
 * @property string|null $status
 * @property string|null $exception
 * @property string|null $order
 * @property string|null $payment_method
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $amount
 * @property string|null $payment_type
 * @property-read Order|null $orderDetails
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment_log newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment_log newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment_log query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment_log whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment_log whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment_log whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment_log whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment_log whereException($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment_log whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment_log whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment_log whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment_log wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment_log wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment_log whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment_log whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment_log whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment_log whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Payment_log extends Model
{
    /**
     * @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory>
     */
    use HasFactory;

    public $timestamps = true;

    protected $table = 'payment_logs';

    protected $fillable = ['id', 'from', 'to', 'date', 'subject', 'body', 'status', 'amount', 'payment_type'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'from', 'email');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Order, $this>
     */
    public function orderDetails(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class, 'order', 'number');
    }
}
