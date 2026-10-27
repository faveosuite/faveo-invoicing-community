<?php

namespace App\Model\Order;

use App\BaseModel;
use App\Model\Product\Subscription;
use App\Traits\SystemActivityLogsTrait;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Override;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property int $user_id
 * @property string $number
 * @property Carbon $date
 * @property string|null $discount
 * @property string|null $discount_mode
 * @property string|null $coupon_code
 * @property string $grand_total
 * @property string $currency
 * @property string $status
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $is_renewed
 * @property string|null $processing_fee
 * @property string|null $billing_pay
 * @property string|null $credits
 * @property string|null $cloud_domain
 * @property array<array-key, mixed>|null $metadata
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Collection<int, PaymentInvoice> $allocations
 * @property-read int|null $allocations_count
 * @property-read Collection<int, InvoiceItem> $invoiceItem
 * @property-read int|null $invoice_item_count
 * @property-read Collection<int, OrderInvoiceRelation> $orderRelation
 * @property-read int|null $order_relation_count
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read PaymentInvoice|null $pivot
 * @property-read Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 * @property-read Collection<int, Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read string $type
 * @property-read User|null $user
 *
 * @method static \Database\Factories\Model\Order\InvoiceFactory factory($count = null, $state = [])
 * @method static Builder<static>|Invoice newModelQuery()
 * @method static Builder<static>|Invoice newQuery()
 * @method static Builder<static>|Invoice orderByField(string $field, string $direction)
 * @method static Builder<static>|Invoice query()
 * @method static Builder<static>|Invoice whereBillingPay($value)
 * @method static Builder<static>|Invoice whereCloudDomain($value)
 * @method static Builder<static>|Invoice whereCouponCode($value)
 * @method static Builder<static>|Invoice whereCreatedAt($value)
 * @method static Builder<static>|Invoice whereCredits($value)
 * @method static Builder<static>|Invoice whereCurrency($value)
 * @method static Builder<static>|Invoice whereDate($value)
 * @method static Builder<static>|Invoice whereDescription($value)
 * @method static Builder<static>|Invoice whereDiscount($value)
 * @method static Builder<static>|Invoice whereDiscountMode($value)
 * @method static Builder<static>|Invoice whereGrandTotal($value)
 * @method static Builder<static>|Invoice whereId($value)
 * @method static Builder<static>|Invoice whereIsRenewed($value)
 * @method static Builder<static>|Invoice whereMetadata($value)
 * @method static Builder<static>|Invoice whereNumber($value)
 * @method static Builder<static>|Invoice whereProcessingFee($value)
 * @method static Builder<static>|Invoice whereStatus($value)
 * @method static Builder<static>|Invoice whereUpdatedAt($value)
 * @method static Builder<static>|Invoice whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Invoice extends BaseModel
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    use SystemActivityLogsTrait;

    protected $table = 'invoices';

    protected $fillable = [
        'user_id', 'number', 'date', 'coupon_code', 'discount', 'discount_mode',
        'grand_total', 'currency', 'status', 'description', 'is_renewed',
        'processing_fee', 'billing_pay', 'cloud_domain', 'credits',
        'metadata',
    ];

    protected string $logName = 'invoice';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'user_id', 'number', 'date', 'coupon_code', 'discount', 'discount_mode',
        'grand_total', 'currency', 'status', 'description', 'is_renewed', 'processing_fee', 'billing_pay', 'cloud_domain', 'credits',
    ];

    protected string $logNameColumn = 'number';

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['admin', 'invoices', ':id'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'user_id' => ['User', fn ($value) => User::find($value)?->user_name], // @phpstan-ignore property.notFound
            'number' => ['Invoice Number', fn ($value) => $value],
            'date' => ['Invoice Date', fn ($value) => Date::parse($value)->toDateTimeString()],
            'coupon_code' => ['Coupon Code', fn ($value) => $value],
            'grand_total' => ['Grand Total', fn ($value) => $value],
            'currency' => ['Currency', fn ($value) => $value],
            'status' => ['Status', fn ($value) => $value],
            'description' => ['Description', fn ($value) => $value],
            'is_renewed' => ['Is Renewed', fn ($value): string => $value === 1 ? 'Yes' : 'No'],
            'processing_fee' => ['Processing Fee', fn ($value) => $value],
            'billing_pay' => ['Billing Pay', fn ($value) => $value],
            'cloud_domain' => ['Cloud Domain', fn ($value) => $value],
            'credits' => ['Credits', fn ($value) => $value],
            'discount' => ['Discount', fn ($value) => $value],
            'discount_mode' => ['Discount Mode', fn ($value) => $value],
        ];
    }

    /**
     * @return HasMany<InvoiceItem, $this>
     */
    public function invoiceItem(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    // Many-to-many: one invoice covers multiple orders; one order appears on multiple invoices (renewals)
    /**
     * @return BelongsToMany<Order, $this, Pivot>
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(
            Order::class,
            'order_invoice_relations',
            'invoice_id',
            'order_id'
        );
    }

    /**
     * @return HasMany<OrderInvoiceRelation, $this>
     */
    public function orderRelation(): HasMany
    {
        return $this->hasMany(OrderInvoiceRelation::class, 'invoice_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Subscriptions reached through the pivot: Invoice → order_invoice_relations → subscriptions
    public function subscriptions(): HasManyThrough // @phpstan-ignore missingType.generics
    {
        return $this->hasManyThrough(
            Subscription::class,
            OrderInvoiceRelation::class,
            'invoice_id',
            'order_id',
            'id',
            'order_id'
        );
    }

    /**
     * The payments that settle this invoice, each carrying how much of itself
     * it put here. Renamed from the old hasMany `payment()` deliberately: that
     * relation summed `payments.amount`, which is the whole payment, not the
     * slice that landed on this invoice.
     *
     * @return BelongsToMany<Payment, $this, PaymentInvoice>
     */
    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'payment_invoice', 'invoice_id', 'payment_id')
            ->using(PaymentInvoice::class)
            ->withPivot('amount');
    }

    /**
     * The three money questions about an invoice — asked here and nowhere else.
     * Every surface (admin payment forms, client invoice list, gateway
     * callbacks, bulk-delete) used to re-derive these with subtly different
     * rules; the one that forgot the payment_status filter reported less owed
     * than was actually owed.
     */
    public function paidTotal(): float
    {
        return (float) $this->payments()
            ->where('payment_status', 'success')
            ->sum('payment_invoice.amount');
    }

    /**
     * @return HasMany<PaymentInvoice, $this>
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentInvoice::class, 'invoice_id');
    }

    /**
     * Rounded to the cent, because both sides come out of varchar columns and
     * a run of partial payments otherwise leaves float dust behind — enough to
     * make a fully-paid invoice read as still owing 0.0000000001.
     */
    public function outstanding(): float
    {
        return max(0, round((float) $this->grand_total - $this->paidTotal(), 2));
    }

    /**
     * grand_total is a varchar column, so it is cast to sort by amount, not as text.
     *
     * @param  Builder<self>  $query
     */
    public function scopeOrderByField(Builder $query, string $field, string $direction): void
    {
        $column = $field === 'grand_total'
            ? DB::raw('CAST(invoices.grand_total AS DECIMAL(20,2))')
            : $field;

        $query->orderBy($column, $direction);
    }

    /** Re-derive the status from what has actually been paid, and persist it. */
    public function refreshStatus(): void
    {
        $this->status = match (true) {
            $this->outstanding() <= 0 => 'success',
            $this->paidTotal() > 0 => 'partially paid',
            default => 'pending',
        };

        $this->save();
    }

    /**
     * @return Attribute<mixed, mixed>
     */
    protected function status(): Attribute
    {
        return Attribute::make(get: function ($value): string {
            return ucfirst((string) $value);
        });
    }

    /**
     * What this invoice does when it is paid: purchase | renewal | upgrade_downgrade
     * | agent_alteration. One source for both the payment handler's branch and the
     * admin list badge, so the label can never disagree with the actual behaviour.
     *
     * @return Attribute<string, never>
     */
    protected function type(): Attribute
    {
        return Attribute::get(fn (): string => $this->metadata['type'] ?? ($this->is_renewed == 1 ? 'renewal' : 'purchase'));
    }

    #[Override]
    public function delete()
    {
        $this->orders()->detach();
        // Installations are not touched: they belong to the licence, not to an invoice —
        // deleting an old invoice must not wipe a live order's installed domains.
        $this->invoiceItem()->delete();
        InvoiceTaxLine::where('invoice_id', $this->id)->delete();
        // Detach, never delete: a payment may be settling other invoices too,
        // and the money arrived regardless of what happens to this invoice.
        $this->payments()->detach();

        return parent::delete();
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
