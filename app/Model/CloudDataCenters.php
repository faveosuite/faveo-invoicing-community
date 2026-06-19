<?php

declare(strict_types=1);

namespace App\Model;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;

/**
 * @property int $id
 * @property string $cloud_countries
 * @property string $cloud_state
 * @property string $cloud_city
 * @property numeric $latitude
 * @property numeric $longitude
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudDataCenters newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudDataCenters newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudDataCenters query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudDataCenters whereCloudCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudDataCenters whereCloudCountries($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudDataCenters whereCloudState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudDataCenters whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudDataCenters whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudDataCenters whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudDataCenters whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudDataCenters whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CloudDataCenters extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'cloud_data_centers';

    protected $guarded = [];

    protected string $logName = 'cloud';

    protected string $logNameColumn = 'Settings';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'cloud_countries',
        'cloud_state',
        'cloud_city',
        'latitude',
        'longitude',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['view', 'tenant'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'cloud_countries' => ['Country', fn ($value) => $value],
            'cloud_state' => ['State', fn ($value) => $value],
            'cloud_city' => ['City', fn ($value) => $value],
            'latitude' => ['Latitude', fn ($value) => $value],
            'longitude' => ['Longitude', fn ($value) => $value],
        ];
    }
}
