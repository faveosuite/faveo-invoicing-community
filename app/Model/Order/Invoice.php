<?php

namespace App\Model\Order;

use App\BaseModel;
use App\License\Models\Installation;
use App\Model\Product\Subscription;
use App\Traits\SystemActivityLogsTrait;
use App\User;
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
use Override;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property int $user_id
 * @property string $number
 * @property Carbon $date
 * @property string|null $discount
 * @property string $discount_mode
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
 * @property string|null $fulfillment_intent
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Collection<int, InvoiceItem> $invoiceItem
 * @property-read int|null $invoice_item_count
 * @property-read Collection<int, OrderInvoiceRelation> $orderRelation
 * @property-read int|null $order_relation_count
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, Payment> $payment
 * @property-read int|null $payment_count
 * @property-read Collection<int, Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read User|null $user
 *
 * @method static \Database\Factories\Model\Order\InvoiceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereBillingPay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCloudDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCouponCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCredits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDiscountMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereFulfillmentIntent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereGrandTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereIsRenewed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereProcessingFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUserId($value)
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
        'segments' => ['invoices', 'show'],
        'params' => [
            'invoiceid' => ':id',
        ],
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

    public function installationDetail(): mixed
    {
        $orderIds = $this->orders()->pluck('orders.id');
        $licenseCodes = Order::whereIn('id', $orderIds)->get()->map->serial_key;

        return Installation::whereIn('license_code', $licenseCodes);
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

    #[Override]
    public function delete()
    {
        $this->orders()->detach();
        $this->installationDetail()->delete();
        $this->invoiceItem()->delete();
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
