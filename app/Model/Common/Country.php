<?php

namespace App\Model\Common;

use App\BaseModel;

class Country extends BaseModel
{
    protected $table = 'countries';

    protected $primaryKey = 'country_id';

    protected $fillable = [
        'country_id', 'country_code_char2', 'country_code_char3', 'country_name', 'nicename', 'numcode', 'capital', 'phonecode', 'latitude', 'longitude', 'emoji', 'emojiU',
    ];

    public function currency()
    {
        return $this->belongsTo(\App\Model\Payment\Currency::class);
    }

    public function users()
    {
        return $this->hasMany(\App\User::class, 'country', 'country_code_char2');
    }
}
