<?php

namespace App\Model\Order;

use App\BaseModel;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends BaseModel
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'invoices';

    protected $fillable = ['user_id', 'number', 'date', 'coupon_code', 'discount',
        'grand_total', 'currency', 'status', 'description', 'is_renewed', 'processing_fee', ];

    protected $casts = [
        'date' => 'datetime',
    ];

    protected static $logName = 'Invoice';

    protected static $logAttributes = ['user_id', 'number', 'date',
        'coupon_code', 'grand_total', 'currency', 'status', 'description', ];

    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        // dd(Activity::where('subject_id',)->pluck('subject_id'));
        if ($eventName == 'created') {
            return 'Invoice No.  <strong> '.$this->number.' </strong> was created';
        }

        if ($eventName == 'updated') {
            return 'Invoice No. <strong> '.$this->number.'</strong> was updated';
        }

        if ($eventName == 'deleted') {
            return 'Invoice No. <strong> '.$this->number.' </strong> was deleted';
        }

        return '';

        // return "Product  has been {$eventName}";
        // \Auth::user()->activity;
    }

    public function invoiceItem()
    {
        return $this->hasMany(\App\Model\Order\InvoiceItem::class, 'invoice_id');
    }

    public function order()
    {
        return $this->hasMany(\App\Model\Order\Order::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function subscription()
    {
        return $this->hasManyThrough(\App\Model\Product\Subscription::class, \App\Model\Order\Order::class);
    }

    public function installationDetail()
    {
        return $this->hasManyThrough(\App\Model\Order\InstallationDetail::class, \App\Model\Order\Order::class);
    }

    public function orderRelation()
    {
        return $this->hasMany('App\Model\Order\OrderInvoiceRelation');
    }

    public function payment()
    {
        return $this->hasMany(\App\Model\Order\Payment::class);
    }

    public function getStatusAttribute($value)
    {
        return ucfirst($value);
    }

    public function getCreatedAtAttribute($value)
    {
        $date1 = new DateTime($value);
        $date = $date1->format('M j, Y, g:i a ');

        return $date;
    }

    public function delete()
    {
        $this->orderRelation()->delete();
        $this->subscription()->delete();
        $this->installationDetail()->delete();
        $this->order()->delete();

        $this->invoiceItem()->delete();
        $this->payment()->delete();

        return parent::delete();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
