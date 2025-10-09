<?php

namespace App\Model\Order;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends BaseModel
{
    use HasFactory, SystemActivityLogsTrait;

    protected $table = 'invoices';

    protected $fillable = [
        'user_id', 'number', 'date', 'coupon_code', 'discount', 'discount_mode',
        'grand_total', 'currency', 'status', 'description', 'is_renewed', 'processing_fee', 'billing_pay', 'cloud_domain', 'credits',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    protected $logName = 'invoice';

    protected $logAttributes = [
        'user_id', 'number', 'date', 'coupon_code', 'discount', 'discount_mode',
        'grand_total', 'currency', 'status', 'description', 'is_renewed', 'processing_fee', 'billing_pay', 'cloud_domain', 'credits',
    ];

    protected $logNameColumn = 'number';

    protected $logUrl = ['invoices'];

    protected function getMappings(): array
    {
        return [
            'user_id' => ['User', fn ($value) => \App\User::find($value)?->user_name],
            'number' => ['Invoice Number', fn ($value) => $value],
            'date' => ['Invoice Date', fn ($value) => $value],
            'coupon_code' => ['Coupon Code', fn ($value) => $value],
            'grand_total' => ['Grand Total', fn ($value) => $value],
            'currency' => ['Currency', fn ($value) => $value],
            'status' => ['Status', fn ($value) => $value],
            'description' => ['Description', fn ($value) => $value],
            'is_renewed' => ['Is Renewed', fn ($value) => $value === 1 ? 'Yes' : 'No'],
            'processing_fee' => ['Processing Fee', fn ($value) => $value],
            'billing_pay' => ['Billing Pay', fn ($value) => $value],
            'cloud_domain' => ['Cloud Domain', fn ($value) => $value],
            'credits' => ['Credits', fn ($value) => $value],
            'discount' => ['Discount', fn ($value) => $value],
            'discount_mode' => ['Discount Mode', fn ($value) => $value],
        ];
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
}
