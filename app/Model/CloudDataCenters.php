<?php

namespace App\Model;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;

class CloudDataCenters extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'cloud_data_centers';

    protected $guarded = [];


    protected $logName = 'cloud';

    protected $logNameColumn = 'Settings';


    protected $logAttributes = [
        'cloud_countries',
        'cloud_state',
        'cloud_city',
        'latitude',
        'longitude',
    ];

    protected $logUrl = ['tax','edit'];

    protected function getMappings(): array
    {
        return [
            'cloud_countries' => ['Country', fn ($value) => $value],
            'cloud_state' => ['State', fn ($value) => $value],
            'cloud_city' => ['City', fn ($value) => $value],
            'latitude' => ['Latitude', fn ($value) => $value],
            'longitude' => ['Longitude', fn ($value) => $value],
        ];
    }
}
