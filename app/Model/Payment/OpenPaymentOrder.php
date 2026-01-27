<?php

namespace App\Model\Payment;

use Illuminate\Database\Eloquent\Model;

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
        'currency',
        'gateway',
        'description',
        'transaction_id',
        'gateway_transaction_id',
        'payment_status',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Boot method to auto-generate transaction_id on creation.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
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
        return 'txn_'.strtolower(\Str::ulid());
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
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    /**
     * Scope for pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope for failed payments.
     */
    public function scopeFailed($query)
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
}
