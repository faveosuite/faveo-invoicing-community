<?php

namespace App\Model\Common;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Model;

class ChatScript extends Model
{
    use SystemActivityLogsTrait;

    protected $table = 'chat_scripts';

    protected $fillable = ['name', 'script', 'on_registration', 'on_every_page', 'google_analytics', 'google_analytics_tag'];

    protected $logName = 'chat-script';

    protected $logNameColumn = 'name';

    protected $logAttributes = [
        'name', 'script', 'on_registration', 'on_every_page', 'google_analytics', 'google_analytics_tag',
    ];

    protected $logUrl = [
        'segments' => ['chat', ':id', 'edit'],
    ];

    protected function getMappings(): array
    {
        return [
            'name' => ['Name', fn ($value) => $value],
            'script' => ['Script', fn ($value) => $value],
            'on_registration' => ['On Registration', fn ($value) => (int)$value === 1 ? __('message.active') : __('message.inactive')],
            'on_every_page' => ['On Every Page', fn ($value) => (int)$value === 1 ? __('message.active') : __('message.inactive')],
            'google_analytics' => ['Google Analytics', fn ($value) => (int)$value === 1 ? __('message.active') : __('message.inactive')],
            'google_analytics_tag' => ['Google Analytics Tag', fn ($value) => $value],
        ];
    }
}
