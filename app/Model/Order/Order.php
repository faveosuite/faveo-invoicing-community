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
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Override;

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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $license_mode
 * @property int $is_downloadable
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Installation> $installation
 * @property-read int|null $installation_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Installation> $installationDetail
 * @property-read int|null $installation_detail_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Order\Invoice> $invoice
 * @property-read int|null $invoice_count
 * @property-read \App\Model\Order\InvoiceItem|null $invoiceItem
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Order\OrderInvoiceRelation> $invoiceRelation
 * @property-read int|null $invoice_relation_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Order\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read Product|null $productRelation
 * @property-read Subscription|null $subscription
 * @property-read User|null $user
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
 * @mixin \Eloquent
 */
class Order extends BaseModel
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'orders';

    protected static string $logName = 'order';

    protected $fillable = ['client', 'order_status', 'invoice_item_id',
        'serial_key', 'product', 'domain', 'price_override', 'qty', 'number', 'license_mode',
        'is_downloadable',
    ];

    protected array $logAttributes = ['client', 'order_status', 'invoice_item_id',
        'serial_key', 'product', 'domain', 'price_override', 'qty', 'number', ];

    protected string $logNameColumn = 'number';

    protected array $logUrl = [
        'segments' => ['orders', ':id'],
    ];

    protected function getMappings(): array
    {
        return [
            'client' => ['Client', fn ($value) => User::find($value)?->user_name],
            'order_status' => ['Order Status', ucfirst(...)],
            'invoice_item_id' => ['Invoice Item ID', fn ($value) => $value],
            'serial_key' => ['Serial Key', fn ($value) => $value],
            'product' => ['Product', fn ($value) => Product::find($value)?->name],
            'domain' => ['Domain', fn ($value) => $value],
            'price_override' => ['Price Override', fn ($value) => $value],
            'qty' => ['Quantity', fn ($value) => $value],
            'number' => ['Order Number', fn ($value) => $value],
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'client');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Model\Product\Subscription, $this>
     */
    public function subscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Subscription::class, 'order_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Model\Product\Product, $this>
     */
    public function productRelation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class, 'product');
    }

    // Many-to-many: one order can appear on multiple invoices (original + renewals)
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Model\Order\Invoice, $this, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function invoices(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Invoice::class,
            'order_invoice_relations',
            'order_id',
            'invoice_id'
        );
    }


    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->invoices();
    }

    public function invoiceRelation(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Model\Order\OrderInvoiceRelation::class, 'order_id');
    }

    // The invoice item that generated this order
    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class, 'invoice_item_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\License\Models\Installation, $this>
     */
    public function installationDetail(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Installation::class, 'license_code', 'serial_key');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\License\Models\Installation, $this>
     */
    public function installation(): \Illuminate\Database\Eloquent\Relations\HasMany
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

    protected function orderStatus(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function ($value): string {
            return ucfirst((string) $value);
        });
    }

    protected function serialKey(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function ($value) {
            try {
                return Crypt::decrypt($value);
            } catch (DecryptException) {
                return $value;
            }
        });
    }

    protected function domain(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function ($value) {
            if (Str::endsWith($value, '/')) {
                return substr_replace($value, '', -1, 0);
            }

            return $value;
        }, set: function ($value): array {
            return ['domain' => $this->get_domain($value)];
        });
    }

    public function get_domain($url): string
    {
        $pieces = parse_url((string) $url);
        $domain = $pieces['host'] ?? '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }

        if (! $domain) {
            $domain = $pieces['path'];
        }

        return strtolower($domain);
    }

    public static function getOrderLink($orderId, string $url = 'orders'): string
    {
        $link = '--';
        $order = Order::where('id', $orderId)->select('id', 'number')->first();
        if ($order) {
            return '<a href='.url($url.'/'.$order->id).'>'.$order->number.'</a>';
        }

        return $link;
    }
}
