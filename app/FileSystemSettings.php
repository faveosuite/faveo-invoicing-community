<?php

declare(strict_types=1);

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string $disk
 * @property string|null $local_file_storage_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $chrome_path
 * @property string|null $pdf_driver
 * @property-read Collection<int, Activity> $activitiesAsSubject
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileSystemSettings wherePdfDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileSystemSettings whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class FileSystemSettings extends Model
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    use SystemActivityLogsTrait;

    protected $table = 'settings_filesystem';

    protected $fillable = [
        'disk', 'local_file_storage_path', 'chrome_path', 'pdf_driver',
    ];

    protected string $logName = 'file_storage';

    protected string $logNameColumn = 'File Storage Settings';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'disk', 'local_file_storage_path', 'chrome_path', 'pdf_driver',
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
            'chrome_path' => ['Chrome Path', fn ($value) => $value],
            'pdf_driver' => ['PDF Driver', fn ($value) => $value],
        ];
    }
}
