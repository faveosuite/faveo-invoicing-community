<?php

declare(strict_types=1);

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property int $user_id
 * @property string $file
 * @property string $file_path
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read User|null $user
 *
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
 *
 * @mixin \Eloquent
 */
class ExportDetail extends Model
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    use SystemActivityLogsTrait;

    protected $table = 'export_details';

    protected $fillable = ['user_id', 'file', 'file_path', 'name',
        'created_at',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected string $logName = 'reports';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'user_id', 'file', 'file_path', 'name',
    ];

    protected string $logNameColumn = 'file';

    protected bool $requireLogUrl = false;

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->causerID = 'user_id';
    }

    /**
     * @return array<mixed>
     */
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
