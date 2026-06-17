<?php

namespace App\Model\Common;

use App\BaseModel;

class Timezone extends BaseModel
{
    protected $table = 'timezone';

    protected $fillable = ['id', 'name', 'location'];

    public $timestamps = false;

    protected $appends = ['timezone_name'];

    protected function timezoneName(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function (): string {
            $extractGMT = explode(' ', $this->location);

            return reset($extractGMT).' '.$this->name;
        });
    }
}
