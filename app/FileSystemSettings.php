<?php

declare(strict_types=1);

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $disk
 * @property string|null $local_file_storage_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $node_path
 * @property string|null $npm_path
 * @property string|null $chrome_path
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileSystemSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileSystemSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileSystemSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileSystemSettings whereChromePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileSystemSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileSystemSettings whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileSystemSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileSystemSettings whereLocalFileStoragePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileSystemSettings whereNodePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileSystemSettings whereNpmPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileSystemSettings whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class FileSystemSettings extends Model
{
    /**
     * @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory>
     */
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'settings_filesystem';

    protected $fillable = [
        'disk', 'local_file_storage_path', 'node_path', 'npm_path', 'chrome_path',
    ];

    protected string $logName = 'file_storage';

    protected string $logNameColumn = 'File Storage Settings';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'disk', 'local_file_storage_path', 'node_path', 'npm_path', 'chrome_path',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['file-storage'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'disk' => ['Disk', fn ($value) => $value],
            'local_file_storage_path' => ['Local File Storage Path', fn ($value) => $value],
            'node_path' => ['Node Path', fn ($value) => $value],
            'npm_path' => ['NPM Path', fn ($value) => $value],
            'chrome_path' => ['Chrome Path', fn ($value) => $value],
        ];
    }
}
