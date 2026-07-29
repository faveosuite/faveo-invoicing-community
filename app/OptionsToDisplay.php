<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OptionsToDisplay extends Model
{
    use HasFactory;

    protected $table = 'options_to_display';
    protected $fillable = [
        'product_id',
        'display_option',
        'option_description',
        'option_type',
    ];
}
