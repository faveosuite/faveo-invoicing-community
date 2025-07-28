<?php

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Model;

class StatesSubdivisions extends Model
{
    protected $table = 'states_subdivisions';

    protected $fillable = ['country_code_char2', 'country_code_char3', 'state_subdivision_name', 'state_subdivision_alternate_names',
        'primary_level_name', 'state_subdivision_code'];
}
