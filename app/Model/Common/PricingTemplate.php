<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\Model\Product\ProductGroup;
use Illuminate\Database\Eloquent\Model;

class PricingTemplate extends Model
{
    protected $tables = 'pricing_templates';

    protected $fillable = ['data', 'image', 'name'];

    public function productGroups()
    {
        return $this->hasMany(ProductGroup::class);
    }
}
