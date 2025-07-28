<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Attach extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'attachment-helper';
    }
}
