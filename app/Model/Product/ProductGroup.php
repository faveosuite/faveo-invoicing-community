<?php

namespace App\Model\Product;

use App\BaseModel;

class ProductGroup extends BaseModel
{
    protected $table = 'product_groups';

    protected $fillable = ['id', 'name', 'headline', 'tagline', 'available_payment', 'hidden', 'cart_link', 'pricing_templates_id', 'status'];

    public function config()
    {
        return $this->hasMany(\App\Model\Product\ConfigurableOption::class, 'group_id');
    }

    public function features()
    {
        return $this->hasMany(\App\Model\Product\GroupFeatures::class, 'group_id');
    }

    public function product()
    {
        return $this->hasMany(\App\Model\Product\Product::class, 'group');
    }

    public function pricingTemplate()
    {
        return $this->belongsTo(\App\Model\Common\PricingTemplate::class, 'pricing_templates_id', 'id');
    }

    public function delete()
    {
        $this->config()->delete();
        $this->features()->delete();
        parent::delete();
    }
}
