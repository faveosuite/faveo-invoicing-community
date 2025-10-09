<?php

namespace App\Model\Common;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Model;

class FaveoCloud extends Model
{
    use SystemActivityLogsTrait;

    protected $table = 'faveo_cloud';

    protected $fillable = ['cloud_central_domain', 'cloud_cname'];

    protected $logName = 'cloud';

    protected $logNameColumn = 'Faveo Cloud';

    protected $logAttributes = [
        'cloud_central_domain', 'cloud_cname',
    ];

    protected $logUrl = ['view/tenant'];

    protected function getMappings(): array
    {
        return [
            'cloud_central_domain' => ['Cloud Central Domain', fn ($value) => $value],
            'cloud_cname' => ['Cloud Cname', fn ($value) => $value],
        ];
    }
}
