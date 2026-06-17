<?php

declare(strict_types=1);

namespace App\Plugins\Zoho\Integrations\Crm\Facades;

use Illuminate\Support\Facades\Facade;

class ZohoCrm extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'zoho.crm';
    }
}
