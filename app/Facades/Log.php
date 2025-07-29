<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Log extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'Log';
    }
}
