<?php

namespace App\Model\Order;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;
use DateTime;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends BaseModel
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'orders';

    protected static $logName = 'order';

    protected $fillable = ['client', 'order_status', 'invoice_item_id',
        'serial_key', 'product', 'domain', 'subscription', 'price_override', 'qty', 'invoice_id', 'number', ];

    protected $logAttributes = ['client', 'order_status', 'invoice_item_id',
        'serial_key', 'product', 'domain', 'subscription', 'price_override', 'qty', 'invoice_id', 'number', ];

    protected $logNameColumn = 'number';

    protected $logUrl = ['orders', '/'];

    protected function getMappings(): array
    {
        return [
            'client' => ['Client', fn ($value) => \App\User::find($value)?->user_name],
            'order_status' => ['Order Status', fn ($value) => ucfirst($value)],
            'invoice_item_id' => ['Invoice Item ID', fn ($value) => $value],
            'serial_key' => ['Serial Key', fn ($value) => $value],
            'product' => ['Product', fn ($value) => \App\Model\Product\Product::find($value)?->name],
            'domain' => ['Domain', fn ($value) => $value],
            'subscription' => ['Subscription', fn ($value) => \App\Model\Product\Subscription::find($value)?->name],
            'price_override' => ['Price Override', fn ($value) => $value],
            'qty' => ['Quantity', fn ($value) => $value],
            'invoice_id' => ['Invoice ID', fn ($value) => $value],
            'number' => ['Order Number', fn ($value) => $value],
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(\App\Model\Order\Invoice::class, 'invoice_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'client');
    }

    public function subscription()
    {
        return $this->hasOne(\App\Model\Product\Subscription::class);
    }

    public function productUpload()
    {
        return $this->hasMany(\App\Model\Product\ProductUpload::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Model\Product\Product::class, 'product');
    }

    public function invoiceRelation()
    {
        return $this->hasMany(\App\Model\Order\OrderInvoiceRelation::class);
    }

    public function invoiceItem()
    {
        return $this->hasManyThrough(\App\Model\Order\InvoiceItem::class, \App\Model\Order\Invoice::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Model\Order\InvoiceItem::class);
    }

    public function installationDetail()
    {
        return $this->hasMany(\App\Model\Order\InstallationDetail::class);
    }

    public function delete()
    {
        $this->invoiceRelation()->delete();
        $this->subscription()->delete();
        parent::delete();
    }

    public function getOrderStatusAttribute($value)
    {
        return ucfirst($value);
    }

    public function getCreatedAtAttribute($value)
    {
        $date1 = new DateTime($value);
        $date = $date1->format('M j, Y, g:i a ');

        return $date;
    }

    public function getSerialKeyAttribute($value)
    {
        try {
            $decrypted = \Crypt::decrypt($value);

            return $decrypted;
        } catch (DecryptException $ex) {
            return $value;
        }
    }

    public function getDomainAttribute($value)
    {
        try {
            if (ends_with($value, '/')) {
                $value = substr_replace($value, '', -1, 0);
            }

            return $value;
        } catch (DecryptException $ex) {
            return $value;
        }
    }

    public function setDomainAttribute($value)
    {
        $this->attributes['domain'] = $this->get_domain($value);
    }

    public function get_domain($url)
    {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }
        if (! $domain) {
            $domain = $pieces['path'];
        }

        return strtolower($domain);
    }

    public static function getOrderLink($orderId, $url = 'orders')
    {
        $link = '--';
        $order = Order::where('id', $orderId)->select('id', 'number')->first();
        if ($order) {
            $link = '<a href='.url($url.'/'.$order->id).'>'.$order->number.'</a>';
        }

        return $link;
    }
}
