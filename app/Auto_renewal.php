<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $customer_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $invoice_number
 * @property int $order_id
 * @property string|null $payment_method
 * @property string|null $payment_intent_id
 * @property-read \App\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auto_renewal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auto_renewal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auto_renewal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auto_renewal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auto_renewal whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auto_renewal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auto_renewal whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auto_renewal whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auto_renewal wherePaymentIntentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auto_renewal wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auto_renewal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auto_renewal whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Auto_renewal extends Model
{
    /**
     * @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory>
     */
    use HasFactory;

    protected $table = 'auto_renewals';

    protected $fillable = ['user_id', 'customer_id', 'order_id', 'payment_method', 'payment_intent_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
