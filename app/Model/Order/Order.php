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

class Order extends BaseModel
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'orders';

    protected static $logName = 'order';

    protected $fillable = ['client', 'order_status', 'invoice_item_id',
        'serial_key', 'product', 'domain', 'price_override', 'qty', 'number', 'license_mode',
        'is_downloadable',
    ];

    protected $logAttributes = ['client', 'order_status', 'invoice_item_id',
        'serial_key', 'product', 'domain', 'price_override', 'qty', 'number', ];

    protected $logNameColumn = 'number';

    protected $logUrl = [
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

    public function user()
    {
        return $this->belongsTo(User::class, 'client');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'order_id');
    }

    public function productRelation()
    {
        return $this->belongsTo(Product::class, 'product');
    }

    // Many-to-many: one order can appear on multiple invoices (original + renewals)
    public function invoices()
    {
        return $this->belongsToMany(
            Invoice::class,
            'order_invoice_relations',
            'order_id',
            'invoice_id'
        );
    }

    // The invoice item that generated this order
    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class, 'invoice_item_id');
    }

    public function installationDetail()
    {
        return $this->hasMany(Installation::class, 'license_code', 'serial_key');
    }

    #[Override]
    public function delete(): void
    {
        $this->invoices()->detach();
        $this->subscription()->delete();
        parent::delete();
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
