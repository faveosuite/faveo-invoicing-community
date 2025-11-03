<?php

namespace App\Model\Product;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;

class ProductGroup extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'product_groups';

    protected $fillable = ['id', 'name', 'headline', 'tagline', 'available_payment', 'hidden', 'cart_link', 'pricing_templates_id', 'status'];

    protected $logName = 'group';
    protected $logNameColumn = 'name';

    protected $logAttributes = ['name', 'headline', 'tagline', 'available_payment', 'hidden', 'cart_link', 'pricing_templates_id', 'status'];

    protected $logUrl = [
        'segments' => [ 'groups', ':id' , 'edit']
        ];

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
        return $this->belongsTo(\App\Model\Common\PricingTemplate::class);
    }

    public function delete()
    {
        $this->config()->delete();
        $this->features()->delete();
        parent::delete();
    }

    protected function getMappings(): array
    {
        return [
            'name' => ['Name', fn ($value) => $value],
            'headline' => ['Headline', fn ($value) => $value],
            'tagline' => ['Tagline', fn ($value) => $value],
            'available_payment' => ['Available Payment', fn ($value) => $value],
            'hidden' => ['Hidden', fn ($value) => $value],
            'cart_link' => ['Cart Link', fn ($value) => $value],
            'pricing_templates_id' => ['Pricing Template', fn ($value) => $value],
            'status' => ['Status', fn ($value) => $value],
        ];
    }
}
