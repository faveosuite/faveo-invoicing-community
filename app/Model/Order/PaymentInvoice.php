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
 * @property int $id
 * @property int $payment_id
 * @property int $invoice_id
 * @property numeric $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Invoice|null $invoice
 * @property-read Payment|null $payment
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentInvoice whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentInvoice whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentInvoice wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentInvoice whereUpdatedAt($value)
 *
 * @mixin \Eloquent
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
