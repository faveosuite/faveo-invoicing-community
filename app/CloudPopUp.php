<?php

declare(strict_types=1);

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $cloud_top_message
 * @property string $cloud_label_field
 * @property string $cloud_label_radio
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudPopUp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudPopUp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudPopUp query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudPopUp whereCloudLabelField($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudPopUp whereCloudLabelRadio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudPopUp whereCloudTopMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudPopUp whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudPopUp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CloudPopUp whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CloudPopUp extends Model
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'cloud_pop_up';

    protected $guarded = [];

    protected string $logName = 'cloud';

    protected string $logNameColumn = 'Settings';

    protected array $logAttributes = [
        'cloud_top_message',
        'cloud_label_field',
        'cloud_label_radio',
    ];

    protected array $logUrl = [
        'segments' => ['view/tenant'],
    ];

    protected function getMappings(): array
    {
        return [
            'cloud_top_message' => ['Cloud Top Message', fn ($value) => $value],
            'cloud_label_field' => ['Cloud Label Field', fn ($value) => $value],
            'cloud_label_radio' => ['Cloud Label Radio', fn ($value) => $value],
        ];
    }
}
