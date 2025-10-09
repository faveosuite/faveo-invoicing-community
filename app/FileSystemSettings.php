<?php

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileSystemSettings extends Model
{
    use HasFactory, SystemActivityLogsTrait;

    protected $table = 'settings_filesystem';

    protected $fillable = [
        'disk', 'local_file_storage_path',
    ];

    protected $logName = 'file_storage';

    protected $logNameColumn = 'File Storage Settings';


    protected $logAttributes = [
        'disk', 'local_file_storage_path',
    ];

    protected $logUrl = ['file-storage'];

    protected function getMappings(): array
    {
        return [
            'disk' => ['Disk', fn($value) => $value],
            'local_file_storage_path' => ['Local File Storage Path', fn($value) => $value],
        ];
    }
}
