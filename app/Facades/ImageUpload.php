<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ImageUpload extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ImageUpload-helper';
    }
}
