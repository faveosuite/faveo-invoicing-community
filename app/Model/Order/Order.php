<?php

namespace App\Model\Order;

use App\BaseModel;
use DateTime;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends BaseModel
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'orders';

    protected static $logName = 'Order';

    protected $fillable = ['client', 'order_status', 'invoice_item_id',
        'serial_key', 'product', 'domain', 'subscription', 'price_override', 'qty', 'invoice_id', 'number', ];

    protected static $logAttributes = ['client', 'order_status', 'invoice_item_id',
        'serial_key', 'product', 'domain', 'subscription', 'price_override', 'qty', 'invoice_id', 'number', ];

    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        if ($eventName == 'created') {
            return 'Order No.  <strong> '.$this->number.' </strong> was created';
        }

        if ($eventName == 'updated') {
            return 'Order No. <strong> '.$this->number.'</strong> was updated';
        }

        if ($eventName == 'deleted') {
            return 'Order No. <strong> '.$this->number.' </strong> was deleted';
        }

        return '';
    }

//    public function invoice()
//    {
//        return $this->belongsTo(\App\Model\Order\Invoice::class, 'invoice_id');
//    }

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

    public function productRelation()
    {
        return $this->belongsTo(\App\Model\Product\Product::class, 'product');
    }

    public function invoices()
    {
        return $this->hasManyThrough(
            Invoice::class, // Final model
            OrderInvoiceRelation::class, // Pivot model
            'order_id', // Foreign key on pivot table
            'id', // Foreign key on Invoice table
            'id', // Local key on Order table
            'invoice_id' // Local key on pivot table
        );
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
