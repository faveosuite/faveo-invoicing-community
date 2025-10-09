<?php

namespace App\Model\Common;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PipedriveField extends Model
{
    use HasFactory, SystemActivityLogsTrait;

    protected $table = 'pipedrive_fields';

    protected $fillable = [
        'field_name',
        'field_key',
        'field_type',
        'pipedrive_group_id',
        'local_field_id',
    ];

    protected $logName = 'pipedrive';

    protected $logNameColumn = 'Settings';

    protected $logAttributes = [
        'field_name',
        'field_key',
        'field_type',
        'pipedrive_group_id',
        'local_field_id',
    ];

    protected $logUrl = ['pipedrive/mapping/1'];

    protected function getMappings(): array
    {
        return [
            'field_name' => ['Field Name', fn ($value) => $value],
            'field_key' => ['Field Key', fn ($value) => $value],
            'field_type' => ['Field Type', fn ($value) => $value],
            'pipedrive_group_id' => ['Pipedrive Group', fn ($value) => optional($this->pipedriveGroups)->name],
            'local_field_id' => ['Local Field', fn ($value) => optional($this->localField)->field_name],
        ];
    }

    public function localField()
    {
        return $this->belongsTo(PipedriveLocalFields::class, 'local_field_id');
    }

    public function pipedriveGroups(): BelongsTo
    {
        return $this->belongsTo(PipedriveGroups::class, 'pipedrive_group_id');
    }

    public function pipedriveOptions()
    {
        return $this->hasMany(PipedriveFieldOption::class, 'pipedrive_field_id');
    }
}
