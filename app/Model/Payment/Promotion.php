<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Model\Product\Product;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Promotion extends BaseModel
{
    use LogsActivity;

    protected $table = 'promotions';

    protected $fillable = ['code', 'type', 'uses', 'value', 'start', 'expiry'];

    protected static $logName = 'Promotion';

    protected static $logAttributes = ['code', 'type', 'uses', 'value', 'start', 'expiry'];

    protected static $logOnlyDirty = true;

    // protected $dates = ['start','expiry'];
    public function getDescriptionForEvent(string $eventName): string
    {
        if ($eventName == 'created') {
            return 'Promotion Code  <strong> '.$this->code.' </strong> was created';
        }

        if ($eventName == 'updated') {
            return 'Promotion Code <strong> '.$this->code.'</strong> was updated';
        }

        if ($eventName == 'deleted') {
            return 'Promotion Code <strong> '.$this->code.' </strong> was deleted';
        }

        return '';
    }

    public function relation()
    {
        return $this->hasMany(\App\Model\Payment\PromoProductRelation::class, 'promotion_id');
    }

    public function delete()
    {
        $this->relation->each(function ($relation) {
            $relation->delete();
        });

        return parent::delete();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function promotionType()
    {
        return $this->belongsTo(\App\Model\Payment\PromotionType::class, 'type', 'id');
    }

    public function products()
    {
        return $this->hasOneThrough(
            Product::class,
            \App\Model\Payment\PromoProductRelation::class,
            'promotion_id', // Foreign key on promo_product_relations table...
            'id',           // Foreign key on products table...
            'id',           // Local key on promotions table...
            'product_id'    // Local key on promo_product_relations table
        );
    }
}
