<?php

namespace App\Model\Product;

use Override;
use App\BaseModel;

class ProductBundle extends BaseModel
{
    protected $table = 'product_bundles';

    protected $fillable = ['name', 'valid_from', 'valid_till', 'uses', 'maximum_uses', 'allow-promotion', 'show'];

    public function relation()
    {
        return $this->hasMany(ProductBundleRelation::class, 'bundle_id');
    }

    #[Override]
    public function delete()
    {
        $this->relation()->delete();

        return parent::delete();
    }
}
