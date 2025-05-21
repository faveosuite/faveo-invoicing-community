<?php

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PipedriveField extends Model
{
    use HasFactory;

    protected $table = 'pipedrive_fields';

    protected $fillable = [
        'field_name',
        'field_key',
        'field_type',
        'pipedrive_group_id',
        'local_field_id',
    ];

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
