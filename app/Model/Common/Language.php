<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $translation
 * @property string $locale
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereTranslation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

    protected string $logName = 'language';

    protected string $logNameColumn = 'name';

    protected array $logAttributes = [
        'name',
        'translation',
        'locale',
        'status',
    ];

    protected array $logUrl = [
        'segments' => ['languages'],
    ];

    protected function getMappings(): array
    {
        return [
            'name' => ['Name', fn ($value) => $value],
            'translation' => ['Translation', fn ($value) => $value],
            'locale' => ['Locale', fn ($value) => $value],
            'status' => [$this->name.' Language', fn ($value): array|string => $value === 1 ? __('message.enable') : __('message.disable')],
        ];
    }
}
