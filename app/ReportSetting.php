<?php

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportSetting extends Model
{
    use HasFactory, SystemActivityLogsTrait;

    protected $table = 'report_settings';

    protected $fillable = ['records'];

    protected $logName = 'report_settings';

    protected $logNameColumn = 'settings';


    protected $logAttributes = [
        'records'
    ];

    protected $logUrl = ['records/column'];

    protected function getMappings(): array
    {
        return [
            'records' => ['Records', fn ($value) => $value],
        ];
    }
}
