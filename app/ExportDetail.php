<?php

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExportDetail extends Model
{
    use HasFactory, SystemActivityLogsTrait;

    protected $table = 'export_details';
    protected $fillable = ['user_id', 'file', 'file_path', 'name'];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }


    protected $logName = 'export';


    protected $logAttributes = [
        'user_id', 'file', 'file_path', 'name'
    ];

    protected $requireLogUrl = false;

    protected $logUrl = [];

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
