<?php

namespace App\Model\Order;

use App\BaseModel;
use App\License\Models\Installation;
use App\Model\Product\Subscription;
use App\Traits\SystemActivityLogsTrait;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Date;
use Override;

/**
 * @property int $id
 * @property int $user_id
 * @property string $number
 * @property \Illuminate\Support\Carbon $date
 * @property string|null $discount
 * @property string $discount_mode
 * @property string|null $coupon_code
 * @property string $grand_total
 * @property string $currency
 * @property string $status
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_renewed
 * @property string|null $processing_fee
 * @property string|null $billing_pay
 * @property string|null $credits
 * @property string|null $cloud_domain
 * @property array<array-key, mixed>|null $metadata
 * @property string|null $fulfillment_intent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Order\InvoiceItem> $invoiceItem
 * @property-read int|null $invoice_item_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Order\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Order\Payment> $payment
 * @property-read int|null $payment_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read User|null $user
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
 * @mixin \Eloquent
 */
class Invoice extends BaseModel
{
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

    protected array $logAttributes = [
        'user_id', 'number', 'date', 'coupon_code', 'discount', 'discount_mode',
        'grand_total', 'currency', 'status', 'description', 'is_renewed', 'processing_fee', 'billing_pay', 'cloud_domain', 'credits',
    ];

    protected string $logNameColumn = 'number';

    protected array $logUrl = [
        'segments' => ['invoices', 'show'],
        'params' => [
            'invoiceid' => ':id',
        ],
    ];

    protected function getMappings(): array
    {
        return [
            'user_id' => ['User', fn ($value) => User::find($value)?->user_name],
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Model\Order\InvoiceItem, $this>
     */
    public function invoiceItem(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    // Many-to-many: one invoice covers multiple orders; one order appears on multiple invoices (renewals)
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Model\Order\Order, $this, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function orders(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Order::class,
            'order_invoice_relations',
            'invoice_id',
            'order_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Subscriptions reached through the pivot: Invoice → order_invoice_relations → subscriptions
    public function subscriptions()
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

    public function installationDetail()
    {
        $orderIds = $this->orders()->pluck('orders.id');
        $licenseCodes = Order::whereIn('id', $orderIds)->get()->map->serial_key;

        return Installation::whereIn('license_code', $licenseCodes);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Model\Order\Payment, $this>
     */
    public function payment(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }

    protected function status(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function ($value): string {
            return ucfirst((string) $value);
        });
    }

    #[Override]
    public function delete()
    {
        $this->orders()->detach();
        $this->installationDetail()->delete();
        $this->invoiceItem()->delete();
        $this->payment()->delete();

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
