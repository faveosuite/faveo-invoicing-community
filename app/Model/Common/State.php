<?php

namespace App\Model\Common;

use App\BaseModel;

class State extends BaseModel
{
    protected $table = 'states_subdivisions';

    protected $primaryKey = 'state_subdivision_id';

    protected $fillable = [
        'state_subdivision_id', 'state_subdivision_name',
        'country_code', 'iso2',
        'primary_level_name', 'country_id',
        'latitude',
        'longitude',
    ];
}
