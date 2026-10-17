<?php

declare(strict_types=1);

namespace App\Model\Order;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * One allocation: how much of a payment settles a particular invoice.
 *
 * A real model rather than an anonymous pivot so the amount is typed — this
 * is the figure every paid/outstanding calculation rests on, and reading it
 * off an untyped `->pivot` is how those calculations silently drift.
 *
 * @property int $payment_id
 * @property int $invoice_id
 * @property string $amount
 */
class PaymentInvoice extends Pivot
{
    protected $table = 'payment_invoice';

    public $incrementing = true;

    protected $fillable = ['payment_id', 'invoice_id', 'amount'];

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
