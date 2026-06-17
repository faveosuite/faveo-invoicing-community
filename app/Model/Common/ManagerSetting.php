<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Model;

class ManagerSetting extends Model
{
    use SystemActivityLogsTrait;

    protected $fillable = [
        'manager_role',
        'auto_assign',
    ];

    protected $logName = 'system_manager';

    protected $logNameColumn = 'Settings';

    protected $logAttributes = [
        'manager_role',
        'auto_assign',
    ];

    protected $logUrl = [
        'segments' => ['system-managers'],
    ];

    protected function getMappings(): array
    {
        return [
            'manager_role' => ['Manager Role', fn ($value) => $value],
            'auto_assign' => [$this->manager_role . ' manager assign status', fn ($value): array|string|null => $value ? __('message.active') : __('message.inactive')],
        ];
    }
}
