<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileSystemSettings extends Model
{
    use HasFactory;

    protected $table = 'settings_filesystem';

    protected $fillable = [
        'disk', 'local_file_storage_path',
    ];
}
