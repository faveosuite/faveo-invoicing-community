<?php

declare(strict_types=1);

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $records
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportSetting whereRecords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ReportSetting extends Model
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'report_settings';

    protected $fillable = ['records'];

    protected string $logName = 'reports';

    protected string $logNameColumn = 'settings';

    protected array $logAttributes = [
        'records',
    ];

    protected array $logUrl = [
        'segments' => ['records', 'column'],
    ];

    protected function getMappings(): array
    {
        return [
            'records' => ['Records', fn ($value) => $value],
        ];
    }
}
