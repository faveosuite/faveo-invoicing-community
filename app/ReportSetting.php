<?php

declare(strict_types=1);

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string $records
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportSetting whereRecords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportSetting whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ReportSetting extends Model
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    use SystemActivityLogsTrait;

    protected $table = 'report_settings';

    protected $fillable = ['records'];

    protected string $logName = 'reports';

    protected string $logNameColumn = 'settings';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'records',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['records', 'column'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'records' => ['Records', fn ($value) => $value],
        ];
    }
}
