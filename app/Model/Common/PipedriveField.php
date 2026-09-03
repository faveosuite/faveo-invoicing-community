<?php

namespace App\Model\Common;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string $field_name
 * @property string|null $field_key
 * @property string|null $field_type
 * @property int|null $local_field_id
 * @property int|null $pipedrive_group_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read PipedriveLocalFields|null $localField
 * @property-read PipedriveGroups|null $pipedriveGroups
 * @property-read Collection<int, PipedriveFieldOption> $pipedriveOptions
 * @property-read int|null $pipedrive_options_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveField query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveField whereFieldKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveField whereFieldName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveField whereFieldType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveField whereLocalFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveField wherePipedriveGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveField whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PipedriveField extends Model
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    use SystemActivityLogsTrait;

    protected $table = 'pipedrive_fields';

    protected $fillable = [
        'field_name',
        'field_key',
        'field_type',
        'pipedrive_group_id',
        'local_field_id',
    ];

    protected string $logName = 'pipedrive';

    protected string $logNameColumn = 'Settings';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'field_name',
        'field_key',
        'field_type',
        'pipedrive_group_id',
        'local_field_id',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['pipedrive/mapping/1'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'field_name' => ['Field Name', fn ($value) => $value],
            'field_key' => ['Field Key', fn ($value) => $value],
            'field_type' => ['Field Type', fn ($value) => $value],
            'pipedrive_group_id' => ['Pipedrive Group', fn ($value) => $this->pipedriveGroups?->name], // @phpstan-ignore property.notFound
            'local_field_id' => ['Local Field', fn ($value) => $this->localField?->field_name],
        ];
    }

    /**
     * @return BelongsTo<PipedriveLocalFields, $this>
     */
    public function localField(): BelongsTo
    {
        return $this->belongsTo(PipedriveLocalFields::class, 'local_field_id');
    }

    /**
     * @return BelongsTo<PipedriveGroups, $this>
     */
    public function pipedriveGroups(): BelongsTo
    {
        return $this->belongsTo(PipedriveGroups::class, 'pipedrive_group_id');
    }

    /**
     * @return HasMany<PipedriveFieldOption, $this>
     */
    public function pipedriveOptions(): HasMany
    {
        return $this->hasMany(PipedriveFieldOption::class, 'pipedrive_field_id');
    }
}
