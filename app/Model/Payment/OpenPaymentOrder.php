<?php

namespace App\Model\Payment;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Override;
use Str;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $mobile
 * @property string $address
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string $country
 * @property string $company
 * @property numeric $amount
 * @property numeric $base_amount
 * @property numeric $processing_fee
 * @property numeric $processing_fee_rate
 * @property string $currency
 * @property string $gateway
 * @property string|null $description
 * @property string|null $transaction_id
 * @property string|null $gateway_transaction_id
 * @property string $payment_status
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder failed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereBaseAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereGateway($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereGatewayTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereProcessingFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereProcessingFeeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenPaymentOrder whereZip($value)
 * @mixin \Eloquent
 */
class OpenPaymentOrder extends Model
{
    protected $table = 'open_payment_orders';

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'company',
        'amount',
        'base_amount',
        'processing_fee',
        'processing_fee_rate',
        'currency',
        'gateway',
        'description',
        'transaction_id',
        'gateway_transaction_id',
        'payment_status',
        'paid_at',
    ];

    /**
     * Boot method to auto-generate transaction_id on creation.
     */
    #[Override]
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order): void {
            if (empty($order->transaction_id)) {
                $order->transaction_id = self::generateTransactionId();
            }
        });
    }

    /**
     * Generate unique transaction ID
     * Format: txn_{unique_id} (e.g., txn_1234567890).
     */
    public static function generateTransactionId(): string
    {
        return 'txn_'.strtolower(Str::ulid());
    }

    /**
     * Check if payment is completed.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'completed';
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    /**
     * Check if payment failed.
     */
    public function isFailed(): bool
    {
        return $this->payment_status === 'failed';
    }

    /**
     * Scope for completed payments.
     */
    #[Scope]
    protected function completed($query)
    {
        return $query->where('payment_status', 'completed');
    }

    /**
     * Scope for pending payments.
     */
    #[Scope]
    protected function pending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope for failed payments.
     */
    #[Scope]
    protected function failed($query)
    {
        return $query->where('payment_status', 'failed');
    }

    /**
     * Get the gateway-specific transaction ID (Stripe payment_intent_id, Razorpay payment_id).
     */
    public function getGatewayId(): ?string
    {
        return $this->gateway_transaction_id;
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'base_amount' => 'decimal:2',
            'processing_fee' => 'decimal:2',
            'processing_fee_rate' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }
}
