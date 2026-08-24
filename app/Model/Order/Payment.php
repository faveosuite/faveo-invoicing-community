<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\BaseModel;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * One payment is one real-world event: an amount left the client's bank on a
 * date, by a method. What it paid for lives in `payment_invoice` — a payment
 * can be split across several invoices, and an invoice can be settled by
 * several payments, so that fact cannot live in a column here.
 *
 * `invoice_id` and `parent_id` still hold their pre-pivot values for history
 * but are NOT authoritative; read {@see invoices()} / {@see applied()}.
 *
 * @property int $id
 * @property int $parent_id
 * @property int $invoice_id
 * @property int $user_id
 * @property string|null $amount
 * @property string|null $currency
 * @property string|null $payment_method
 * @property string|null $payment_status
 * @property string|null $amt_to_credit
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Invoice> $invoices
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PaymentInvoice> $allocations
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
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = ['parent_id', 'invoice_id', 'amount',
        'payment_method', 'user_id', 'payment_status', 'created_at', 'amt_to_credit', 'currency', ];

    /**
     * The invoices this payment settles, with how much went to each.
     *
     * @return BelongsToMany<Invoice, $this, PaymentInvoice>
     */
    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'payment_invoice', 'payment_id', 'invoice_id')
            ->using(PaymentInvoice::class)
            ->withPivot('amount');
    }

    /**
     * This payment's allocations as first-class rows — the typed way to read
     * how much went where, without going through an untyped pivot.
     *
     * @return HasMany<PaymentInvoice, $this>
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentInvoice::class, 'payment_id');
    }

    /** How much of this payment has been put against invoices. */
    public function applied(): float
    {
        return (float) $this->invoices()->sum('payment_invoice.amount');
    }

    /** What is left of it — money received that no invoice has claimed. */
    public function unapplied(): float
    {
        return max(0, round((float) $this->amount - $this->applied(), 2));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
