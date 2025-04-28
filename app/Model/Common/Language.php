<?php

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $table = 'languages';

    protected $fillable = [
        'name',
        'translation',
        'locale',
        'enable_disable',
    ];
}
