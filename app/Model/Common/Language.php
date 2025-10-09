<?php

namespace App\Model\Common;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use SystemActivityLogsTrait;

    protected $table = 'languages';

    protected $fillable = [
        'name',
        'translation',
        'locale',
        'status',
    ];

    protected $logName = 'language';

    protected $logNameColumn = 'Settings';

    protected $logAttributes = [
        'name',
        'translation',
        'locale',
        'status',
    ];

    protected $logUrl = ['languages'];

    protected function getMappings(): array
    {
        return [
            'name' => ['Name', fn ($value) => $value],
            'translation' => ['Translation', fn ($value) => $value],
            'locale' => ['Locale', fn ($value) => $value],
            'status' => ["{$this->name} Language", fn ($value) => $value === 1 ? __('message.enable') : __('message.disable')],
        ];
    }
}
