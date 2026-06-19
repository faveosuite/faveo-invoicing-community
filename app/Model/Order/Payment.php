<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\BaseModel;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $parent_id
 * @property int $invoice_id
 * @property int $user_id
 * @property string|null $amount
 * @property string|null $currency
 * @property string|null $payment_method
 * @property string|null $payment_status
 * @property string|null $amt_to_credit
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Model\Order\Invoice|null $invoice
 * @property-read User|null $user
 *
 * @method static \Database\Factories\Model\Order\PaymentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereAmtToCredit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Payment extends BaseModel
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = ['parent_id', 'invoice_id', 'amount',
        'payment_method', 'user_id', 'payment_status', 'created_at', 'amt_to_credit', 'currency', ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Model\Order\Invoice, $this>
     */
    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    //    public function setCreatedAtAttribute($value) {
//        dd($value);
//    }
}
