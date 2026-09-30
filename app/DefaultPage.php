<?php

declare(strict_types=1);

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property int $page_id
 * @property string $page_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPage wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPage wherePageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DefaultPage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class DefaultPage extends Model
{
    use SystemActivityLogsTrait;

    protected $table = 'default_pages';

    protected $fillable = ['page_id', 'page_url'];

    protected string $logName = 'page';

    protected string $logNameColumn = 'Default page';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'page_id', 'page_url',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['pages', ':id',  'edit'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'page_id' => ['Page ID', fn ($value) => $value],
            'page_url' => ['Page URL', fn ($value) => $value],
        ];
    }
}
