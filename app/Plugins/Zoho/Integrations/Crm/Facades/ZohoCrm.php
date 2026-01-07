<?php

namespace App\Plugins\Zoho\Integrations\Crm\Facades;

use Illuminate\Support\Facades\Facade;

class ZohoCrm extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'zoho.crm';
    }
}