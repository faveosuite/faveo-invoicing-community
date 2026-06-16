<?php

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileSystemSettings extends Model
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'settings_filesystem';

    protected $fillable = [
        'disk', 'local_file_storage_path', 'node_path', 'npm_path', 'chrome_path',
    ];

    protected $logName = 'file_storage';

    protected $logNameColumn = 'File Storage Settings';

    protected $logAttributes = [
        'disk', 'local_file_storage_path', 'node_path', 'npm_path', 'chrome_path',
    ];

    protected $logUrl = [
        'segments' => ['file-storage'],
    ];

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
