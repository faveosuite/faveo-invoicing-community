<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Model\Product\Product;
use App\Traits\SystemActivityLogsTrait;

class Promotion extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'promotions';

    protected $fillable = ['code', 'type', 'uses', 'value', 'start', 'expiry'];

    protected $logName = 'promotions';

    protected $logNameColumn = 'code';

    protected $logAttributes = [
        'code', 'type', 'uses', 'value', 'start', 'expiry',
    ];

    protected $logUrl = [
        'segments' => ['promotions', ':id', 'edit'],
    ];

    protected function getMappings(): array
    {
        return [
            'code' => ['Promotion Code', fn ($value) => $value],
            'type' => ['Promotion Type', fn ($value) => $value === 1 ? 'Percentage' : 'Fixed Amount'],
            'uses' => ['Number of Uses', fn ($value) => $value],
            'value' => ['Promotion Value', fn ($value) => $value],
            'start' => ['Start Date', fn ($value) => $value],
            'expiry' => ['Expiry Date', fn ($value) => $value],
        ];
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
