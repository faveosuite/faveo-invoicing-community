<?php

namespace App\Model\Configure;

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
}
