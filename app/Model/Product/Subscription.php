<?php

namespace App\Model\Product;

use App\Traits\SystemActivityLogsTrait;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'subscriptions';

    protected $fillable = ['name', 'description', 'days', 'ends_at', 'update_ends_at',
        'user_id', 'plan_id', 'order_id', 'deny_after_subscription', 'version', 'product_id', 'support_ends_at', 'version_updated_at', 'is_subscribed', 'is_deleted'];

    protected $casts = [
        'ends_at' => 'datetime',
    ];

    protected $logName = 'subscriptions';

    protected $logNameColumn = 'Settings';

    protected $logAttributes = [
        'name', 'description', 'days', 'ends_at', 'update_ends_at',
        'user_id', 'plan_id', 'order_id', 'deny_after_subscription', 'version', 'product_id', 'support_ends_at', 'version_updated_at', 'is_subscribed', 'is_deleted',
    ];

    protected function getMappings(): array
    {
        return [
            'name' => ['Subscription Name', fn ($value) => $value],
            'description' => ['Description', fn ($value) => $value],
            'days' => ['Subscription Days', fn ($value) => $value],
            'ends_at' => ['Subscription End Date', fn ($value) => $value],
            'update_ends_at' => ['Update End Date', fn ($value) => $value],
            'user_id' => ['User', fn ($value) => \App\User::find($value)?->name],
            'plan_id' => ['Plan', fn ($value) => \App\Model\Payment\Plan::find($value)?->name],
            'order_id' => ['Order', fn ($value) => $value ? \App\Model\Order\Order::find($value)?->number : 'No Order'],
            'deny_after_subscription' => ['Deny After Subscription', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'version' => ['Version', fn ($value) => $value],
            'product_id' => ['Product', fn ($value) => \App\Model\Product\Product::find($value)?->name],
            'support_ends_at' => ['Support End Date', fn ($value) => $value],
            'version_updated_at' => ['Version Updated At', fn ($value) => $value],
            'is_subscribed' => ['Is Subscribed', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'is_deleted' => ['Is Deleted', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
        ];
    }

    public function plan()
    {
        return $this->belongsTo(\App\Model\Payment\Plan::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Model\Product\Product::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function order()
    {
        return $this->belongsTo(\App\Model\Order\Order::class);
    }

    // public function getEndsAtAttribute($value)
    // {
    //      $date1 = new DateTime($value);
    //     $tz = \Auth::user()->timezone()->first()->name;
    //     $date1->setTimezone(new DateTimeZone($tz));
    //     $date = $date1->format('M j, Y, g:i a ');

    //     return $date;
    // }

    //    public function delete() {
//
//
//        $this->Plan()->delete();
//
//
//        return parent::delete();
//    }
}
