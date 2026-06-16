<?php

namespace App\Model\Payment;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Override;
use Str;

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
    public function isPaid()
    {
        return $this->payment_status === 'completed';
    }

    /**
     * Check if payment is pending.
     */
    public function isPending()
    {
        return $this->payment_status === 'pending';
    }

    /**
     * Check if payment failed.
     */
    public function isFailed()
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
