<?php

declare(strict_types=1);

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Demo_page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Demo_page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Demo_page query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Demo_page whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Demo_page whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Demo_page whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Demo_page whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Demo_page extends Model
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'demo_pages';

    protected $fillable = ['id', 'link', 'email', 'status'];

    protected string $logName = 'page';

    protected string $logNameColumn = 'Demo page';

    protected array $logAttributes = [
        'id', 'link', 'email', 'status',
    ];

    protected array $logUrl = [
        'segments' => ['demo', 'page'],
    ];

    protected function getMappings(): array
    {
        return [
            'id' => ['ID', fn ($value) => $value],
            'link' => ['Link', fn ($value) => $value],
            'email' => ['Email', fn ($value) => $value],
            'status' => ['Status', fn ($value): array|string|null => $value === 1 ? __('message.active') : __('message.inactive')],
        ];
    }
}
