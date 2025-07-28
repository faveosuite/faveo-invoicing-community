<?php

namespace App\Model\Configure;

use App\Model\Payment\Plan;
use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigOption extends Model
{
    use HasFactory;

    protected $table = 'config_option';

    protected $guarded = [];

    // Define the relationship with ConfigGroup
    public function configGroup()
    {
        return $this->belongsTo(ConfigGroup::class, 'group_id');
    }

    // Define the relationship with Plan
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    // Define the relationship with ConfigOptionValue
    public function configOptionValues()
    {
        return $this->hasMany(ConfigOptionValue::class, 'option_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
