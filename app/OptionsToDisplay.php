<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
