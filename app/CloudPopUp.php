<?php

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CloudPopUp extends Model
{
    use HasFactory, SystemActivityLogsTrait;

    protected $table = 'cloud_pop_up';

    protected $guarded = [];

    protected $logName = 'cloud';

    protected $logNameColumn = 'Settings';

    protected $logAttributes = [
        'cloud_top_message',
        'cloud_label_field',
        'cloud_label_radio',
    ];

    protected $logUrl = [
        'segments' => ['view/tenant'],
    ];

    protected function getMappings(): array
    {
        return [
            'cloud_top_message' => ['Cloud Top Message', fn ($value) => $value],
            'cloud_label_field' => ['Cloud Label Field', fn ($value) => $value],
            'cloud_label_radio' => ['Cloud Label Radio', fn ($value) => $value],
        ];
    }
}
