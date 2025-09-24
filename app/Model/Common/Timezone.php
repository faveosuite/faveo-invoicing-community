<?php

namespace App\Model\Common;

use App\BaseModel;

class Timezone extends BaseModel
{
    protected $table = 'timezone';

    protected $fillable = ['id', 'name', 'location'];

    public $timestamps = false;

    protected $appends = ['timezone_name'];
    public function getTimezoneNameAttribute()
    {
        $extractGMT = explode(' ', $this->location);
        $timezone = reset($extractGMT).' '.$this->name;

        return $timezone;
    }
}
