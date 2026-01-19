<?php

namespace App\Model\Configure;

use App\Model\Payment\Plan;
use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigGroup extends Model
{
    use HasFactory;

    protected $table = 'config_group';

    protected $guarded = [];

    // Define the relationship with ConfigOption
    public function configOptions()
    {
        return $this->hasMany(ConfigOption::class, 'group_id');
    }

    public function plans(){
        return $this->hasMany(Plan::class,'group_id');
    }

    public function products(){
        return $this->belongsTo(Product::class,'product_id');
    }
}
