<?php

namespace App\Model\Product;

use App\BaseModel;
use Override;

class Type extends BaseModel
{
    protected $table = 'product_types';

    protected $fillable = ['name', 'description'];

    public function product()
    {
        return $this->hasMany(Product::class);
    }

    #[Override]
    public function delete()
    {
        $this->Product()->delete();

        return parent::delete();
    }
}
