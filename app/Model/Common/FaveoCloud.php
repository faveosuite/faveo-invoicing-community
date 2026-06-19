<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $cloud_central_domain
 * @property string|null $cloud_cname
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoCloud newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoCloud newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoCloud query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoCloud whereCloudCentralDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoCloud whereCloudCname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoCloud whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoCloud whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoCloud whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FaveoCloud extends Model
{
    use SystemActivityLogsTrait;

    protected $table = 'faveo_cloud';

    protected $fillable = ['cloud_central_domain', 'cloud_cname'];

    protected string $logName = 'cloud';

    protected string $logNameColumn = 'Faveo Cloud';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'cloud_central_domain', 'cloud_cname',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['view/tenant'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'cloud_central_domain' => ['Cloud Central Domain', fn ($value) => $value],
            'cloud_cname' => ['Cloud Cname', fn ($value) => $value],
        ];
    }
}
