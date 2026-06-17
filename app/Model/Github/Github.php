<?php

namespace App\Model\Github;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;
use Crypt;

class Github extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'githubs';

    protected $fillable = ['client_id', 'client_secret', 'username', 'password'];

    protected $logName = 'github';

    protected $logNameColumn = 'Settings';

    protected $logAttributes = [
        'client_id', 'client_secret', 'username', 'password',
    ];

    protected $logUrl = [
        'segments' => ['third-party-integration'],
    ];

    protected function getMappings(): array
    {
        return [
            'client_id' => ['Client ID', fn ($value) => $value],
            'client_secret' => ['Client Secret', fn ($value) => $value],
            'username' => ['Username', fn ($value) => $value],
            'password' => ['Password', fn ($value): string => $value ? '********' : ''],
        ];
    }

    protected function password(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function ($value) {
            if ($value) {
                return Crypt::decrypt($value);
            }

            return $value;
        }, set: function ($value): array {
            $value = Crypt::encrypt($value);

            return ['password' => $value];
        });
    }
}
