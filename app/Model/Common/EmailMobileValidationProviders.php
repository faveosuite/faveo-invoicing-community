<?php

namespace App\Model\Common;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Model;

class EmailMobileValidationProviders extends Model
{
    use SystemActivityLogsTrait;

    protected $table = 'email_mobile_validation_providers';

    protected $fillable = ['provider', 'api_key', 'api_secret', 'mode', 'accepted_output'];

    protected $logName = 'validation-provider';

    protected $logNameColumn = 'Third Party Keys';

    protected $logAttributes = [
        'provider,api_key', 'api_secret', 'mode', 'accepted_output',
    ];

    protected $logUrl = ['third-party-integration'];

    protected function getMappings(): array
    {
        return [
            'provider' => ['Provider', fn ($value) => $value],
            'api_key' => ['API Key', fn ($value) => $value],
            'api_secret' => ['API Secret', fn ($value) => $value],
            'mode' => ['Mode', fn ($value) => $value],
            'accepted_output' => ['Accepted Output', fn ($value) => $value],
        ];
    }
}
