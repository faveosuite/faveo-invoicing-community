<?php

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Model;

class PricingTemplate extends Model
{
    protected $tables = 'pricing_templates';

    protected $fillable = ['data', 'image', 'name'];

    public function productGroups()
    {
        return $this->hasMany(\App\Model\Product\ProductGroup::class);
    }
}
