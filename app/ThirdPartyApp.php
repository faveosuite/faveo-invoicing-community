<?php

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Model;

class ThirdPartyApp extends Model
{

    use SystemActivityLogsTrait;

    protected $table = 'third_party_apps';

    protected $fillable = ['app_name', 'app_key', 'app_secret'];

    protected $logName = 'third_party_apps';

    protected $logNameColumn = 'settings';


    protected $logAttributes = [
        'app_name', 'app_key', 'app_secret'
    ];

    protected $logUrl = ['third-party-keys'];

    protected function getMappings(): array
    {
        return [
            'app_name' => ['App Name', fn ($value) => $value],
            'app_key' => ['App Key', fn ($value) => $value],
            'app_secret' => ['App Secret', fn ($value) => $value],
        ];
    }
}
