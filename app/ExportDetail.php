<?php

declare(strict_types=1);

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $file
 * @property string $file_path
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDetail whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDetail whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDetail whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportDetail whereUserId($value)
 * @mixin \Eloquent
 */
class ExportDetail extends Model
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'export_details';

    protected $fillable = ['user_id', 'file', 'file_path', 'name',
        'created_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected string $logName = 'reports';

    protected array $logAttributes = [
        'user_id', 'file', 'file_path', 'name',
    ];

    protected string $logNameColumn = 'file';

    protected bool $requireLogUrl = false;

    protected array $logUrl = [];

    protected ?string $causerID = 'user_id';

    protected function getMappings(): array
    {
        return [
            'user_id' => ['User ID', fn ($value) => $value],
            'file' => ['File', fn ($value) => $value],
            'file_path' => ['File Path', fn ($value) => $value],
            'name' => ['Name', fn ($value) => $value],
        ];
    }
}
