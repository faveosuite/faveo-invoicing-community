<?php

namespace App\Model\Order;

use App\BaseModel;
use App\License\Models\Installation;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Traits\SystemActivityLogsTrait;
use App\User;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Override;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property int $number
 * @property int $invoice_item_id
 * @property int $client
 * @property string $order_status
 * @property string|null $serial_key
 * @property int|null $product
 * @property string $domain
 * @property string $price_override
 * @property string $qty
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $license_mode
 * @property int $is_downloadable
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Collection<int, Installation> $installation
 * @property-read int|null $installation_count
 * @property-read Collection<int, Installation> $installationDetail
 * @property-read int|null $installation_detail_count
 * @property-read Collection<int, Invoice> $invoice
 * @property-read int|null $invoice_count
 * @property-read InvoiceItem|null $invoiceItem
 * @property-read Collection<int, OrderInvoiceRelation> $invoiceRelation
 * @property-read int|null $invoice_relation_count
 * @property-read Collection<int, Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read Product|null $productRelation
 * @property-read Subscription|null $subscription
 * @property-read User|null $user
 *
 * @method static \Database\Factories\Model\Order\OrderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereInvoiceItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereIsDownloadable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereLicenseMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePriceOverride($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereProduct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereSerialKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Order extends BaseModel
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    use SystemActivityLogsTrait;

    protected $table = 'orders';

    protected static string $logName = 'order';

    protected $fillable = ['client', 'order_status', 'invoice_item_id',
        'serial_key', 'product', 'domain', 'price_override', 'qty', 'number', 'license_mode',
        'is_downloadable',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = ['client', 'order_status', 'invoice_item_id',
        'serial_key', 'product', 'domain', 'price_override', 'qty', 'number', ];

    protected string $logNameColumn = 'number';

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['orders', ':id'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'client' => ['Client', fn ($value) => User::find($value)?->user_name], // @phpstan-ignore property.notFound
            'order_status' => ['Order Status', ucfirst(...)],
            'invoice_item_id' => ['Invoice Item ID', fn ($value) => $value],
            'serial_key' => ['Serial Key', fn ($value) => $value],
            'product' => ['Product', fn ($value) => Product::find($value)?->name], // @phpstan-ignore property.notFound
            'domain' => ['Domain', fn ($value) => $value],
            'price_override' => ['Price Override', fn ($value) => $value],
            'qty' => ['Quantity', fn ($value) => $value],
            'number' => ['Order Number', fn ($value) => $value],
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client');
    }

    /**
     * @return HasOne<Subscription, $this>
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'order_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function productRelation(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product');
    }

    // Many-to-many: one order can appear on multiple invoices (original + renewals)
    /**
     * @return BelongsToMany<Invoice, $this, Pivot>
     */
    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(
            Invoice::class,
            'order_invoice_relations',
            'order_id',
            'invoice_id'
        );
    }

    public function invoice(): BelongsToMany // @phpstan-ignore missingType.generics
    {
        return $this->invoices();
    }

    /**
     * @return HasMany<OrderInvoiceRelation, $this>
     */
    public function invoiceRelation(): HasMany
    {
        return $this->hasMany(OrderInvoiceRelation::class, 'order_id');
    }

    // The invoice item that generated this order
    /**
     * @return BelongsTo<InvoiceItem, $this>
     */
    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'invoice_item_id');
    }

    /**
     * @return HasMany<Installation, $this>
     */
    public function installationDetail(): HasMany
    {
        return $this->hasMany(Installation::class, 'license_code', 'serial_key');
    }

    /**
     * @return HasMany<Installation, $this>
     */
    public function installation(): HasMany
    {
        return $this->hasMany(Installation::class, 'order_id');
    }

    #[Override]
    public function delete(): bool
    {
        $this->invoices()->detach();
        $this->subscription()->delete();

        return (bool) parent::delete();
    }

    /**
     * @return Attribute<mixed, mixed>
     */
    protected function orderStatus(): Attribute
    {
        return Attribute::make(get: function ($value): string {
            return ucfirst((string) $value);
        });
    }

    /**
     * @return Attribute<mixed, mixed>
     */
    protected function serialKey(): Attribute
    {
        return Attribute::make(get: function ($value) {
            try {
                return Crypt::decrypt($value);
            } catch (DecryptException) {
                return $value;
            }
        });
    }

    /**
     * @return Attribute<mixed, mixed>
     */
    protected function domain(): Attribute
    {
        return Attribute::make(get: function ($value) {
            if (Str::endsWith($value, '/')) {
                return substr_replace($value, '', -1, 0);
            }

            return $value;
        }, set: function ($value): array {
            return ['domain' => $this->get_domain($value)];
        });
    }

    public function get_domain(mixed $url): string
    {
        $pieces = parse_url((string) $url);
        $pieces = is_array($pieces) ? $pieces : [];
        $domain = $pieces['host'] ?? '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }

        if (! $domain) {
            $domain = $pieces['path'] ?? '';
        }

        return strtolower($domain);
    }

    public static function getOrderLink(mixed $orderId, string $url = 'orders'): string
    {
        $link = '--';
        $order = Order::where('id', $orderId)->select('id', 'number')->first();
        if ($order) {
            return '<a href='.url($url.'/'.$order->id).'>'.$order->number.'</a>';
        }

        return $link;
    }
}
