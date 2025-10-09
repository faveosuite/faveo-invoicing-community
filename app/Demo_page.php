<?php

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demo_page extends Model
{
    use HasFactory, SystemActivityLogsTrait;

    protected $table = 'demo_pages';

    protected $fillable = ['id', 'link', 'email', 'status'];

    protected $logName = 'page';

    protected $logNameColumn = 'Demo page';

    protected $logAttributes = [
        'id', 'link', 'email', 'status',
    ];

    protected $logUrl = ['/demo/page'];

    protected function getMappings(): array
    {
        return [
            'id' => ['ID', fn ($value) => $value],
            'link' => ['Link', fn ($value) => $value],
            'email' => ['Email', fn ($value) => $value],
            'status' => ['Status', fn ($value) => $value === 1 ? __('message.active') : __('message.inactive')],
        ];
    }
}
