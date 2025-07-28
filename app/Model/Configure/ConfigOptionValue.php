<?php

namespace App\Model\Configure;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigOptionValue extends Model
{
    use HasFactory;

    protected $table = 'config_option_values';
    protected $guarded = [];

    // Define the relationship with ConfigOption
    public function configOption()
    {
        return $this->belongsTo(ConfigOption::class, 'option_id');
    }
}
