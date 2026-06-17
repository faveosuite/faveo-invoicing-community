<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $manager_role
 * @property int $auto_assign
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManagerSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManagerSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManagerSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManagerSetting whereAutoAssign($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManagerSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManagerSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManagerSetting whereManagerRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ManagerSetting whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ManagerSetting extends Model
{
    use SystemActivityLogsTrait;

    protected $fillable = [
        'manager_role',
        'auto_assign',
    ];

    protected string $logName = 'system_manager';

    protected string $logNameColumn = 'Settings';

    protected array $logAttributes = [
        'manager_role',
        'auto_assign',
    ];

    protected array $logUrl = [
        'segments' => ['system-managers'],
    ];

    protected function getMappings(): array
    {
        return [
            'manager_role' => ['Manager Role', fn ($value) => $value],
            'auto_assign' => [$this->manager_role.' manager assign status', fn ($value): array|string => $value ? __('message.active') : __('message.inactive')],
        ];
    }
}
